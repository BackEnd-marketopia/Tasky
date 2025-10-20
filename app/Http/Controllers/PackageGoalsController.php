<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PackageGoal;
use App\Models\PackageType;
use App\Models\Workspace;

class PackageGoalsController extends Controller
{
    protected $workspace;
    protected $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // fetch session and use it in entire class with constructor
            $this->workspace = Workspace::find(getWorkspaceId());
            $this->user = getAuthenticatedUser();
            return $next($request);
        });
    }

    /**
     * Display a listing of package goals for the current workspace.
     */
    public function index(Request $request)
    {
        $packageGoals = PackageGoal::where('workspace_id', $this->workspace->id)
            ->with(['packageType'])
            ->orderBy('id', 'desc')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'error' => false,
                'package_goals' => $packageGoals,
                'total' => $packageGoals->count()
            ]);
        }

        $packageTypes = PackageType::forWorkspace($this->workspace->id)
            ->where('is_active', 1)
            ->get();

        return view('package_goals.index', compact('packageGoals', 'packageTypes'));
    }

    /**
     * Store a newly created package goal in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'package_type_id' => 'required|exists:package_types,id',
                'title' => 'required|string|max:255',
                'target_count' => 'required|integer|min:1|max:10000',
                'description' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify package type belongs to this workspace
            $packageType = PackageType::where('id', $request->package_type_id)
                ->where('workspace_id', $this->workspace->id)
                ->first();

            if (!$packageType) {
                return response()->json([
                    'error' => true,
                    'message' => 'Package type not found or not accessible'
                ], 404);
            }

            // Check if package goal already exists with same title for this package type and workspace
            $existingGoal = PackageGoal::where('package_type_id', $request->package_type_id)
                ->where('workspace_id', $this->workspace->id)
                ->where('title', $request->title)
                ->first();

            if ($existingGoal) {
                return response()->json([
                    'error' => true,
                    'message' => 'هدف بهذا العنوان موجود بالفعل لهذا النوع من الحزم في هذا المشروع. يرجى اختيار عنوان مختلف.'
                ], 422);
            }

            // Create package goal
            $packageGoal = PackageGoal::create([
                'package_type_id' => $request->package_type_id,
                'title' => $request->title,
                'target_count' => $request->target_count,
                'description' => $request->description,
                'workspace_id' => $this->workspace->id
            ]);

            return response()->json([
                'error' => false,
                'message' => 'Package goal created successfully',
                'package_goal' => $packageGoal->load('packageType')
            ]);

        } catch (\Exception $e) {
            // Log the actual error for debugging
            Log::error('Package Goal Creation Error: ' . $e->getMessage());
            
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while creating package goal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified package goal.
     */
    public function show($id)
    {
        $packageGoal = PackageGoal::where('workspace_id', $this->workspace->id)
            ->with(['packageType', 'tasks.status', 'tasks.project'])
            ->findOrFail($id);

        // Calculate analytics for this specific goal
        $totalProgress = $packageGoal->tasks->sum('progress_count');
        $progressPercentage = $packageGoal->target_count > 0 ? 
            round(($totalProgress / $packageGoal->target_count) * 100, 2) : 0;

        return view('package-goals.show', compact('packageGoal', 'totalProgress', 'progressPercentage'));
    }

    /**
     * Update the specified package goal in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $packageGoal = PackageGoal::where('workspace_id', $this->workspace->id)
                ->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'package_type_id' => 'required|exists:package_types,id',
                'title' => 'required|string|max:255',
                'target_count' => 'required|integer|min:1|max:10000',
                'description' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify package type belongs to this workspace
            $packageType = PackageType::where('id', $request->package_type_id)
                ->where('workspace_id', $this->workspace->id)
                ->first();

            if (!$packageType) {
                return response()->json([
                    'error' => true,
                    'message' => 'Package type not found or not accessible'
                ], 404);
            }

            // Check if title is unique for this package type and workspace (excluding current goal)
            $existingGoal = PackageGoal::where('package_type_id', $request->package_type_id)
                ->where('workspace_id', $this->workspace->id)
                ->where('title', $request->title)
                ->where('id', '!=', $id)
                ->first();

            if ($existingGoal) {
                return response()->json([
                    'error' => true,
                    'message' => 'هدف بهذا العنوان موجود بالفعل لهذا النوع من الحزم في هذا المشروع. يرجى اختيار عنوان مختلف.'
                ], 422);
            }

            // Update package goal
            $packageGoal->update([
                'package_type_id' => $request->package_type_id,
                'title' => $request->title,
                'target_count' => $request->target_count,
                'description' => $request->description
            ]);

            return response()->json([
                'error' => false,
                'message' => 'Package goal updated successfully',
                'package_goal' => $packageGoal->fresh(['packageType'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while updating package goal'
            ], 500);
        }
    }

    /**
     * Remove the specified package goal from storage.
     */
    public function destroy($id)
    {
        try {
            $packageGoal = PackageGoal::where('workspace_id', $this->workspace->id)
                ->findOrFail($id);

            $packageGoal->delete();

            return response()->json([
                'error' => false,
                'message' => 'Package goal deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while deleting package goal'
            ], 500);
        }
    }

    /**
     * Get package goals by package type.
     */
    public function getByPackageType($packageTypeId)
    {
        try {
            // Verify package type belongs to this workspace
            $packageType = PackageType::where('id', $packageTypeId)
                ->where('workspace_id', $this->workspace->id)
                ->first();

            if (!$packageType) {
                return response()->json([
                    'error' => true,
                    'message' => 'Package type not found or not accessible'
                ], 404);
            }

            $packageGoals = PackageGoal::where('package_type_id', $packageTypeId)
                ->where('workspace_id', $this->workspace->id)
                ->where('is_active', true)
                ->orderBy('id', 'desc')
                ->get(['id', 'title', 'target_count', 'description']);

            return response()->json([
                'error' => false,
                'data' => $packageGoals
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while fetching package goals'
            ], 500);
        }
    }
}

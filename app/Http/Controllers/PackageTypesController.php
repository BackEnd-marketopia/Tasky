<?php

namespace App\Http\Controllers;

use App\Models\PackageType;
use App\Models\PackageGoal;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PackageTypesController extends Controller
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
     * Display a listing of package types for the current workspace.
     */
    public function index(Request $request)
    {
        $packageTypes = PackageType::forWorkspace($this->workspace->id)
            ->with(['packageGoal'])
            ->orderBy('name')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'error' => false,
                'package_types' => $packageTypes,
                'total' => $packageTypes->count()
            ]);
        }

        return view('package_types.index', compact('packageTypes'));
    }

    /**
     * Store a newly created package type in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:7',
                'description' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if package type name already exists in this workspace
            $existingType = PackageType::forWorkspace($this->workspace->id)
                ->where('name', $request->name)
                ->first();

            if ($existingType) {
                return response()->json([
                    'error' => true,
                    'message' => 'Package type name already exists in this workspace'
                ], 422);
            }

            DB::beginTransaction();

            // Create package type
            $packageType = PackageType::create([
                'name' => $request->name,
                'icon' => $request->icon ?? 'fas fa-box',
                'color' => $request->color ?? '#007bff',
                'description' => $request->description,
                'workspace_id' => $this->workspace->id,
                'is_active' => true
            ]);



            DB::commit();

            return response()->json([
                'error' => false,
                'message' => 'Package type created successfully',
                'package_type' => $packageType->load('packageGoal')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Failed to create package type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified package type.
     */
    public function show($id)
    {
        $packageType = PackageType::forWorkspace($this->workspace->id)
            ->with(['packageGoal'])
            ->find($id);

        if (!$packageType) {
            return response()->json([
                'error' => true,
                'message' => 'Package type not found'
            ], 404);
        }

        return response()->json([
            'error' => false,
            'package_type' => $packageType
        ]);
    }

    /**
     * Update the specified package type in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $packageType = PackageType::forWorkspace($this->workspace->id)->find($id);

            if (!$packageType) {
                return response()->json([
                    'error' => true,
                    'message' => 'Package type not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:7',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update package type
            $packageType->update([
                'name' => $request->name,
                'icon' => $request->icon ?? $packageType->icon,
                'color' => $request->color ?? $packageType->color,
                'description' => $request->description,
                'is_active' => $request->has('is_active') ? $request->is_active : $packageType->is_active
            ]);



            DB::commit();

            return response()->json([
                'error' => false,
                'message' => 'Package type updated successfully',
                'package_type' => $packageType->fresh(['packageGoal'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Failed to update package type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified package type from storage.
     */
    public function destroy($id)
    {
        try {
            $packageType = PackageType::forWorkspace($this->workspace->id)->find($id);

            if (!$packageType) {
                return response()->json([
                    'error' => true,
                    'message' => 'Package type not found'
                ], 404);
            }

            DB::beginTransaction();

            // Delete package type (package goals will be cascade deleted automatically)
            $packageType->delete();

            DB::commit();

            return response()->json([
                'error' => false,
                'message' => 'Package type deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Failed to delete package type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get package type statistics for the current workspace.
     */
    public function statistics()
    {
        $packageTypes = PackageType::forWorkspace($this->workspace->id)
            ->active()
            ->with(['packageGoal'])
            ->get();

        $statistics = $packageTypes->map(function ($packageType) {
            return [
                'id' => $packageType->id,
                'name' => $packageType->name,
                'icon' => $packageType->icon,
                'color' => $packageType->color,
                'total_tasks' => $packageType->total_tasks_count,
                'completed_tasks' => $packageType->completed_tasks_count,
                'target_count' => $packageType->packageGoal?->target_count ?? 0,
                'completion_percentage' => $packageType->completion_percentage,
                'progress_status' => $packageType->packageGoal?->progress_status ?? 'not-started'
            ];
        });

        return response()->json([
            'error' => false,
            'statistics' => $statistics,
            'workspace' => [
                'name' => $this->workspace->title,
                'total_package_types' => $packageTypes->count()
            ]
        ]);
    }
}

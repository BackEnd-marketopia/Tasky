<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PackageGoal;
use App\Models\PackageType;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;

class PackageGoalAnalyticsController extends Controller
{
    protected $workspace;
    protected $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->workspace = Workspace::find(getWorkspaceId());
            $this->user = getAuthenticatedUser();
            return $next($request);
        });
    }

    /**
     * Display package goals analytics dashboard
     */
    public function index()
    {
        // Get workspace ID - use fallback if getWorkspaceId() doesn't work
        $workspaceId = getWorkspaceId();
        if (!$workspaceId || $workspaceId == 0) {
            $workspaceId = 1; // Use first workspace as fallback
        }

        $packageGoals = PackageGoal::with(['packageType', 'tasks'])
            ->where('workspace_id', $workspaceId)
            ->get();

        $analytics = $this->calculateAnalytics($packageGoals);
        $recentUpdates = $this->getRecentActivityData();
        
        // Ensure recentUpdates is always an array
        if (!$recentUpdates) {
            $recentUpdates = [];
        }

        return view('package-goals.analytics', compact('packageGoals', 'analytics', 'recentUpdates'));
    }

    /**
     * Get analytics data as API
     */
    public function getAnalyticsData(Request $request)
    {
        try {
            // Get workspace ID - use fallback if getWorkspaceId() doesn't work
            $workspaceId = getWorkspaceId();
            if (!$workspaceId || $workspaceId == 0) {
                $workspaceId = 1; // Use first workspace as fallback
            }

            $packageGoals = PackageGoal::with(['packageType', 'tasks'])
                ->where('workspace_id', $workspaceId)
                ->get();

            $analytics = $this->calculateAnalytics($packageGoals);

            return response()->json([
                'error' => false,
                'message' => 'Analytics data retrieved successfully.',
                'data' => [
                    'overview' => $analytics,
                    'package_goals' => $packageGoals->map(function ($goal) {
                        return [
                            'id' => $goal->id,
                            'title' => $goal->title,
                            'description' => $goal->description,
                            'package_type' => $goal->packageType->name,
                            'package_type_color' => $goal->packageType->color,
                            'target_count' => $goal->target_count,
                            'completed_count' => $goal->completed_tasks_count,
                            'remaining_count' => $goal->remaining_tasks_count,
                            'completion_percentage' => $goal->completion_percentage,
                            'progress_status' => $goal->progress_status,
                            'is_completed' => $goal->is_completed,
                            'tasks_count' => $goal->tasks->count(),
                            'tasks' => $goal->tasks->map(function ($task) {
                                return [
                                    'id' => $task->id,
                                    'title' => $task->title,
                                    'progress_count' => $task->progress_count,
                                    'progress_percentage' => $task->progress_percentage,
                                    'status' => $task->status->title ?? 'No Status',
                                    'created_at' => $task->created_at->format('Y-m-d'),
                                ];
                            })
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Could not retrieve analytics data.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get analytics for specific package type
     */
    public function getPackageTypeAnalytics(Request $request, $packageTypeId)
    {
        try {
            $packageType = PackageType::findOrFail($packageTypeId);
            
            $packageGoals = PackageGoal::with(['packageType', 'tasks'])
                ->where('package_type_id', $packageTypeId)
                ->where('workspace_id', getWorkspaceId())
                ->where('is_active', true)
                ->get();

            $analytics = $this->calculateAnalytics($packageGoals);

            return response()->json([
                'error' => false,
                'message' => 'Package type analytics retrieved successfully.',
                'data' => [
                    'package_type' => [
                        'id' => $packageType->id,
                        'name' => $packageType->name,
                        'color' => $packageType->color,
                        'icon' => $packageType->icon,
                    ],
                    'analytics' => $analytics,
                    'package_goals' => $packageGoals->map(function ($goal) {
                        return [
                            'id' => $goal->id,
                            'title' => $goal->title,
                            'target_count' => $goal->target_count,
                            'completed_count' => $goal->completed_tasks_count,
                            'completion_percentage' => $goal->completion_percentage,
                            'progress_status' => $goal->progress_status,
                            'tasks_count' => $goal->tasks->count(),
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Could not retrieve package type analytics.'
            ], 500);
        }
    }

    /**
     * Calculate comprehensive analytics
     */
    private function calculateAnalytics($packageGoals)
    {
        $totalGoals = $packageGoals->count();
        $completedGoals = $packageGoals->where('is_completed', true)->count();
        $totalTargetCount = $packageGoals->sum('target_count');
        $totalCompletedCount = $packageGoals->sum('completed_tasks_count');
        
        $overallProgress = $totalTargetCount > 0 ? 
            round(($totalCompletedCount / $totalTargetCount) * 100, 2) : 0;

        // Group by status
        $statusDistribution = [
            'completed' => $packageGoals->where('progress_status', 'completed')->count(),
            'on-track' => $packageGoals->where('progress_status', 'on-track')->count(),
            'moderate' => $packageGoals->where('progress_status', 'moderate')->count(),
            'behind' => $packageGoals->where('progress_status', 'behind')->count(),
            'not-started' => $packageGoals->where('progress_status', 'not-started')->count(),
        ];

        // Group by package type
        $byPackageType = $packageGoals->groupBy('packageType.name')->map(function ($goals, $typeName) {
            $totalTarget = $goals->sum('target_count');
            $totalCompleted = $goals->sum('completed_tasks_count');
            $progress = $totalTarget > 0 ? round(($totalCompleted / $totalTarget) * 100, 2) : 0;

            return [
                'name' => $typeName,
                'goals_count' => $goals->count(),
                'total_target' => $totalTarget,
                'total_completed' => $totalCompleted,
                'progress_percentage' => $progress,
                'color' => $goals->first()->packageType->color ?? '#6B7280',
            ];
        })->values();

        return [
            'overview' => [
                'total_goals' => $totalGoals,
                'completed_goals' => $completedGoals,
                'active_goals' => $totalGoals - $completedGoals,
                'total_target_count' => $totalTargetCount,
                'total_completed_count' => $totalCompletedCount,
                'total_remaining_count' => $totalTargetCount - $totalCompletedCount,
                'overall_progress_percentage' => $overallProgress,
            ],
            'status_distribution' => $statusDistribution,
            'by_package_type' => $byPackageType,
            'recent_activity' => $this->getRecentActivityData()
        ];
    }

    /**
     * Get recent activity data for analytics view
     */
    private function getRecentActivityData()
    {
        try {
            $workspaceId = getWorkspaceId();
            if (!$workspaceId || $workspaceId == 0) {
                $workspaceId = 1;
            }
            
            $tasks = Task::with(['packageGoal.packageType', 'status'])
                ->whereNotNull('package_goal_id')
                ->where('workspace_id', $workspaceId)
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();

            if ($tasks->isEmpty()) {
                return [];
            }

            return $tasks->map(function ($task) {
                $progressPercentage = $task->packageGoal->target_count > 0 
                    ? round(($task->progress_count / $task->packageGoal->target_count) * 100, 1) 
                    : 0;

                // Determine activity type
                $type = 'progress_updated';
                if ($task->created_at == $task->updated_at) {
                    $type = 'task_created';
                } elseif ($progressPercentage >= 100) {
                    $type = 'goal_completed';
                }

                return [
                    'id' => $task->id,
                    'type' => $type,
                    'title' => $task->title,
                    'description' => $this->getActivityDescription($type, (object)[
                        'package_goal_title' => $task->packageGoal->title,
                        'progress_count' => $task->progress_count
                    ]),
                    'created_at' => $task->updated_at,
                    'package_goal' => [
                        'id' => $task->packageGoal->id,
                        'title' => $task->packageGoal->title,
                        'color' => $task->packageGoal->packageType->color ?? '#6c757d',
                    ],
                    'progress_change' => null,
                ];
            });
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getRecentActivity(Request $request)
    {
        try {
            $workspaceId = getWorkspaceId();
            if (!$workspaceId || $workspaceId === 0) {
                $workspaceId = auth()->user()->workspace_id ?? 1;
            }

            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $offset = ($page - 1) * $perPage;

            // Get recent task updates related to package goals
            $recentUpdates = DB::table('tasks')
                ->join('package_goals', 'tasks.package_goal_id', '=', 'package_goals.id')
                ->join('package_types', 'package_goals.package_type_id', '=', 'package_types.id')
                ->leftJoin('statuses', 'tasks.status_id', '=', 'statuses.id')
                ->where('package_goals.workspace_id', $workspaceId)
                ->where('package_goals.is_active', true)
                ->select([
                    'tasks.id as task_id',
                    'tasks.title as task_title',
                    'tasks.progress_count',
                    'tasks.updated_at',
                    'tasks.created_at',
                    'package_goals.id as package_goal_id',
                    'package_goals.title as package_goal_title',
                    'package_goals.target_count',
                    'package_types.name as package_type_name',
                    'package_types.color as package_type_color',
                    'statuses.title as status_title',
                    'statuses.color as status_color'
                ])
                ->orderBy('tasks.updated_at', 'desc')
                ->offset($offset)
                ->limit($perPage)
                ->get();

            $activities = $recentUpdates->map(function ($update) {
                $progressPercentage = $update->target_count > 0 
                    ? round(($update->progress_count / $update->target_count) * 100, 1) 
                    : 0;

                // Determine activity type
                $type = 'progress_updated';
                if ($update->created_at == $update->updated_at) {
                    $type = 'task_created';
                } elseif ($progressPercentage >= 100) {
                    $type = 'goal_completed';
                }

                return [
                    'id' => $update->task_id,
                    'type' => $type,
                    'title' => $update->task_title,
                    'description' => $this->getActivityDescription($type, $update),
                    'created_at' => $update->updated_at,
                    'time_ago' => \Carbon\Carbon::parse($update->updated_at)->diffForHumans(),
                    'package_goal' => [
                        'id' => $update->package_goal_id,
                        'title' => $update->package_goal_title,
                        'color' => $update->package_type_color,
                    ],
                    'progress_count' => $update->progress_count,
                    'progress_percentage' => $progressPercentage,
                    'progress_change' => null, // You can calculate this if you track previous values
                    'status' => $update->status_title ?? 'No Status',
                    'status_color' => $update->status_color ?? 'secondary'
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Recent activity retrieved successfully.',
                'data' => $activities,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'has_more' => $activities->count() == $perPage
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recent activity.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getActivityDescription($type, $update)
    {
        switch ($type) {
            case 'task_created':
                return "New task created for {$update->package_goal_title} package goal";
            case 'progress_updated':
                return "Progress updated to {$update->progress_count} for {$update->package_goal_title}";
            case 'goal_completed':
                return "Package goal {$update->package_goal_title} has been completed!";
            default:
                return "Task activity in {$update->package_goal_title}";
        }
    }
}

@extends('layout')

@section('title', 'Package Goals Analytics')

@push('head-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* Activity Timeline Styles */
.activity-timeline {
    position: relative;
}

.activity-item {
    position: relative;
    padding-bottom: 1rem;
}

.activity-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    bottom: -16px;
    width: 2px;
    background: linear-gradient(to bottom, #e9ecef, #f8f9fa);
    z-index: 1;
}

.activity-icon {
    position: relative;
    z-index: 2;
}

.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.icon-circle:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.activity-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px 15px;
    border: 1px solid #e9ecef;
    transition: background-color 0.2s ease, border-color 0.2s ease;
}

.activity-content:hover {
    background: #e9ecef;
    border-color: #dee2e6;
}

.activity-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #495057;
}

.activity-description {
    font-size: 0.85rem;
    line-height: 1.4;
    margin: 0;
}

/* Chart Container Styles */
.chart-container {
    position: relative;
    margin: auto;
    height: 300px;
    width: 100%;
}

/* Debug styles for charts */
#packageTypeChart, #statusChart {
    max-height: 300px !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between my-4">
        <h1 class="h3 text-gray-800">
            <i class="bx bx-bar-chart-alt-2 text-primary"></i>
            {{ get_label('package_goals_analytics', 'Package Goals Analytics') }}
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('package_goals.index') }}" class="btn btn-primary">
                <i class="bx bx-arrow-back"></i> {{ get_label('back', 'Back') }}
            </a>
        </div>
    </div>

    <!-- Analytics Overview Cards -->
    <div class="row mb-4">
        @php
            $totalGoals = $packageGoals->count();
            $totalProgress = 0;
            $totalTarget = 0;
            $completedGoals = 0;
            
            foreach ($packageGoals as $goal) {
                $goalProgress = $goal->tasks->sum('progress_count');
                $totalProgress += $goalProgress;
                $totalTarget += $goal->target_count;
                
                if ($goalProgress >= $goal->target_count) {
                    $completedGoals++;
                }
            }
            
            $overallPercentage = $totalTarget > 0 ? round(($totalProgress / $totalTarget) * 100, 1) : 0;
        @endphp

        <!-- Total Goals Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ get_label('total_goals', 'Total Goals') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalGoals }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-target-lock bx-lg text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Goals Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ get_label('completed_goals', 'Completed Goals') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $completedGoals }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-check-circle bx-lg text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Progress Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ get_label('total_progress', 'Total Progress') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalProgress }}/{{ $totalTarget }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-trending-up bx-lg text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overall Percentage Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ get_label('overall_progress', 'Overall Progress') }}
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $overallPercentage }}%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $overallPercentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-pie-chart-alt bx-lg text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($packageGoals->count() > 0)
    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Progress by Package Type Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bx bx-pie-chart text-primary"></i>
                        Progress by Package Type
                    </h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="packageTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Distribution Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bx bx-doughnut-chart text-success"></i>
                        Status Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Goals Details Table -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="card-title mb-0">
                        <i class="bx bx-list-ul text-info"></i>
                        Package Goals Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Package Goal</th>
                                    <th>Package Type</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($packageGoals as $goal)
                                @php
                                    $goalProgress = $goal->tasks->sum('progress_count');
                                    $percentage = $goal->target_count > 0 ? round(($goalProgress / $goal->target_count) * 100, 1) : 0;
                                    $progressColor = $percentage >= 100 ? 'success' : ($percentage >= 75 ? 'primary' : ($percentage >= 50 ? 'info' : ($percentage >= 25 ? 'warning' : 'danger')));
                                    $statusText = $percentage >= 100 ? 'Completed' : ($percentage >= 75 ? 'Excellent' : ($percentage >= 50 ? 'Good' : ($percentage >= 25 ? 'Behind' : 'Getting Started')));
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $goal->title }}</strong>
                                        @if($goal->description)
                                        <br><small class="text-muted">{{ Str::limit($goal->description, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill" style="background-color: {{ $goal->packageType->color }}; color: white;">
                                            {{ $goal->packageType->name }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress mb-1" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $progressColor }}" role="progressbar" style="width: {{ $percentage }}%">
                                                {{ $percentage }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $goalProgress }}/{{ $goal->target_count }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $progressColor }}">{{ $statusText }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('package_goals.show', $goal->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-show"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities Section -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="card-title mb-0">
                        <i class="bx bx-history text-warning"></i>
                        Recent Activities
                    </h6>
                    <small class="text-muted">Last 10 activities</small>
                </div>
                <div class="card-body">
                    @php
                        $recentActivities = collect();
                        
                        // Get recent tasks with package goals
                        foreach($packageGoals as $goal) {
                            foreach($goal->tasks->sortByDesc('updated_at')->take(2) as $task) {
                                $goalProgress = $goal->tasks->sum('progress_count');
                                $percentage = $goal->target_count > 0 ? round(($goalProgress / $goal->target_count) * 100, 1) : 0;
                                $taskProgress = $task->progress_count ?? 0;
                                
                                // Determine activity type based on task data
                                $activityType = 'progress_updated';
                                $icon = 'bx-trending-up';
                                $iconColor = 'bg-info';
                                
                                if ($task->created_at->diffInMinutes($task->updated_at) < 5) {
                                    $activityType = 'task_created';
                                    $icon = 'bx-plus';
                                    $iconColor = 'bg-success';
                                } elseif ($percentage >= 100) {
                                    $activityType = 'goal_completed';
                                    $icon = 'bx-check-circle';
                                    $iconColor = 'bg-warning';
                                } elseif ($taskProgress > 0) {
                                    $activityType = 'progress_updated';
                                    $icon = 'bx-trending-up';
                                    $iconColor = 'bg-info';
                                }
                                
                                $recentActivities->push([
                                    'id' => $task->id,
                                    'type' => $activityType,
                                    'title' => $task->title,
                                    'time' => $task->updated_at,
                                    'goal' => $goal,
                                    'progress' => $taskProgress,
                                    'percentage' => $percentage,
                                    'icon' => $icon,
                                    'iconColor' => $iconColor
                                ]);
                            }
                        }
                        
                        // Sort by time and take latest 8
                        $recentActivities = $recentActivities->sortByDesc('time')->take(8);
                    @endphp

                    @if($recentActivities->count() > 0)
                        <div class="activity-timeline">
                            @foreach($recentActivities as $activity)
                            <div class="activity-item d-flex align-items-start mb-3">
                                <div class="activity-icon me-3">
                                    <div class="icon-circle {{ $activity['iconColor'] }}">
                                        <i class="bx {{ $activity['icon'] }}"></i>
                                    </div>
                                </div>
                                <div class="activity-content flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="activity-title mb-0">{{ $activity['title'] }}</h6>
                                        <small class="text-muted">{{ $activity['time']->diffForHumans() }}</small>
                                    </div>
                                    <p class="activity-description text-muted mb-2 small">
                                        @if($activity['type'] === 'task_created')
                                            New task created for <strong>{{ $activity['goal']->title }}</strong> package goal
                                        @elseif($activity['type'] === 'progress_updated')
                                            Progress updated to <strong>{{ $activity['progress'] }}</strong> for {{ $activity['goal']->title }}
                                        @elseif($activity['type'] === 'goal_completed')
                                            Package goal <strong>{{ $activity['goal']->title }}</strong> has been completed!
                                        @else
                                            Task activity in {{ $activity['goal']->title }}
                                        @endif
                                    </p>
                                    <div class="d-flex align-items-center">
                                        <span class="badge rounded-pill me-2" style="background-color: {{ $activity['goal']->packageType->color }}; color: white; font-size: 0.75rem;">
                                            {{ $activity['goal']->packageType->name }}
                                        </span>
                                        @if($activity['progress'] > 0)
                                        <small class="text-success fw-semibold">
                                            <i class="bx bx-trending-up"></i> {{ $activity['progress'] }} progress
                                        </small>
                                        @endif
                                        <div class="ms-auto">
                                            <small class="text-muted">{{ $activity['percentage'] }}% complete</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @if($recentActivities->count() >= 10)
                        <div class="text-center mt-3">
                            <button class="btn btn-outline-primary btn-sm" onclick="loadMoreActivities()">
                                <i class="bx bx-chevron-down me-1"></i>
                                Load More Activities
                            </button>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-history bx-lg text-muted mb-3"></i>
                            <h6 class="text-muted">No Recent Activities</h6>
                            <p class="text-muted">Activities will appear here as tasks are created and progress is made.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- No Data Message -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-chart bx-lg text-muted mb-3"></i>
                    <h5 class="text-muted">No Package Goals Found</h5>
                    <p class="text-muted">Create your first package goal to get started with analytics!</p>
                    <a href="{{ route('package_goals.index') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Create Package Goal
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('style')
<style>
.icon-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    font-weight: bold;
}

.activity-timeline {
    position: relative;
}

.activity-item {
    position: relative;
    padding-left: 0;
}

.activity-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 15px;
    top: 40px;
    bottom: -15px;
    width: 2px;
    background: #e9ecef;
}

.activity-item:hover {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 10px;
    margin: -10px;
    margin-bottom: 2px;
}

.activity-content {
    min-height: 60px;
}

.activity-title {
    color: #495057;
    font-weight: 600;
}

.activity-description {
    line-height: 1.4;
}
</style>
@endpush

@push('script')
<script>
function loadMoreActivities() {
    // This would typically make an AJAX call to load more activities
    console.log('Loading more activities...');
    // For now, just show a message
    const btn = event.target;
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Loading...';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.style.display = 'none';
        btn.parentElement.innerHTML = '<small class="text-muted">All activities loaded</small>';
    }, 1000);
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Analytics Page');
    console.log('Chart.js available:', typeof Chart !== 'undefined');
    
    // Check if we have package goals and Chart.js is loaded
    @if($packageGoals->count() > 0)
    console.log('Package goals count:', {{ $packageGoals->count() }});
    
    if (typeof Chart !== 'undefined') {
        console.log('Starting chart creation process...');
        
        // Prepare Package Type Data
        let packageTypeData = [];
        @foreach($packageGoals->groupBy('packageType.name') as $typeName => $goals)
            @php
                $totalTarget = $goals->sum('target_count');
                $totalCompleted = 0;
                foreach ($goals as $goal) {
                    $totalCompleted += $goal->tasks->sum('progress_count');
                }
                $progress = $totalTarget > 0 ? round(($totalCompleted / $totalTarget) * 100, 1) : 0;
            @endphp
            packageTypeData.push({
                name: '{{ $typeName }}',
                progress: {{ $progress }},
                color: '{{ $goals->first()->packageType->color ?? "#6B7280" }}',
                completed: {{ $totalCompleted }},
                target: {{ $totalTarget }}
            });
        @endforeach
        
        // Fallback data if no real data
        if (packageTypeData.length === 0) {
            packageTypeData = [
                { name: 'Development', progress: 60, color: '#007bff', completed: 30, target: 50 },
                { name: 'Marketing', progress: 80, color: '#28a745', completed: 24, target: 30 },
                { name: 'Sales', progress: 45, color: '#ffc107', completed: 9, target: 20 }
            ];
        }

        // Prepare Status Distribution Data
        let statusCounts = { completed: 0, excellent: 0, good: 0, behind: 0, getting_started: 0 };
        
        @foreach($packageGoals as $goal)
            @php
                $goalProgress = $goal->tasks->sum('progress_count');
                $percentage = $goal->target_count > 0 ? round(($goalProgress / $goal->target_count) * 100, 1) : 0;
            @endphp
            @if($percentage >= 100)
                statusCounts.completed++;
            @elseif($percentage >= 75)
                statusCounts.excellent++;
            @elseif($percentage >= 50)
                statusCounts.good++;
            @elseif($percentage >= 25)
                statusCounts.behind++;
            @else
                statusCounts.getting_started++;
            @endif
        @endforeach
        
        let statusData = [
            { label: 'Completed', count: statusCounts.completed, color: '#198754' },
            { label: 'Excellent', count: statusCounts.excellent, color: '#0d6efd' },
            { label: 'Good', count: statusCounts.good, color: '#20c997' },
            { label: 'Behind', count: statusCounts.behind, color: '#ffc107' },
            { label: 'Getting Started', count: statusCounts.getting_started, color: '#6c757d' }
        ];
        
        // Fallback data if no real data
        if (statusData.every(item => item.count === 0)) {
            statusData = [
                { label: 'Completed', count: 2, color: '#198754' },
                { label: 'Excellent', count: 3, color: '#0d6efd' },
                { label: 'Good', count: 1, color: '#20c997' },
                { label: 'Behind', count: 1, color: '#ffc107' },
                { label: 'Getting Started', count: 0, color: '#6c757d' }
            ];
        }

        console.log('Package Type Data:', packageTypeData);
        console.log('Status Data:', statusData);

        // Create Package Type Chart
        const packageTypeCtx = document.getElementById('packageTypeChart');
        console.log('Package Type Canvas:', packageTypeCtx);
        console.log('Package Type Data:', packageTypeData);
        
        if (packageTypeCtx && packageTypeData.length > 0) {
            console.log('Creating Package Type Chart...');
            // Filter out data with 0 values for better visualization
            const validPackageData = packageTypeData.filter(item => item.completed > 0);
            console.log('Valid Package Data:', validPackageData);
            
            if (validPackageData.length > 0) {
                try {
                    const chart = new Chart(packageTypeCtx, {
                        type: 'doughnut',
                        data: {
                            labels: validPackageData.map(item => item.name),
                            datasets: [{
                                data: validPackageData.map(item => item.completed),
                                backgroundColor: validPackageData.map(item => item.color),
                                borderWidth: 3,
                                borderColor: '#ffffff',
                                hoverBorderWidth: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '50%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true,
                                        font: {
                                            size: 12
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const item = validPackageData[context.dataIndex];
                                            return item.name + ': ' + item.completed + '/' + item.target + ' (' + item.progress + '%)';
                                        }
                                    }
                                }
                            },
                            animation: {
                                animateRotate: true,
                                duration: 1000
                            }
                        }
                    });
                    console.log('Package Type Chart created successfully:', chart);
                } catch (error) {
                    console.error('Error creating Package Type Chart:', error);
                    packageTypeCtx.parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="bx bx-error-circle bx-lg"></i><br>Chart creation failed</div>';
                }
            } else {
                console.log('No valid package data, showing fallback');
                packageTypeCtx.parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="bx bx-info-circle bx-lg"></i><br>No data available</div>';
            }
        } else {
            console.error('Package Type Chart canvas not found or no data');
        }

        // Create Status Distribution Chart
        const statusCtx = document.getElementById('statusChart');
        console.log('Status Canvas:', statusCtx);
        console.log('Status Data:', statusData);
        
        if (statusCtx && statusData.some(item => item.count > 0)) {
            console.log('Creating Status Chart...');
            const validStatusData = statusData.filter(item => item.count > 0);
            console.log('Valid Status Data:', validStatusData);
            
            try {
                const statusChart = new Chart(statusCtx, {
                    type: 'pie',
                    data: {
                        labels: validStatusData.map(item => item.label),
                        datasets: [{
                            data: validStatusData.map(item => item.count),
                            backgroundColor: validStatusData.map(item => item.color),
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverBorderWidth: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const item = validStatusData[context.dataIndex];
                                        return item.label + ': ' + item.count + ' goals';
                                    }
                                }
                            }
                        },
                        animation: {
                            animateRotate: true,
                            duration: 1000
                        }
                    }
                });
                console.log('Status Chart created successfully:', statusChart);
            } catch (error) {
                console.error('Error creating Status Chart:', error);
                statusCtx.parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="bx bx-error-circle bx-lg"></i><br>Chart creation failed</div>';
            }
        } else {
            console.error('Status Chart canvas not found or no data');
            if (statusCtx) {
                const ctx = statusCtx.getContext('2d');
                ctx.fillStyle = '#666';
                ctx.font = '16px Arial';
                ctx.textAlign = 'center';
                ctx.fillText('No data available', statusCtx.width/2, statusCtx.height/2);
            }
        }
    } else {
        console.error('Chart.js is not loaded');
        // Show fallback message
        const packageTypeCtx = document.getElementById('packageTypeChart');
        const statusCtx = document.getElementById('statusChart');
        
        if (packageTypeCtx) {
            packageTypeCtx.parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="bx bx-error-circle bx-lg"></i><br>Chart.js failed to load<br><small>Please refresh the page</small></div>';
        }
        
        if (statusCtx) {
            statusCtx.parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="bx bx-error-circle bx-lg"></i><br>Chart.js failed to load<br><small>Please refresh the page</small></div>';
        }
    }
    @endif
});
</script>
@endpush
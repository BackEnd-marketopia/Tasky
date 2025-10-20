@extends('layout')

@section('title')
{{ $packageGoal->title }} - {{ get_label('package_goal', 'Package Goal') }}
@endsection

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ url('home') }}">{{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ url('package-goals') }}">{{ get_label('package_goals', 'Package Goals') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ $packageGoal->title }}
                    </li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ url('package-goals/analytics') }}" class="btn btn-sm btn-info">
                <i class="bx bx-line-chart"></i> {{ get_label('analytics', 'Analytics') }}
            </a>
            <a href="{{ url('package-goals') }}" class="btn btn-sm btn-primary">
                <i class="bx bx-arrow-back"></i> {{ get_label('back', 'Back') }}
            </a>
        </div>
    </div>

    <!-- Package Goal Header -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="fw-bold mb-2">{{ $packageGoal->title }}</h2>
                            <span class="badge rounded-pill px-3 py-2" style="background-color: {{ $packageGoal->packageType->color }}; color: white;">
                                <i class="bx bx-package me-1"></i>{{ $packageGoal->packageType->name }}
                            </span>
                        </div>
                        <div class="text-end">
                            <div class="fs-2 fw-bold" style="color: {{ $progressPercentage >= 100 ? '#198754' : ($progressPercentage >= 75 ? '#0d6efd' : ($progressPercentage >= 50 ? '#0dcaf0' : ($progressPercentage >= 25 ? '#ffc107' : '#dc3545'))) }}">
                                {{ $progressPercentage }}%
                            </div>
                            <small class="text-muted">{{ $totalProgress }}/{{ $packageGoal->target_count }}</small>
                        </div>
                    </div>

                    @if($packageGoal->description)
                    <div class="mb-4">
                        <p class="text-muted mb-0">{{ $packageGoal->description }}</p>
                    </div>
                    @endif

                    <!-- Progress Bar -->
                    @php
                        $progressColor = $progressPercentage >= 100 ? '#198754' : 
                                        ($progressPercentage >= 75 ? '#0d6efd' : 
                                        ($progressPercentage >= 50 ? '#0dcaf0' : 
                                        ($progressPercentage >= 25 ? '#ffc107' : '#dc3545')));
                    @endphp
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: {{ $progressPercentage }}%; background-color: {{ $progressColor }};" 
                             aria-valuenow="{{ $progressPercentage }}" aria-valuemin="0" aria-valuemax="100">
                            <span class="fw-semibold">{{ $progressPercentage }}%</span>
                        </div>
                    </div>

                    <!-- Stats Row -->
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background-color: rgba(25, 135, 84, 0.1); border: 1px solid rgba(25, 135, 84, 0.2);">
                                <div class="fs-4 fw-bold" style="color: #198754;">{{ $totalProgress }}</div>
                                <small style="color: #198754;">{{ get_label('current_progress', 'Current Progress') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background-color: rgba(13, 110, 253, 0.1); border: 1px solid rgba(13, 110, 253, 0.2);">
                                <div class="fs-4 fw-bold" style="color: #0d6efd;">{{ $packageGoal->target_count }}</div>
                                <small style="color: #0d6efd;">{{ get_label('target', 'Target') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background-color: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.2);">
                                <div class="fs-4 fw-bold" style="color: #ffc107;">{{ max(0, $packageGoal->target_count - $totalProgress) }}</div>
                                <small style="color: #ffc107;">{{ get_label('remaining', 'Remaining') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background-color: rgba(13, 202, 240, 0.1); border: 1px solid rgba(13, 202, 240, 0.2);">
                                <div class="fs-5 fw-bold" style="color: {{ $progressColor }};">
                                    @if($progressPercentage >= 100)
                                        <i class="bx bx-check-circle"></i> {{ get_label('completed', 'Completed') }}
                                    @elseif($progressPercentage >= 75)
                                        <i class="bx bx-trending-up"></i> {{ get_label('excellent', 'Excellent') }}
                                    @elseif($progressPercentage >= 50)
                                        <i class="bx bx-trending-up"></i> {{ get_label('good', 'Good') }}
                                    @elseif($progressPercentage >= 25)
                                        <i class="bx bx-time-five"></i> {{ get_label('behind', 'Behind') }}
                                    @else
                                        <i class="bx bx-play-circle"></i> {{ get_label('getting_started', 'Getting Started') }}
                                    @endif
                                </div>
                                <small style="color: {{ $progressColor }};">{{ get_label('status', 'Status') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Tasks -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="card-title mb-0">
                        <i class="bx bx-task me-2"></i>
                        {{ get_label('related_tasks', 'Related Tasks') }} ({{ $packageGoal->tasks->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($packageGoal->tasks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ get_label('task', 'Task') }}</th>
                                    <th>{{ get_label('project', 'Project') }}</th>
                                    <th>{{ get_label('progress', 'Progress') }}</th>
                                    <th>{{ get_label('status', 'Status') }}</th>
                                    <th>{{ get_label('actions', 'Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($packageGoal->tasks as $task)
                                <tr>
                                    <td>
                                        <strong>{{ $task->title }}</strong>
                                        @if($task->description)
                                        <br><small class="text-muted">{{ Str::limit($task->description, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->project)
                                        <a href="{{ url('projects/information/' . $task->project_id) }}" class="text-decoration-none">
                                            {{ $task->project->title }}
                                        </a>
                                        @else
                                        <span class="text-muted">{{ get_label('no_project', 'No Project') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->progress_count)
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold me-2">{{ $task->progress_count ?? 0 }}</span>
                                            @php
                                                $taskProgress = $packageGoal->target_count > 0 ? round((($task->progress_count ?? 0) / $packageGoal->target_count) * 100, 1) : 0;
                                            @endphp
                                            <small class="text-muted">({{ $taskProgress }}% of goal)</small>
                                        </div>
                                        @else
                                        <span class="text-muted">{{ get_label('no_progress', 'No Progress') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->status)
                                        <span class="badge bg-label-{{ $task->status->color }}">{{ $task->status->title }}</span>
                                        @else
                                        <span class="badge bg-secondary">{{ get_label('no_status', 'No Status') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url('tasks/information/' . $task->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-show"></i> {{ get_label('view', 'View') }}
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bx bx-task bx-lg text-muted mb-3"></i>
                        <h6 class="text-muted">{{ get_label('no_related_tasks', 'No Related Tasks') }}</h6>
                        <p class="text-muted">{{ get_label('no_tasks_assigned_to_goal', 'No tasks have been assigned to this package goal yet.') }}</p>
                        <a href="{{ url('tasks') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i>{{ get_label('create_task', 'Create Task') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
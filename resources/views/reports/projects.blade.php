@extends('layout')
@section('title')
{{ get_label('projects_report', 'Projects Report') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home.index') }}">{{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#">{{ get_label('reports', 'Reports') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ get_label('projects', 'Projects') }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Summary Cards -->
    <div class="d-flex mb-4 flex-wrap gap-3">
        <div class="card flex-grow-1 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bx bx-briefcase-alt-2 fs-2 text-primary me-3"></i>
                <div>
                    <h6 class="card-title mb-1">{{ get_label('total_projects', 'Total Projects') }}</h6>
                    <p class="card-text mb-0" id="total-projects">{{ get_label('loading', 'Loading...') }}</p>
                </div>
            </div>
        </div>
        <div class="card flex-grow-1 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bx bx-task fs-2 text-success me-3"></i>
                <div>
                    <h6 class="card-title mb-1">{{ get_label('total_tasks', 'Total Tasks') }}</h6>
                    <p class="card-text mb-0" id="total-tasks">{{ get_label('loading', 'Loading...') }}</p>
                </div>
            </div>
        </div>
        <div class="card flex-grow-1 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bx bx-group fs-2 text-warning me-3"></i>
                <div>
                    <h6 class="card-title mb-1">{{ get_label('total_team_members', 'Total Team Members') }}</h6>
                    <p class="card-text mb-0" id="total-team-members">{{ get_label('loading', 'Loading...') }}</p>
                </div>
            </div>
        </div>
        <div class="card flex-grow-1 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bx bx-time-five fs-2 text-danger me-3"></i>
                <div>
                    <h6 class="card-title mb-1">
                        {{ get_label('average_overdue_days_per_project', 'Avg. Overdue Days/Project') }}
                    </h6>
                    <p class="card-text mb-0" id="average-overdue-days-per-project">{{ get_label('loading', 'Loading...') }}</p>
                </div>
            </div>
        </div>
        <div class="card flex-grow-1 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bx bx-calendar-exclamation fs-2 text-info me-3"></i>
                <div>
                    <h6 class="card-title mb-1">{{ get_label('overdue_projects', 'Overdue Projects') }}
                    </h6>
                    <p class="card-text mb-0" id="overdue-projects-percentage">{{ get_label('loading', 'Loading...') }}</p>
                </div>
            </div>
        </div>
        <div class="card flex-grow-1 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bx bx-calendar fs-2 text-dark me-3"></i>
                <div>
                    <h6 class="card-title mb-1">{{ get_label('total_overdue_days', 'Total Overdue Days') }}</h6>
                    <p class="card-text mb-0" id="total-overdue-days">{{ get_label('loading', 'Loading...') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Filters Row -->
            <div class="row">
                <!-- Date Range Filter -->
                <div class="col-md-4 mb-3">
                    <input type="text" id="filter_date_range" class="form-control" placeholder="<?= get_label('date_between', 'Date Between') ?>" autocomplete="off">
                </div>
                <div class="col-md-4 mb-3">
                    <div class="input-group input-group-merge">
                        <input type="text" id="report_start_date_between" class="form-control" placeholder="<?= get_label('from_date_between', 'From date between') ?>" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="input-group input-group-merge">
                        <input type="text" id="report_end_date_between" class="form-control" placeholder="<?= get_label('to_date_between', 'To date between') ?>" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <select class="form-control projects_select" id="project_filter" multiple="multiple" data-placeholder="<?= get_label('select_projects', 'Select Projects') ?>">
                    </select>
                </div>
                <!-- User Filter -->
                <div class="col-md-4 mb-3">
                    <select class="form-control users_select" id="user_filter" multiple="multiple" data-placeholder="<?= get_label('select_users', 'Select Users') ?>">
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <select class="form-control clients_select" id="client_filter" multiple="multiple" data-placeholder="<?= get_label('select_clients', 'Select Clients') ?>">
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-4 mb-3">
                    <select class="form-control statuses_filter" id="status_filter" multiple="multiple" data-placeholder="<?= get_label('select_statuses', 'Select Statuses') ?>">
                    </select>
                </div>
            </div>
            <input type="hidden" id="filter_date_range_from">
            <input type="hidden" id="filter_date_range_to">
            <input type="hidden" id="filter_start_date_from">
            <input type="hidden" id="filter_start_date_to">
            <input type="hidden" id="filter_end_date_from">
            <input type="hidden" id="filter_end_date_to">
            <div class="row mb-2">
                <!-- Export Button -->
                <div class="col-md-12 col-lg-12 d-flex align-items-center justify-content-md-end mb-md-0 mb-2">
                    <button class="btn btn-primary" id="export_button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ get_label('export_projects_report', 'Export Projects Report') }}">
                        <i class="bx bx-export"></i>
                    </button>
                </div>
            </div>
            <!-- Table -->
            <div class="table-responsive text-nowrap">
                <table id="projects_report_table" data-toggle="table"
                    data-url="{{ route('reports.project-report-data') }}" data-loading-template="loadingTemplate"
                    data-icons-prefix="bx" data-icons="icons" data-show-refresh="true" data-total-field="total"
                    data-trim-on-search="false" data-data-field="projects" data-page-list="[5, 10, 20, 50, 100, 200]"
                    data-search="true" data-side-pagination="server" data-show-columns="true" data-pagination="true"
                    data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                    data-query-params="project_report_query_params">
                    <thead>
                        <tr>
                            <th rowspan="2" data-field="id" data-sortable="true">{{ get_label('id', 'ID') }}</th>
                            <th rowspan="2" data-field="title" data-sortable="true">{{ get_label('title', 'Title') }}</th>
                            <th rowspan="2" data-field="description" data-sortable="true" data-visible="false">{{ get_label('description', 'Description') }}</th>
                            <th colspan="2">{{ get_label('dates', 'Dates') }}</th>
                            <th rowspan="2" data-field="status" data-sortable="true">{{ get_label('status', 'Status') }}</th>
                            <th rowspan="2" data-field="priority" data-sortable="true">{{ get_label('priority', 'Priority') }}</th>
                            <th rowspan="2" data-field="budget.total" data-sortable="true">{{ get_label('budget', 'Budget') }}</th>
                            <th colspan="3">{{ get_label('duration', 'Duration') }}</th>
                            <th colspan="4">{{ get_label('tasks', 'Tasks') }}</th>
                            <th colspan="2">{{ get_label('team', 'Team') }}</th>
                            <!-- Group Clients and Total Clients under one header -->
                            <th colspan="2">{{ get_label('clients', 'Clients') }}</th>
                        </tr>
                        <tr>
                            <th data-field="start_date" data-sortable="true">{{ get_label('start_date', 'Start Date') }}</th>
                            <th data-field="end_date" data-sortable="true">{{ get_label('end_date', 'End Date') }}</th>
                            <th data-field="time.total_days" data-sortable="true">{{ get_label('total_days', 'Total Days') }}</th>
                            <th data-field="time.days_elapsed" data-sortable="true">{{ get_label('days_elapsed', 'Days Elapsed') }}</th>
                            <th data-field="time.days_remaining" data-sortable="true">{{ get_label('days_remaining', 'Days Remaining') }}</th>
                            <th data-field="tasks.total" data-sortable="true">{{ get_label('total', 'Total') }}</th>
                            <th data-field="tasks.due" data-sortable="true">{{ get_label('due', 'Due') }}</th>
                            <th data-field="tasks.overdue" data-sortable="true">{{ get_label('overdue', 'Overdue') }}</th>
                            <th data-field="tasks.overdue_days" data-sortable="true">{{ get_label('overdue_days', 'Overdue Days') }}</th>
                            <th data-field="users">{{ get_label('users', 'Users') }}</th>
                            <th data-field="team.total_members" data-sortable="true">{{ get_label('total', 'Total') }}</th>
                            <!-- Separate Clients and Total Clients under their new group -->
                            <th data-field="clients">{{ get_label('clients', 'Clients') }}</th>
                            <th data-field="total_clients">{{ get_label('total', 'Total') }}</th>
                        </tr>
                    </thead>

                </table>
            </div>
        </div>
    </div>

</div>
<script>
    var projects_report_export_url = "{{ route('reports.export-projects-report') }}";
</script>
<script src="{{ asset('assets/js/pages/projects-report.js') }}"></script>
@endsection
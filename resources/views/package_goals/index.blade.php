@extends('layout')

@section('title')
    <?= get_label('package_goals', 'Package Goals') ?>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ url('home') }}"><?= get_label('home', 'Home') ?></a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ url('projects') }}"><?= get_label('projects', 'Projects') ?></a>
                    </li>
                    <li class="breadcrumb-item active"><?= get_label('package_goals', 'Package Goals') ?></li>
                </ol>
            </nav>
        </div>
        <div>
            @php
                $workspace = App\Models\Workspace::find(getWorkspaceId());
            @endphp
            <span class="badge bg-primary">{{ $workspace->title ?? 'Workspace' }}</span>
        </div>
    </div>

    <!-- Header Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">
                    <i class="bx bx-target-lock text-success"></i>
                    <?= get_label('package_goals_management', 'Package Goals Management') ?>
                </h4>
                <p class="text-muted mb-0"><?= get_label('manage_package_goals_desc', 'Manage package goals for projects and tasks') ?></p>
            </div>
            <div>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#create_package_goal_modal">
                    <i class="bx bx-plus"></i> <?= get_label('create_package_goal', 'Create Package Goal') ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Package Goals Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><?= get_label('package_goals_list', 'Package Goals List') ?></h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="col-auto">
                    <div class="input-group input-group-sm">
                        <input type="text" id="search_package_goals" class="form-control" placeholder="<?= get_label('search', 'Search') ?>...">
                        <button class="btn btn-outline-success" type="button" onclick="refreshPackageGoals()">
                            <i class="bx bx-refresh"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover" id="package_goals_table">
                    <thead>
                        <tr>
                            <th><?= get_label('package_type', 'Package Type') ?></th>
                            <th><?= get_label('title', 'Title') ?></th>
                            <th><?= get_label('target_count', 'Target Count') ?></th>
                            <th><?= get_label('description', 'Description') ?></th>
                            <th><?= get_label('actions', 'Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody id="package_goals_tbody">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div class="text-center mt-3" id="loading_spinner" style="display: none;">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden"><?= get_label('loading', 'Loading') ?>...</span>
                </div>
            </div>
            
            <div class="text-center mt-3" id="no_data_message" style="display: none;">
                <div class="alert alert-info">
                    <i class="bx bx-info-circle"></i>
                    <?= get_label('no_package_goals_found', 'No package goals found. Create your first package goal to get started!') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Package Goal Modal -->
<div class="modal fade" id="create_package_goal_modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" onsubmit="event.preventDefault();store_package_goal(this)">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-plus-circle text-success"></i>
                    <?= get_label('create_package_goal', 'Create Package Goal') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="package_type_id" class="form-label"><?= get_label('package_type', 'Package Type') ?> *</label>
                        <select class="form-select" id="package_type_id" name="package_type_id" required>
                            <option value=""><?= get_label('select_package_type', 'Select Package Type') ?></option>
                            @foreach($packageTypes as $type)
                                <option value="{{ $type->id }}" data-icon="{{ $type->icon }}" data-color="{{ $type->color }}">
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label"><?= get_label('title', 'Goal Title') ?> *</label>
                        <input type="text" class="form-control" id="title" name="title" required placeholder="<?= get_label('example_basic_design_tasks', 'Example: Basic Design Tasks') ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="target_count" class="form-label"><?= get_label('target_count', 'Required Tasks Count') ?> *</label>
                        <input type="number" class="form-control" id="target_count" name="target_count" min="1" max="10000" required placeholder="<?= get_label('example_50', 'Example: 50') ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label"><?= get_label('description', 'Description') ?></label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="<?= get_label('goal_description_placeholder', 'Brief description of this goal') ?>"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= get_label('cancel', 'Cancel') ?></button>
                <button type="submit" class="btn btn-success"><?= get_label('create', 'Create') ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Package Goal Modal -->
<div class="modal fade" id="edit_package_goal_modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" onsubmit="event.preventDefault();update_package_goal(this)">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-edit text-warning"></i>
                    <?= get_label('edit_package_goal', 'Edit Package Goal') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_package_goal_id" name="id">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_package_type_id" class="form-label"><?= get_label('package_type', 'Package Type') ?> *</label>
                        <select class="form-select" id="edit_package_type_id" name="package_type_id" required>
                            <option value=""><?= get_label('select_package_type', 'Select Package Type') ?></option>
                            @foreach($packageTypes as $type)
                                <option value="{{ $type->id }}" data-icon="{{ $type->icon }}" data-color="{{ $type->color }}">
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_title" class="form-label"><?= get_label('title', 'Goal Title') ?> *</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_target_count" class="form-label"><?= get_label('target_count', 'Required Tasks Count') ?> *</label>
                        <input type="number" class="form-control" id="edit_target_count" name="target_count" min="1" max="10000" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="edit_description" class="form-label"><?= get_label('description', 'Description') ?></label>
                    <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">
                            <?= get_label('is_active', 'Is Active') ?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= get_label('cancel', 'Cancel') ?></button>
                <button type="submit" class="btn btn-warning"><?= get_label('update', 'Update') ?></button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('script')
<script>
// Global functions
function store_package_goal(form) {
    const formData = new FormData(form);
    
    $.ajax({
        url: '{{ route("package_goals.store") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.error === false) {
                $('#create_package_goal_modal').modal('hide');
                form.reset();
                toastr.success(response.message);
                loadPackageGoals();
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const response = xhr.responseJSON;
                if (response.errors) {
                    Object.keys(response.errors).forEach(function(key) {
                        toastr.error(response.errors[key][0]);
                    });
                } else if (response.message) {
                    toastr.error(response.message);
                }
            } else if (xhr.status === 500) {
                const response = xhr.responseJSON;
                toastr.error(response.message || '<?= get_label("server_error", "Server error occurred") ?>');
            } else {
                toastr.error('<?= get_label("error_creating_package_goal", "Error creating package goal") ?>');
            }
        }
    });
}

function update_package_goal(form) {
    const formData = new FormData(form);
    const id = $('#edit_package_goal_id').val();
    
    $.ajax({
        url: `{{ url('package-goals') }}/${id}`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-HTTP-Method-Override': 'PUT'
        },
        success: function(response) {
            if (response.error === false) {
                $('#edit_package_goal_modal').modal('hide');
                toastr.success(response.message);
                loadPackageGoals();
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const response = xhr.responseJSON;
                if (response.errors) {
                    Object.keys(response.errors).forEach(function(key) {
                        toastr.error(response.errors[key][0]);
                    });
                } else if (response.message) {
                    toastr.error(response.message);
                }
            } else if (xhr.status === 500) {
                const response = xhr.responseJSON;
                toastr.error(response.message || '<?= get_label("server_error", "Server error occurred") ?>');
            } else {
                toastr.error('<?= get_label("error_updating_package_goal", "Error updating package goal") ?>');
            }
        }
    });
}

function editPackageGoal(id) {
    $.ajax({
        url: `{{ url('package-goals') }}/${id}`,
        method: 'GET',
        success: function(response) {
            if (response.error === false) {
                const packageGoal = response.package_goal;
                
                $('#edit_package_goal_id').val(packageGoal.id);
                $('#edit_package_type_id').val(packageGoal.package_type_id);
                $('#edit_title').val(packageGoal.title);
                $('#edit_target_count').val(packageGoal.target_count);
                $('#edit_description').val(packageGoal.description);
                
                $('#edit_package_goal_modal').modal('show');
            }
        },
        error: function(xhr) {
            toastr.error('<?= get_label("error_loading_package_goal", "Error loading package goal data") ?>');
        }
    });
}

function deletePackageGoal(id) {
    if (confirm('<?= get_label("delete_package_goal_confirmation", "Are you sure you want to delete this goal?") ?>')) {
        $.ajax({
            url: `{{ url('package-goals') }}/${id}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.error === false) {
                    toastr.success(response.message);
                    loadPackageGoals();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('<?= get_label("error_deleting_package_goal", "Error deleting package goal") ?>');
            }
        });
    }
}

function loadPackageGoals() {
    $('#loading_spinner').show();
    $('#no_data_message').hide();
    
    const search = $('#search_package_goals').val();
    
    $.ajax({
        url: '{{ route("package_goals.index") }}',
        method: 'GET',
        data: { search: search },
        headers: {
            'Accept': 'application/json'
        },
        success: function(response) {
            $('#loading_spinner').hide();
            
            if (response.error === false && response.package_goals.length > 0) {
                displayPackageGoals(response.package_goals);
            } else {
                $('#no_data_message').show();
                $('#package_goals_tbody').empty();
            }
        },
        error: function(xhr) {
            $('#loading_spinner').hide();
            toastr.error('<?= get_label("error_loading_package_goals", "Error loading package goals") ?>');
        }
    });
}

function displayPackageGoals(packageGoals) {
    const tbody = $('#package_goals_tbody');
    tbody.empty();
    
    packageGoals.forEach(function(packageGoal) {
        const packageType = packageGoal.package_type;
        
        const row = `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm rounded me-2" style="background-color: ${packageType.color}20;">
                            <i class="${packageType.icon}" style="color: ${packageType.color};"></i>
                        </div>
                        <span>${packageType.name}</span>
                    </div>
                </td>
                <td>
                    <strong>${packageGoal.title}</strong>
                </td>
                <td>
                    <span class="badge bg-info">${packageGoal.target_count} <?= get_label('tasks', 'tasks') ?></span>
                </td>
                <td>
                    <span class="text-muted">${packageGoal.description || '<?= get_label("no_description", "No description") ?>'}</span>
                </td>
                <td>
                    <div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:void(0);" onclick="editPackageGoal(${packageGoal.id})">
                                <i class="bx bx-edit-alt me-1"></i> <?= get_label('edit', 'Edit') ?>
                            </a>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="deletePackageGoal(${packageGoal.id})">
                                <i class="bx bx-trash me-1"></i> <?= get_label('delete', 'Delete') ?>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
        `;
        
        tbody.append(row);
    });
}

function refreshPackageGoals() {
    $('#search_package_goals').val('');
    loadPackageGoals();
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

$(document).ready(function() {
    loadPackageGoals();
    
    // Search functionality
    $('#search_package_goals').on('input', debounce(function() {
        loadPackageGoals();
    }, 300));
});
</script>
@endsection
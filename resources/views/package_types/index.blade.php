@extends('layout')

@section('title')
    <?= get_label('package_types', 'Package Types') ?>
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
                    <li class="breadcrumb-item active"><?= get_label('package_types', 'Package Types') ?></li>
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
                    <i class="bx bx-package text-primary"></i>
                    <?= get_label('package_types_management', 'Package Types Management') ?>
                </h4>
                <p class="text-muted mb-0"><?= get_label('manage_package_types_desc', 'Manage and organize your workspace package types with goals') ?></p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_package_type_modal">
                    <i class="bx bx-plus"></i> <?= get_label('create_package_type', 'Create Package Type') ?>
                </button>
            </div>
        </div>
    </div>



    <!-- Package Types Table -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0"><?= get_label('package_types_list', 'Package Types List') ?></h5>
                </div>
                <div class="col-auto">
                    <div class="input-group input-group-sm">
                        <input type="text" id="search_package_types" class="form-control" placeholder="<?= get_label('search', 'Search') ?>...">
                        <button class="btn btn-outline-primary" type="button" onclick="refreshPackageTypes()">
                            <i class="bx bx-refresh"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover" id="package_types_table">
                    <thead>
                        <tr>
                            <th><?= get_label('package_type', 'Package Type') ?></th>
                            <th><?= get_label('description', 'Description') ?></th>
                            <th><?= get_label('status', 'Status') ?></th>
                            <th><?= get_label('actions', 'Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody id="package_types_tbody">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div class="text-center mt-3" id="loading_spinner" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden"><?= get_label('loading', 'Loading') ?>...</span>
                </div>
            </div>
            
            <div class="text-center mt-3" id="no_data_message" style="display: none;">
                <div class="alert alert-info">
                    <i class="bx bx-info-circle"></i>
                    <?= get_label('no_package_types_found', 'No package types found. Create your first package type to get started!') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Package Type Modal -->
<div class="modal fade" id="create_package_type_modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" onsubmit="event.preventDefault();store_package_type(this)">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-plus-circle text-primary"></i>
                    <?= get_label('create_package_type', 'Create Package Type') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="name" class="form-label"><?= get_label('name', 'Name') ?> *</label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="<?= get_label('example_design_development_content', 'Example: Design, Development, Content') ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="icon" class="form-label"><?= get_label('icon', 'Icon') ?></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i id="icon_preview" class="bx bx-package"></i>
                            </span>
                            <input type="text" class="form-control" id="icon" name="icon" value="bx bx-package" placeholder="bx bx-package">
                        </div>
                        <div class="form-text"><?= get_label('icon_desc', 'Use Boxicons class names (e.g. bx bx-design-tool)') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="color" class="form-label"><?= get_label('color', 'Color') ?></label>
                        <input type="color" class="form-control form-control-color" id="color" name="color" value="#007bff">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label"><?= get_label('description', 'Description') ?></label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="<?= get_label('description_placeholder', 'Brief description of this package type') ?>"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= get_label('close', 'Close') ?></button>
                <button type="submit" class="btn btn-primary"><?= get_label('create', 'Create') ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Package Type Modal -->
<div class="modal fade" id="edit_package_type_modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" onsubmit="event.preventDefault();update_package_type(this)">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-edit text-warning"></i>
                    <?= get_label('edit_package_type', 'Edit Package Type') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_package_type_id" name="id">
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="edit_name" class="form-label"><?= get_label('name', 'Name') ?> *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_icon" class="form-label"><?= get_label('icon', 'Icon') ?></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i id="edit_icon_preview" class="bx bx-package"></i>
                            </span>
                            <input type="text" class="form-control" id="edit_icon" name="icon" placeholder="bx bx-package">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_color" class="form-label"><?= get_label('color', 'Color') ?></label>
                        <input type="color" class="form-control form-control-color" id="edit_color" name="color">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="edit_description" class="form-label"><?= get_label('description', 'Description') ?></label>
                    <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">
                            <?= get_label('is_active', 'Is Active') ?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= get_label('close', 'Close') ?></button>
                <button type="submit" class="btn btn-warning"><?= get_label('update', 'Update') ?></button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('script')
<script>
// Global functions
function store_package_type(form) {
    const formData = new FormData(form);
    
    $.ajax({
        url: '{{ route("package_types.store") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.error === false) {
                $('#create_package_type_modal').modal('hide');
                form.reset();
                toastr.success(response.message);
                loadPackageTypes();
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                Object.keys(errors).forEach(function(key) {
                    toastr.error(errors[key][0]);
                });
            } else {
                toastr.error('<?= get_label("error_creating_package_type", "Error creating package type") ?>');
            }
        }
    });
}

function update_package_type(form) {
    const formData = new FormData(form);
    const id = $('#edit_package_type_id').val();
    
    // Add the PUT method override for Laravel
    formData.append('_method', 'PUT');
    
    $.ajax({
        url: `{{ url('package-types') }}/${id}`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.error === false) {
                $('#edit_package_type_modal').modal('hide');
                toastr.success(response.message);
                loadPackageTypes();
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                Object.keys(errors).forEach(function(key) {
                    toastr.error(errors[key][0]);
                });
            } else {
                toastr.error('<?= get_label("error_updating_package_type", "Error updating package type") ?>');
            }
        }
    });
}

function editPackageType(id) {
    $.ajax({
        url: `{{ url('package-types') }}/${id}`,
        method: 'GET',
        success: function(response) {
            if (response.error === false) {
                const packageType = response.package_type;
                const goal = packageType.package_goal;
                
                $('#edit_package_type_id').val(packageType.id);
                $('#edit_name').val(packageType.name);
                $('#edit_icon').val(packageType.icon);
                $('#edit_color').val(packageType.color);
                $('#edit_description').val(packageType.description);
                $('#edit_is_active').prop('checked', packageType.is_active);
                $('#edit_icon_preview').attr('class', packageType.icon || 'bx bx-package');
                
                $('#edit_package_type_modal').modal('show');
            }
        },
        error: function(xhr) {
            toastr.error('<?= get_label("error_loading_package_type", "Error loading package type details") ?>');
        }
    });
}

function deletePackageType(id) {
    if (confirm('<?= get_label("delete_package_type_confirmation", "Are you sure you want to delete this package type?") ?>')) {
        $.ajax({
            url: `{{ url('package-types') }}/${id}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.error === false) {
                    toastr.success(response.message);
                    loadPackageTypes();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('<?= get_label("error_deleting_package_type", "Error deleting package type") ?>');
            }
        });
    }
}

$(document).ready(function() {
    loadPackageTypes();
    
    // Icon preview functionality
    $('#icon').on('input', function() {
        const iconClass = $(this).val();
        $('#icon_preview').attr('class', iconClass || 'bx bx-package');
    });
    
    $('#edit_icon').on('input', function() {
        const iconClass = $(this).val();
        $('#edit_icon_preview').attr('class', iconClass || 'bx bx-package');
    });
    
    // Search functionality
    $('#search_package_types').on('input', debounce(function() {
        loadPackageTypes();
    }, 300));
});

function loadPackageTypes() {
    $('#loading_spinner').show();
    $('#no_data_message').hide();
    
    const search = $('#search_package_types').val();
    
    $.ajax({
        url: '{{ route("package_types.index") }}',
        method: 'GET',
        data: { search: search },
        headers: {
            'Accept': 'application/json'
        },
        success: function(response) {
            $('#loading_spinner').hide();
            
            if (response.error === false && response.package_types.length > 0) {
                displayPackageTypes(response.package_types);
            } else {
                $('#no_data_message').show();
                $('#package_types_tbody').empty();
            }
        },
        error: function(xhr) {
            $('#loading_spinner').hide();
            toastr.error('<?= get_label("error_loading_package_types", "Error loading package types") ?>');
        }
    });
}

function displayPackageTypes(packageTypes) {
    const tbody = $('#package_types_tbody');
    tbody.empty();
    
    packageTypes.forEach(function(packageType) {
        const statusBadge = packageType.is_active 
            ? '<span class="badge bg-success"><?= get_label("active", "Active") ?></span>'
            : '<span class="badge bg-secondary"><?= get_label("inactive", "Inactive") ?></span>';
        
        const row = `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm rounded me-2" style="background-color: ${packageType.color}20;">
                            <i class="${packageType.icon}" style="color: ${packageType.color};"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">${packageType.name}</h6>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="text-muted">${packageType.description || '<?= get_label("no_description", "No description") ?>'}</span>
                </td>
                <td>${statusBadge}</td>
                <td>
                    <div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:void(0);" onclick="editPackageType(${packageType.id})">
                                <i class="bx bx-edit-alt me-1"></i> <?= get_label('edit', 'Edit') ?>
                            </a>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="deletePackageType(${packageType.id})">
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
</script>
@endsection
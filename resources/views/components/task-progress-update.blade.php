<!-- Task Progress Update Modal -->
<div class="modal fade" id="taskProgressUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-line text-primary"></i>
                    {{ get_label('update_task_progress', 'Update Task Progress') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="progressUpdateForm">
                <div class="modal-body">
                    <input type="hidden" id="taskId" name="task_id">
                    
                    <!-- Task Info -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title" id="taskTitle"></h6>
                            <div id="packageGoalInfo" class="text-muted"></div>
                        </div>
                    </div>
                    
                    <!-- Current Progress -->
                    <div class="mb-3">
                        <label class="form-label">{{ get_label('current_progress', 'Current Progress') }}</label>
                        <div id="currentProgressBar"></div>
                        <small class="text-muted" id="currentProgressText"></small>
                    </div>
                    
                    <!-- Progress Count Input -->
                    <div class="mb-3">
                        <label for="progressCount" class="form-label">
                            {{ get_label('progress_count', 'Progress Count') }}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="decrementProgress()">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="form-control text-center" id="progressCount" 
                                   name="progress_count" min="0" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="incrementProgress()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            <span id="maxProgressText"></span>
                        </div>
                    </div>
                    
                    <!-- Progress Slider -->
                    <div class="mb-3">
                        <label class="form-label">{{ get_label('adjust_progress', 'Adjust Progress') }}</label>
                        <input type="range" class="form-range" id="progressSlider" min="0" 
                               oninput="updateProgressFromSlider(this.value)">
                    </div>
                    
                    <!-- New Progress Preview -->
                    <div class="mb-3">
                        <label class="form-label">{{ get_label('new_progress', 'New Progress') }}</label>
                        <div id="newProgressBar"></div>
                        <small class="text-muted" id="newProgressText"></small>
                    </div>
                    
                    <!-- Progress Notes -->
                    <div class="mb-3">
                        <label for="progressNotes" class="form-label">{{ get_label('progress_notes', 'Progress Notes') }}</label>
                        <textarea class="form-control" id="progressNotes" name="progress_notes" rows="3" 
                                  placeholder="{{ get_label('progress_notes_placeholder', 'Add notes about this progress update...') }}"></textarea>
                    </div>
                    
                    <!-- Goal Completion Alert -->
                    <div id="goalCompletionAlert" class="alert alert-success d-none">
                        <i class="fas fa-trophy"></i>
                        {{ get_label('goal_completed_message', 'Congratulations! You have completed this goal!') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ get_label('cancel', 'Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="updateProgressBtn">
                        <i class="fas fa-save"></i>
                        {{ get_label('update_progress', 'Update Progress') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Progress Update Buttons Component -->
<div class="quick-progress-update d-none" id="quickProgressUpdate">
    <div class="card border-primary">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-tachometer-alt"></i>
                {{ get_label('quick_progress_update', 'Quick Progress Update') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickUpdateProgress(1)">
                        +1
                    </button>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickUpdateProgress(5)">
                        +5
                    </button>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickUpdateProgress(10)">
                        +10
                    </button>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="quickUpdateProgress(-1)">
                        -1
                    </button>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-primary btn-sm" onclick="openProgressModal()">
                        <i class="fas fa-edit"></i>
                        {{ get_label('detailed_update', 'Detailed Update') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentTask = null;
let maxProgress = 0;

function showTaskProgress(taskId) {
    // Fetch task details and show progress update modal
    $.ajax({
        url: `/api/tasks/${taskId}`,
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        success: function(response) {
            if (!response.error && response.data) {
                currentTask = response.data;
                populateProgressModal(currentTask);
                $('#taskProgressUpdateModal').modal('show');
            }
        },
        error: function() {
            showErrorToast('{{ get_label("error_loading_task", "Error loading task details") }}');
        }
    });
}

function populateProgressModal(task) {
    $('#taskId').val(task.id);
    $('#taskTitle').text(task.title);
    
    if (task.package_goal) {
        maxProgress = task.package_goal.target_count;
        $('#packageGoalInfo').html(`
            <strong>${task.package_goal.title}</strong> 
            (${task.package_goal.package_type})
            <br>Target: ${task.package_goal.target_count}
        `);
        $('#maxProgressText').text(`Maximum: ${maxProgress}`);
        $('#progressSlider').attr('max', maxProgress);
        
        // Show quick update if has package goal
        $('#quickProgressUpdate').removeClass('d-none');
    } else {
        $('#packageGoalInfo').text('{{ get_label("no_package_goal", "No package goal assigned") }}');
        $('#maxProgressText').text('');
        maxProgress = 1000; // Default max
        $('#quickProgressUpdate').addClass('d-none');
    }
    
    const currentProgress = task.progress_count || 0;
    $('#progressCount').val(currentProgress).attr('max', maxProgress);
    $('#progressSlider').val(currentProgress);
    
    updateProgressDisplay(currentProgress, currentProgress);
}

function updateProgressDisplay(currentProgress, newProgress) {
    // Current progress display
    const currentPercentage = maxProgress > 0 ? (currentProgress / maxProgress * 100) : 0;
    $('#currentProgressBar').html(`
        <div class="progress">
            <div class="progress-bar bg-info" style="width: ${currentPercentage}%">${currentPercentage.toFixed(1)}%</div>
        </div>
    `);
    $('#currentProgressText').text(`${currentProgress} / ${maxProgress}`);
    
    // New progress display
    const newPercentage = maxProgress > 0 ? (newProgress / maxProgress * 100) : 0;
    const progressColor = newPercentage >= 100 ? 'success' : newPercentage >= 75 ? 'primary' : newPercentage >= 50 ? 'warning' : 'danger';
    
    $('#newProgressBar').html(`
        <div class="progress">
            <div class="progress-bar bg-${progressColor}" style="width: ${newPercentage}%">${newPercentage.toFixed(1)}%</div>
        </div>
    `);
    $('#newProgressText').text(`${newProgress} / ${maxProgress}`);
    
    // Show completion alert
    if (newProgress >= maxProgress) {
        $('#goalCompletionAlert').removeClass('d-none');
    } else {
        $('#goalCompletionAlert').addClass('d-none');
    }
}

function incrementProgress() {
    const current = parseInt($('#progressCount').val()) || 0;
    const newValue = Math.min(current + 1, maxProgress);
    $('#progressCount').val(newValue);
    $('#progressSlider').val(newValue);
    updateProgressDisplay(currentTask.progress_count || 0, newValue);
}

function decrementProgress() {
    const current = parseInt($('#progressCount').val()) || 0;
    const newValue = Math.max(current - 1, 0);
    $('#progressCount').val(newValue);
    $('#progressSlider').val(newValue);
    updateProgressDisplay(currentTask.progress_count || 0, newValue);
}

function updateProgressFromSlider(value) {
    $('#progressCount').val(value);
    updateProgressDisplay(currentTask.progress_count || 0, parseInt(value));
}

function quickUpdateProgress(increment) {
    if (!currentTask) return;
    
    const currentProgress = currentTask.progress_count || 0;
    const newProgress = Math.max(0, Math.min(currentProgress + increment, maxProgress));
    
    updateTaskProgress(currentTask.id, newProgress, 'Quick update');
}

function openProgressModal() {
    if (currentTask) {
        $('#taskProgressUpdateModal').modal('show');
    }
}

// Form submission
$('#progressUpdateForm').on('submit', function(e) {
    e.preventDefault();
    
    const taskId = $('#taskId').val();
    const progressCount = parseInt($('#progressCount').val());
    const notes = $('#progressNotes').val();
    
    updateTaskProgress(taskId, progressCount, notes);
});

function updateTaskProgress(taskId, progressCount, notes = '') {
    $('#updateProgressBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
    
    $.ajax({
        url: '/api/tasks/update-progress',
        method: 'PATCH',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Content-Type': 'application/json'
        },
        data: JSON.stringify({
            task_id: taskId,
            progress_count: progressCount,
            notes: notes
        }),
        success: function(response) {
            if (!response.error) {
                showSuccessToast('{{ get_label("progress_updated", "Progress updated successfully") }}');
                $('#taskProgressUpdateModal').modal('hide');
                
                // Refresh the page or update the UI
                if (typeof refreshTaskList === 'function') {
                    refreshTaskList();
                }
                if (typeof loadAnalyticsData === 'function') {
                    loadAnalyticsData();
                }
                
                // Update current task data
                if (currentTask) {
                    currentTask.progress_count = progressCount;
                }
            } else {
                showErrorToast(response.message);
            }
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || '{{ get_label("error_updating_progress", "Error updating progress") }}';
            showErrorToast(errorMsg);
        },
        complete: function() {
            $('#updateProgressBtn').prop('disabled', false).html('<i class="fas fa-save"></i> {{ get_label("update_progress", "Update Progress") }}');
        }
    });
}

// Progress count input validation
$('#progressCount').on('input', function() {
    const value = parseInt($(this).val());
    const currentProgress = currentTask?.progress_count || 0;
    
    if (value > maxProgress) {
        $(this).val(maxProgress);
    }
    if (value < 0) {
        $(this).val(0);
    }
    
    const finalValue = parseInt($(this).val()) || 0;
    $('#progressSlider').val(finalValue);
    updateProgressDisplay(currentProgress, finalValue);
});
</script>
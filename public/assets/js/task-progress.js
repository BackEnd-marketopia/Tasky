/**
 * Task Progress Management JavaScript
 * Handles progress updates and analytics for tasks with package goals
 */

class TaskProgressManager {
    constructor() {
        this.currentTask = null;
        this.maxProgress = 0;
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadTaskProgress();
    }

    bindEvents() {
        // Progress update form submission
        $(document).on('submit', '#progressUpdateForm', (e) => {
            e.preventDefault();
            this.handleProgressUpdate();
        });

        // Progress count input changes
        $(document).on('input', '#progressCount', (e) => {
            this.handleProgressInput(e.target.value);
        });

        // Progress slider changes
        $(document).on('input', '#progressSlider', (e) => {
            this.updateProgressFromSlider(e.target.value);
        });

        // Quick update buttons
        $(document).on('click', '[data-quick-progress]', (e) => {
            const increment = parseInt($(e.target).data('quick-progress'));
            this.quickUpdateProgress(increment);
        });

        // Increment/decrement buttons
        $(document).on('click', '.progress-increment', () => this.incrementProgress());
        $(document).on('click', '.progress-decrement', () => this.decrementProgress());
    }

    loadTaskProgress() {
        const taskId = this.getTaskIdFromUrl();
        if (taskId) {
            this.fetchTaskDetails(taskId);
        }
    }

    getTaskIdFromUrl() {
        const urlParts = window.location.pathname.split('/');
        const taskIndex = urlParts.indexOf('tasks');
        if (taskIndex !== -1 && urlParts[taskIndex + 2]) {
            return urlParts[taskIndex + 2];
        }
        return null;
    }

    fetchTaskDetails(taskId) {
        $.ajax({
            url: `/api/tasks/${taskId}`,
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            success: (response) => {
                if (!response.error && response.data) {
                    this.currentTask = response.data;
                    this.renderTaskProgress();
                }
            },
            error: () => {
                console.error('Error loading task details');
            }
        });
    }

    renderTaskProgress() {
        if (!this.currentTask) return;

        // Render progress section in task details
        const progressHtml = this.generateProgressHtml();
        
        // Insert progress section after task description or in designated area
        const targetElement = $('#task-progress-section');
        if (targetElement.length) {
            targetElement.html(progressHtml);
        } else {
            // Fallback: insert after task description
            $('.task-description').after(`<div id="task-progress-section">${progressHtml}</div>`);
        }

        this.updateProgressDisplay();
    }

    generateProgressHtml() {
        const task = this.currentTask;
        
        if (!task.package_goal) {
            return `
                <div class="card mt-3">
                    <div class="card-body text-center text-muted">
                        <i class="fas fa-info-circle"></i>
                        No package goal assigned to this task
                    </div>
                </div>
            `;
        }

        return `
            <div class="card mt-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line text-primary"></i>
                        Progress Tracking
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="taskProgressManager.showProgressModal()">
                        <i class="fas fa-edit"></i> Update Progress
                    </button>
                </div>
                <div class="card-body">
                    <!-- Package Goal Info -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <strong>${task.package_goal.title}</strong>
                                <span class="badge ms-2" style="background-color: ${task.package_goal.package_type_color || '#6B7280'};">
                                    ${task.package_goal.package_type}
                                </span>
                            </div>
                            <div class="text-muted">
                                Target: ${task.package_goal.target_count}
                            </div>
                        </div>
                        ${task.package_goal.description ? `<p class="text-muted mt-2 mb-0">${task.package_goal.description}</p>` : ''}
                    </div>

                    <!-- Progress Display -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span>Current Progress</span>
                            <span class="fw-bold" id="progress-text">
                                ${task.progress_count || 0} / ${task.package_goal.target_count}
                            </span>
                        </div>
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar" id="progress-bar" role="progressbar" style="width: 0%">
                                <span id="progress-percentage">0%</span>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-auto">
                                <small class="text-muted">Completed: <span id="completed-count">${task.progress_count || 0}</span></small>
                            </div>
                            <div class="col-auto">
                                <small class="text-muted">Remaining: <span id="remaining-count">0</span></small>
                            </div>
                            <div class="col-auto">
                                <small class="text-muted">Status: <span id="progress-status">Not Started</span></small>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Update Buttons -->
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary progress-decrement">
                            <i class="fas fa-minus"></i> -1
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-quick-progress="1">
                            <i class="fas fa-plus"></i> +1
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-quick-progress="5">
                            +5
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-quick-progress="10">
                            +10
                        </button>
                        <button type="button" class="btn btn-sm btn-success progress-increment">
                            <i class="fas fa-plus"></i> +1
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    updateProgressDisplay() {
        if (!this.currentTask || !this.currentTask.package_goal) return;

        const task = this.currentTask;
        const progress = task.progress_count || 0;
        const target = task.package_goal.target_count;
        const percentage = target > 0 ? Math.round((progress / target) * 100) : 0;
        const remaining = Math.max(0, target - progress);

        // Update progress bar
        const progressBar = $('#progress-bar');
        const progressColor = this.getProgressColor(percentage);
        
        progressBar
            .removeClass('bg-danger bg-warning bg-primary bg-success')
            .addClass(`bg-${progressColor}`)
            .css('width', `${percentage}%`);
        
        $('#progress-percentage').text(`${percentage}%`);
        $('#progress-text').text(`${progress} / ${target}`);
        $('#completed-count').text(progress);
        $('#remaining-count').text(remaining);
        $('#progress-status').text(this.getProgressStatus(percentage));

        // Update max progress for modals
        this.maxProgress = target;
    }

    getProgressColor(percentage) {
        if (percentage >= 100) return 'success';
        if (percentage >= 75) return 'primary';
        if (percentage >= 50) return 'info';
        if (percentage >= 25) return 'warning';
        return 'danger';
    }

    getProgressStatus(percentage) {
        if (percentage >= 100) return 'Completed';
        if (percentage >= 75) return 'On Track';
        if (percentage >= 50) return 'Moderate';
        if (percentage >= 25) return 'Behind';
        return 'Not Started';
    }

    showProgressModal() {
        if (!this.currentTask) return;

        // Populate modal with current task data
        this.populateProgressModal();
        $('#taskProgressUpdateModal').modal('show');
    }

    populateProgressModal() {
        const task = this.currentTask;
        
        $('#taskId').val(task.id);
        $('#taskTitle').text(task.title);
        
        if (task.package_goal) {
            this.maxProgress = task.package_goal.target_count;
            $('#packageGoalInfo').html(`
                <strong>${task.package_goal.title}</strong> 
                (${task.package_goal.package_type})
                <br>Target: ${task.package_goal.target_count}
            `);
            $('#maxProgressText').text(`Maximum: ${this.maxProgress}`);
            $('#progressSlider').attr('max', this.maxProgress);
        }
        
        const currentProgress = task.progress_count || 0;
        $('#progressCount').val(currentProgress).attr('max', this.maxProgress);
        $('#progressSlider').val(currentProgress);
        
        this.updateModalProgressDisplay(currentProgress, currentProgress);
    }

    updateModalProgressDisplay(currentProgress, newProgress) {
        const currentPercentage = this.maxProgress > 0 ? (currentProgress / this.maxProgress * 100) : 0;
        const newPercentage = this.maxProgress > 0 ? (newProgress / this.maxProgress * 100) : 0;
        const progressColor = this.getProgressColor(newPercentage);
        
        // Current progress display
        $('#currentProgressBar').html(`
            <div class="progress">
                <div class="progress-bar bg-info" style="width: ${currentPercentage}%">${currentPercentage.toFixed(1)}%</div>
            </div>
        `);
        $('#currentProgressText').text(`${currentProgress} / ${this.maxProgress}`);
        
        // New progress display
        $('#newProgressBar').html(`
            <div class="progress">
                <div class="progress-bar bg-${progressColor}" style="width: ${newPercentage}%">${newPercentage.toFixed(1)}%</div>
            </div>
        `);
        $('#newProgressText').text(`${newProgress} / ${this.maxProgress}`);
        
        // Show completion alert
        if (newProgress >= this.maxProgress) {
            $('#goalCompletionAlert').removeClass('d-none');
        } else {
            $('#goalCompletionAlert').addClass('d-none');
        }
    }

    handleProgressInput(value) {
        const currentProgress = this.currentTask?.progress_count || 0;
        const numValue = parseInt(value) || 0;
        
        if (numValue > this.maxProgress) {
            $('#progressCount').val(this.maxProgress);
        }
        if (numValue < 0) {
            $('#progressCount').val(0);
        }
        
        const finalValue = parseInt($('#progressCount').val()) || 0;
        $('#progressSlider').val(finalValue);
        this.updateModalProgressDisplay(currentProgress, finalValue);
    }

    updateProgressFromSlider(value) {
        $('#progressCount').val(value);
        const currentProgress = this.currentTask?.progress_count || 0;
        this.updateModalProgressDisplay(currentProgress, parseInt(value));
    }

    incrementProgress() {
        if (!this.currentTask) return;
        const current = this.currentTask.progress_count || 0;
        const newValue = Math.min(current + 1, this.maxProgress);
        this.quickUpdateProgress(1);
    }

    decrementProgress() {
        if (!this.currentTask) return;
        const current = this.currentTask.progress_count || 0;
        if (current > 0) {
            this.quickUpdateProgress(-1);
        }
    }

    quickUpdateProgress(increment) {
        if (!this.currentTask) return;
        
        const currentProgress = this.currentTask.progress_count || 0;
        const newProgress = Math.max(0, Math.min(currentProgress + increment, this.maxProgress));
        
        this.updateTaskProgress(this.currentTask.id, newProgress, `Quick update: ${increment > 0 ? '+' : ''}${increment}`);
    }

    handleProgressUpdate() {
        const taskId = $('#taskId').val();
        const progressCount = parseInt($('#progressCount').val());
        const notes = $('#progressNotes').val();
        
        this.updateTaskProgress(taskId, progressCount, notes);
    }

    updateTaskProgress(taskId, progressCount, notes = '') {
        const updateBtn = $('#updateProgressBtn');
        updateBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        
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
            success: (response) => {
                if (!response.error) {
                    this.showSuccessMessage('Progress updated successfully');
                    $('#taskProgressUpdateModal').modal('hide');
                    
                    // Update current task data
                    this.currentTask.progress_count = progressCount;
                    this.updateProgressDisplay();
                    
                    // Clear notes field
                    $('#progressNotes').val('');
                } else {
                    this.showErrorMessage(response.message);
                }
            },
            error: (xhr) => {
                const errorMsg = xhr.responseJSON?.message || 'Error updating progress';
                this.showErrorMessage(errorMsg);
            },
            complete: () => {
                updateBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Progress');
            }
        });
    }

    showSuccessMessage(message) {
        // Check if toastr is available
        if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else if (typeof showSuccessToast === 'function') {
            showSuccessToast(message);
        } else {
            alert(message);
        }
    }

    showErrorMessage(message) {
        // Check if toastr is available
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else if (typeof showErrorToast === 'function') {
            showErrorToast(message);
        } else {
            alert(message);
        }
    }
}

// Initialize task progress manager when document is ready
let taskProgressManager;
$(document).ready(function() {
    taskProgressManager = new TaskProgressManager();
});

// Global functions for backwards compatibility
function showTaskProgress(taskId) {
    if (taskProgressManager) {
        taskProgressManager.fetchTaskDetails(taskId);
        taskProgressManager.showProgressModal();
    }
}

function updateTaskProgressQuick(taskId, increment) {
    if (taskProgressManager) {
        taskProgressManager.quickUpdateProgress(increment);
    }
}
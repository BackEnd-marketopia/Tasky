<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_type_id',
        'title',
        'target_count',
        'workspace_id',
        'description'
    ];

    protected $casts = [
        'target_count' => 'integer',
    ];

    /**
     * Get the workspace that owns the package goal.
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the package type for this goal.
     */
    public function packageType()
    {
        return $this->belongsTo(PackageType::class);
    }

    /**
     * Get all tasks associated with this package goal.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get completed tasks count for this package goal.
     * Sum all progress_count from associated tasks.
     */
    public function getCompletedTasksCountAttribute()
    {
        return $this->tasks()->sum('progress_count');
    }

    /**
     * Get total tasks count for this package goal.
     * Returns the target count as the total.
     */
    public function getTotalTasksCountAttribute()
    {
        return $this->target_count;
    }

    /**
     * Get remaining tasks count for this package goal.
     */
    public function getRemainingTasksCountAttribute()
    {
        return max(0, $this->target_count - $this->completed_tasks_count);
    }

    /**
     * Get completion percentage for this package goal.
     */
    public function getCompletionPercentageAttribute()
    {
        if ($this->target_count == 0) {
            return 0;
        }

        return round(($this->completed_tasks_count / $this->target_count) * 100, 2);
    }

    /**
     * Check if goal is completed.
     */
    public function getIsCompletedAttribute()
    {
        return $this->completed_tasks_count >= $this->target_count;
    }

    /**
     * Get progress status (behind, on-track, completed, etc.)
     */
    public function getProgressStatusAttribute()
    {
        $percentage = $this->completion_percentage;

        if ($percentage >= 100) {
            return 'completed';
        } elseif ($percentage >= 75) {
            return 'on-track';
        } elseif ($percentage >= 50) {
            return 'moderate';
        } elseif ($percentage >= 25) {
            return 'behind';
        } else {
            return 'not-started';
        }
    }

    /**
     * Scope to filter by workspace
     */
    public function scopeForWorkspace($query, $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    /**
     * Scope to filter active package goals
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

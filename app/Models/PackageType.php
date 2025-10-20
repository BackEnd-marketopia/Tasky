<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'color',
        'description',
        'workspace_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the workspace that owns the package type.
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    // Tasks are not directly related to package types in this system

    /**
     * Get the package goal for this type in the workspace.
     */
    public function packageGoal()
    {
        return $this->hasOne(PackageGoal::class);
    }

    /**
     * Get completion percentage for this package type based on package goals.
     */
    public function getCompletionPercentageAttribute()
    {
        $goal = $this->packageGoal?->target_count ?? 0;
        $current = $this->packageGoal?->current_count ?? 0;
        
        if ($goal == 0) {
            return 0;
        }

        return round(($current / $goal) * 100, 2);
    }

    /**
     * Scope to filter by workspace
     */
    public function scopeForWorkspace($query, $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    /**
     * Scope to get only active package types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

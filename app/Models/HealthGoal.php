<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HealthGoal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'title',
        'description',
        'category',
        'target_value',
        'current_value',
        'unit',
        'start_date',
        'target_date',
        'priority',
        'status',
        'progress',
        'is_achieved',
        'achieved_date',
        'milestones',
        'notes',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'start_date' => 'date',
        'target_date' => 'date',
        'is_achieved' => 'boolean',
        'achieved_date' => 'date',
        'progress' => 'integer',
        'milestones' => 'array',
    ];

    /**
     * Get the patient that owns the health goal.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Scope for active health goals.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for achieved health goals.
     */
    public function scopeAchieved($query)
    {
        return $query->where('is_achieved', true);
    }

    /**
     * Scope for health goals by category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for health goals by priority.
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for overdue health goals.
     */
    public function scopeOverdue($query)
    {
        return $query->where('target_date', '<', now())
                    ->where('status', 'active')
                    ->where('is_achieved', false);
    }

    /**
     * Update progress based on current value.
     */
    public function updateProgress()
    {
        if ($this->target_value > 0) {
            $progress = min(100, ($this->current_value / $this->target_value) * 100);
            $this->update(['progress' => round($progress)]);

            // Check if goal is achieved
            if ($progress >= 100 && !$this->is_achieved) {
                $this->markAsAchieved();
            }
        }
    }

    /**
     * Mark goal as achieved.
     */
    public function markAsAchieved()
    {
        $this->update([
            'is_achieved' => true,
            'achieved_date' => now(),
            'status' => 'completed',
            'progress' => 100,
        ]);
    }

    /**
     * Get progress percentage.
     */
    public function getProgressPercentageAttribute()
    {
        return $this->progress . '%';
    }

    /**
     * Get status color for display.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'green',
            'paused' => 'yellow',
            'completed' => 'blue',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get priority color for display.
     */
    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'high' => 'red',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'gray',
        };
    }

    /**
     * Check if goal is overdue.
     */
    public function getIsOverdueAttribute()
    {
        return $this->target_date->isPast() && 
               $this->status === 'active' && 
               !$this->is_achieved;
    }

    /**
     * Get days remaining.
     */
    public function getDaysRemainingAttribute()
    {
        if ($this->is_achieved || $this->status !== 'active') {
            return 0;
        }

        return max(0, now()->diffInDays($this->target_date, false));
    }
}
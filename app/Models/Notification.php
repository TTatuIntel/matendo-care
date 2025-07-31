<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'patient_id',
        'type',
        'priority',
        'title',
        'message',
        'data',
        'action_url',
        'is_read',
        'read_at',
        'is_actionable',
        'action_taken',
        'action_taken_at',
        'action_taken_by',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'is_actionable' => 'boolean',
        'read_at' => 'datetime',
        'action_taken_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the patient associated with the notification.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who took action on the notification.
     */
    public function actionTaker()
    {
        return $this->belongsTo(User::class, 'action_taken_by');
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope for notifications by priority.
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for critical notifications.
     */
    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }

    /**
     * Scope for actionable notifications.
     */
    public function scopeActionable($query)
    {
        return $query->where('is_actionable', true)
            ->whereNull('action_taken');
    }

    /**
     * Scope for active notifications (not expired).
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Take action on notification.
     */
    public function takeAction($action, $userId = null)
    {
        $this->update([
            'action_taken' => $action,
            'action_taken_at' => now(),
            'action_taken_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Check if notification has expired.
     */
    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get priority color.
     */
    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'gray',
        };
    }

    /**
     * Get priority icon.
     */
    public function getPriorityIconAttribute()
    {
        return match($this->priority) {
            'critical' => 'exclamation-circle',
            'high' => 'exclamation',
            'medium' => 'information-circle',
            'low' => 'check-circle',
            default => 'question-mark-circle',
        };
    }

    /**
     * Create notification for critical vital signs.
     */
    public static function createCriticalVitalSignNotification($patient, $vitalSign, $alerts)
    {
        $doctors = $patient->doctors()->wherePivot('status', 'active')->get();
        
        foreach ($doctors as $doctor) {
            static::create([
                'user_id' => $doctor->user_id,
                'patient_id' => $patient->id,
                'type' => 'critical_vital_signs',
                'priority' => 'critical',
                'title' => 'Critical Vital Signs Alert',
                'message' => "Patient {$patient->user->name} has critical vital signs that require immediate attention.",
                'data' => [
                    'vital_sign_id' => $vitalSign->id,
                    'alerts' => $alerts,
                ],
                'action_url' => route('patients.vitals.show', [$patient->id, $vitalSign->id]),
                'is_actionable' => true,
                'expires_at' => now()->addHours(1),
            ]);
        }
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'type',
        'reason',
        'notes',
        'pre_appointment_notes',
        'post_appointment_notes',
        'is_online',
        'meeting_link',
        'reminder_sent',
        'reminder_sent_at',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_online' => 'boolean',
        'reminder_sent' => 'boolean',
        'reminder_sent_at' => 'datetime',
    ];

    /**
     * Get the patient for the appointment.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor for the appointment.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the comments for the appointment.
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Scope for upcoming appointments.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', now())
            ->whereIn('status', ['scheduled', 'confirmed']);
    }

    /**
     * Scope for past appointments.
     */
    public function scopePast($query)
    {
        return $query->where('appointment_date', '<', now());
    }

    /**
     * Scope for appointments by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for today's appointments.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', today());
    }

    /**
     * Scope for appointments needing reminders.
     */
    public function scopeNeedingReminder($query)
    {
        return $query->where('reminder_sent', false)
            ->where('appointment_date', '>', now())
            ->where('appointment_date', '<=', now()->addHours(24))
            ->whereIn('status', ['scheduled', 'confirmed']);
    }

    /**
     * Get full appointment datetime.
     */
    public function getFullStartDatetimeAttribute()
    {
        return $this->appointment_date->format('Y-m-d') . ' ' . $this->start_time;
    }

    /**
     * Get full appointment end datetime.
     */
    public function getFullEndDatetimeAttribute()
    {
        return $this->appointment_date->format('Y-m-d') . ' ' . $this->end_time;
    }

    /**
     * Check if appointment is today.
     */
    public function getIsTodayAttribute()
    {
        return $this->appointment_date->isToday();
    }

    /**
     * Check if appointment is upcoming.
     */
    public function getIsUpcomingAttribute()
    {
        return $this->appointment_date->isFuture() && 
               in_array($this->status, ['scheduled', 'confirmed']);
    }

    /**
     * Check if appointment is past.
     */
    public function getIsPastAttribute()
    {
        return $this->appointment_date->isPast();
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationInMinutesAttribute()
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        
        return $end->diffInMinutes($start);
    }

    /**
     * Confirm appointment.
     */
    public function confirm()
    {
        $this->update(['status' => 'confirmed']);
    }

    /**
     * Cancel appointment.
     */
    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Mark as completed.
     */
    public function complete($notes = null)
    {
        $this->update([
            'status' => 'completed',
            'post_appointment_notes' => $notes,
        ]);
    }

    /**
     * Send reminder.
     */
    public function sendReminder()
    {
        // Send reminder notification
        Notification::create([
            'user_id' => $this->patient->user_id,
            'patient_id' => $this->patient_id,
            'type' => 'appointment_reminder',
            'priority' => 'medium',
            'title' => 'Appointment Reminder',
            'message' => "You have an appointment with {$this->doctor->user->name} on {$this->appointment_date->format('M d, Y')} at {$this->start_time}",
            'data' => [
                'appointment_id' => $this->id,
            ],
            'action_url' => route('appointments.show', $this->id),
        ]);

        $this->update([
            'reminder_sent' => true,
            'reminder_sent_at' => now(),
        ]);
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'scheduled' => 'blue',
            'confirmed' => 'green',
            'in_progress' => 'yellow',
            'completed' => 'gray',
            'cancelled' => 'red',
            'no_show' => 'orange',
            default => 'gray',
        };
    }
}
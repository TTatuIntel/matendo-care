<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'name',
        'dosage',
        'frequency',
        'frequency_type',
        'instructions',
        'start_date',
        'end_date',
        'prescribed_by',
        'is_active',
        'status',
        'reminder_times',
        'last_taken_at',
        'next_dose_at',
        'adherence_score',
        'side_effects',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'reminder_times' => 'array',
        'side_effects' => 'array',
        'last_taken_at' => 'datetime',
        'next_dose_at' => 'datetime',
        'adherence_score' => 'integer',
    ];

    /**
     * Get the patient that owns the medication.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor who prescribed the medication.
     */
    public function prescriber()
    {
        return $this->belongsTo(Doctor::class, 'prescribed_by');
    }

    /**
     * Scope for active medications.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for medications by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for current medications (not ended).
     */
    public function scopeCurrent($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>', now());
        });
    }

    /**
     * Check if medication is currently active.
     */
    public function getIsCurrentAttribute()
    {
        return $this->is_active && 
               (!$this->end_date || $this->end_date->isFuture());
    }

    /**
     * Get next reminder time.
     */
    public function getNextReminderAttribute()
    {
        if (!$this->reminder_times || !$this->is_active) {
            return null;
        }

        $now = now();
        $today = $now->format('Y-m-d');
        
        foreach ($this->reminder_times as $time) {
            $reminderTime = \Carbon\Carbon::parse($today . ' ' . $time);
            if ($reminderTime->isFuture()) {
                return $reminderTime;
            }
        }

        // If no more reminders today, get first reminder for tomorrow
        $tomorrow = $now->addDay()->format('Y-m-d');
        return \Carbon\Carbon::parse($tomorrow . ' ' . $this->reminder_times[0]);
    }

    /**
     * Mark medication as taken.
     */
    public function markAsTaken()
    {
        $this->update([
            'last_taken_at' => now(),
            'next_dose_at' => $this->calculateNextDose(),
        ]);
    }

    /**
     * Calculate next dose time.
     */
    private function calculateNextDose()
    {
        switch ($this->frequency_type) {
            case 'daily':
                return now()->addDay();
            case 'weekly':
                return now()->addWeek();
            case 'monthly':
                return now()->addMonth();
            default:
                return null;
        }
    }
}
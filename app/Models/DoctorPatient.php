<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorPatient extends Pivot
{
    use SoftDeletes;

    protected $table = 'doctor_patients';

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'status',
        'assigned_date',
        'termination_date',
        'notes',
        'is_primary',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'termination_date' => 'date',
        'is_primary' => 'boolean',
    ];

    /**
     * Get the doctor.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the patient.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Scope for active relationships.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for primary relationships.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Terminate the relationship.
     */
    public function terminate($reason = null)
    {
        $this->update([
            'status' => 'terminated',
            'termination_date' => now(),
            'notes' => $reason ? $this->notes . "\nTermination reason: " . $reason : $this->notes,
        ]);
    }

    /**
     * Make this doctor primary.
     */
    public function makePrimary()
    {
        // Remove primary from other doctors
        static::where('patient_id', $this->patient_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Make this doctor primary
        $this->update(['is_primary' => true]);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'specialization',
        'license_number',
        'hospital_affiliation',
        'qualifications',
        'years_of_experience',
        'consultation_start_time',
        'consultation_end_time',
        'consultation_fee',
        'is_available',
        'accepts_emergency',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'accepts_emergency' => 'boolean',
        'consultation_fee' => 'decimal:2',
        'consultation_start_time' => 'datetime:H:i',
        'consultation_end_time' => 'datetime:H:i',
    ];

    /**
     * Get the user that owns the doctor profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the patients assigned to the doctor.
     */
    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'doctor_patients')
            ->withPivot('status', 'assigned_date', 'termination_date', 'notes', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Get active patients only.
     */
    public function activePatients()
    {
        return $this->patients()->wherePivot('status', 'active');
    }

    /**
     * Get the appointments for the doctor.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the medications prescribed by the doctor.
     */
    public function prescribedMedications()
    {
        return $this->hasMany(Medication::class, 'prescribed_by');
    }

    /**
     * Scope for available doctors.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope for doctors accepting emergency.
     */
    public function scopeAcceptsEmergency($query)
    {
        return $query->where('accepts_emergency', true);
    }

    /**
     * Scope for doctors by specialization.
     */
    public function scopeSpecialization($query, $specialization)
    {
        return $query->where('specialization', $specialization);
    }

    /**
     * Check if doctor is available at a specific time.
     */
    public function isAvailableAt($time)
    {
        if (!$this->is_available) {
            return false;
        }

        $checkTime = \Carbon\Carbon::parse($time)->format('H:i:s');
        $startTime = \Carbon\Carbon::parse($this->consultation_start_time)->format('H:i:s');
        $endTime = \Carbon\Carbon::parse($this->consultation_end_time)->format('H:i:s');

        return $checkTime >= $startTime && $checkTime <= $endTime;
    }

    /**
     * Get doctor's full name with title.
     */
    public function getFullNameAttribute()
    {
        return "Dr. " . $this->user->name;
    }

    /**
     * Get upcoming appointments count.
     */
    public function getUpcomingAppointmentsCountAttribute()
    {
        return $this->appointments()
            ->where('appointment_date', '>=', now())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->count();
    }
}
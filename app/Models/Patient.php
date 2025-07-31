<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'patient_id',
        'insurance_number',
        'insurance_provider',
        'medical_history',
        'current_medications',
        'allergies',
        'chronic_conditions',
        'primary_physician',
        'height',
        'weight',
        'last_checkup_date',
        'next_appointment_date',
        'risk_level',
    ];

    protected $casts = [
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'last_checkup_date' => 'date',
        'next_appointment_date' => 'date',
    ];

    /**
     * Get the user that owns the patient profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the doctors assigned to the patient.
     */
    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_patients')
            ->withPivot('status', 'assigned_date', 'termination_date', 'notes', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Get the primary doctor.
     */
    public function primaryDoctor()
    {
        return $this->doctors()->wherePivot('is_primary', true)->first();
    }

    /**
     * Get the medical records for the patient.
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    /**
     * Get the vital signs for the patient.
     */
    public function vitalSigns()
    {
        return $this->hasMany(VitalSign::class);
    }

    /**
     * Get the documents for the patient.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get the appointments for the patient.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get upcoming appointments for the patient.
     */
    public function upcomingAppointments()
    {
        return $this->appointments()
                    ->where('appointment_date', '>=', now())
                    ->whereIn('status', ['scheduled', 'confirmed'])
                    ->orderBy('appointment_date')
                    ->orderBy('start_time');
    }

    /**
     * Get the medications for the patient.
     */
    public function medications()
    {
        return $this->hasMany(Medication::class);
    }

    /**
     * Get active medications for the patient.
     */
    public function activeMedications()
    {
        return $this->medications()->where('is_active', true);
    }

    /**
     * Get the health goals for the patient.
     */
    public function healthGoals()
    {
        return $this->hasMany(HealthGoal::class);
    }

    /**
     * Get the notifications for the patient.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the temporary access logs for the patient.
     */
    public function tempAccessLogs()
    {
        return $this->hasMany(TempAccessLog::class);
    }

    /**
     * Scope for high-risk patients.
     */
    public function scopeHighRisk($query)
    {
        return $query->whereIn('risk_level', ['high', 'critical']);
    }

    /**
     * Scope for patients by risk level.
     */
    public function scopeRiskLevel($query, $level)
    {
        return $query->where('risk_level', $level);
    }

    /**
     * Get the latest vital signs.
     */
    public function getLatestVitalSignsAttribute()
    {
        return $this->vitalSigns()->latest()->first();
    }

    /**
     * Calculate BMI from height and weight.
     */
    public function getBmiAttribute()
    {
        if ($this->height && $this->weight) {
            $heightInMeters = $this->height / 100;
            return round($this->weight / ($heightInMeters * $heightInMeters), 1);
        }
        return null;
    }

    /**
     * Get active medications count.
     */
    public function getActiveMedicationsCountAttribute()
    {
        return $this->medications()->where('is_active', true)->count();
    }

    /**
     * Check if patient has critical vital signs.
     */
    public function hasCriticalVitals()
    {
        return $this->vitalSigns()
            ->where('is_critical', true)
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();
    }

    /**
     * Generate unique patient ID.
     */
    public static function generatePatientId()
    {
        $prefix = 'PAT';
        $year = date('Y');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $year . $random;
    }
}
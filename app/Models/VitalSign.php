<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VitalSign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'systolic',
        'diastolic',
        'heart_rate',
        'temperature',
        'respiratory_rate',
        'oxygen_saturation',
        'blood_sugar',
        'weight',
        'height',
        'bmi',
        'pain_level',
        'mood',
        'is_critical',
        'alerts',
        'recorded_by',
    ];

    protected $casts = [
        'temperature' => 'decimal:1',
        'blood_sugar' => 'decimal:1',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'bmi' => 'decimal:1',
        'is_critical' => 'boolean',
        'alerts' => 'array',
    ];

    /**
     * Get the patient that owns the vital signs.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who recorded the vital signs.
     */
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Scope for critical vital signs.
     */
    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }

    /**
     * Scope for recent vital signs.
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Calculate BMI if height and weight are present.
     */
    public function calculateBmi()
    {
        if ($this->height && $this->weight) {
            $heightInMeters = $this->height / 100;
            $this->bmi = round($this->weight / ($heightInMeters * $heightInMeters), 1);
            $this->save();
        }
    }

    /**
     * Check vital signs and set alerts.
     */
    public function checkVitals()
    {
        $alerts = [];
        $isCritical = false;

        // Blood Pressure
        if ($this->systolic) {
            if ($this->systolic >= 180 || $this->diastolic >= 120) {
                $alerts[] = ['type' => 'danger', 'message' => 'Hypertensive Crisis: Immediate medical attention required'];
                $isCritical = true;
            } elseif ($this->systolic >= 140 || $this->diastolic >= 90) {
                $alerts[] = ['type' => 'warning', 'message' => 'High Blood Pressure detected'];
            } elseif ($this->systolic < 90 || $this->diastolic < 60) {
                $alerts[] = ['type' => 'warning', 'message' => 'Low Blood Pressure detected'];
            }
        }

        // Heart Rate
        if ($this->heart_rate) {
            if ($this->heart_rate > 120) {
                $alerts[] = ['type' => 'danger', 'message' => 'Tachycardia: High heart rate detected'];
                $isCritical = true;
            } elseif ($this->heart_rate > 100) {
                $alerts[] = ['type' => 'warning', 'message' => 'Elevated heart rate'];
            } elseif ($this->heart_rate < 50) {
                $alerts[] = ['type' => 'warning', 'message' => 'Bradycardia: Low heart rate detected'];
            }
        }

        // Temperature
        if ($this->temperature) {
            if ($this->temperature >= 39.5) {
                $alerts[] = ['type' => 'danger', 'message' => 'High Fever: Immediate attention required'];
                $isCritical = true;
            } elseif ($this->temperature >= 38) {
                $alerts[] = ['type' => 'warning', 'message' => 'Fever detected'];
            } elseif ($this->temperature < 35) {
                $alerts[] = ['type' => 'warning', 'message' => 'Hypothermia: Low body temperature'];
            }
        }

        // Oxygen Saturation
        if ($this->oxygen_saturation) {
            if ($this->oxygen_saturation < 90) {
                $alerts[] = ['type' => 'danger', 'message' => 'Low Oxygen Saturation: Immediate attention required'];
                $isCritical = true;
            } elseif ($this->oxygen_saturation < 95) {
                $alerts[] = ['type' => 'warning', 'message' => 'Below normal oxygen saturation'];
            }
        }

        // Blood Sugar
        if ($this->blood_sugar) {
            if ($this->blood_sugar > 300 || $this->blood_sugar < 50) {
                $alerts[] = ['type' => 'danger', 'message' => 'Critical blood sugar levels'];
                $isCritical = true;
            } elseif ($this->blood_sugar > 180) {
                $alerts[] = ['type' => 'warning', 'message' => 'High blood sugar'];
            } elseif ($this->blood_sugar < 70) {
                $alerts[] = ['type' => 'warning', 'message' => 'Low blood sugar'];
            }
        }

        $this->alerts = $alerts;
        $this->is_critical = $isCritical;
        $this->save();

        return ['alerts' => $alerts, 'is_critical' => $isCritical];
    }

    /**
     * Get blood pressure as string.
     */
    public function getBloodPressureAttribute()
    {
        if ($this->systolic && $this->diastolic) {
            return $this->systolic . '/' . $this->diastolic;
        }
        return null;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'patient_id',
        'category',
        'data',
        'notes',
        'recorded_by',
        'is_critical',
        'requires_attention',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'data' => 'array',
        'is_critical' => 'boolean',
        'requires_attention' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the medical record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the patient that owns the medical record.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who recorded the medical record.
     */
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the user who reviewed the medical record.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the comments for the medical record.
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Scope for critical records.
     */
    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }

    /**
     * Scope for records requiring attention.
     */
    public function scopeRequiresAttention($query)
    {
        return $query->where('requires_attention', true);
    }

    /**
     * Scope for unreviewed records.
     */
    public function scopeUnreviewed($query)
    {
        return $query->whereNull('reviewed_at');
    }

    /**
     * Scope for records by category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for recent records.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark record as reviewed.
     */
    public function markAsReviewed($userId = null)
    {
        $this->update([
            'reviewed_at' => now(),
            'reviewed_by' => $userId ?? auth()->id(),
            'requires_attention' => false,
        ]);
    }

    /**
     * Check if record contains abnormal values.
     */
    public function checkForAbnormalValues()
    {
        $abnormal = false;
        
        switch ($this->category) {
            case 'vitals':
                $data = $this->data;
                
                // Blood pressure
                if (isset($data['systolic']) && ($data['systolic'] > 140 || $data['systolic'] < 90)) {
                    $abnormal = true;
                }
                if (isset($data['diastolic']) && ($data['diastolic'] > 90 || $data['diastolic'] < 60)) {
                    $abnormal = true;
                }
                
                // Heart rate
                if (isset($data['heart_rate']) && ($data['heart_rate'] > 100 || $data['heart_rate'] < 60)) {
                    $abnormal = true;
                }
                
                // Temperature
                if (isset($data['temperature']) && ($data['temperature'] > 38 || $data['temperature'] < 36)) {
                    $abnormal = true;
                }
                break;
                
            case 'labs':
                // Add lab value checks here
                break;
        }
        
        if ($abnormal) {
            $this->update([
                'is_critical' => true,
                'requires_attention' => true,
            ]);
        }
        
        return $abnormal;
    }

    /**
     * Get formatted data for display.
     */
    public function getFormattedDataAttribute()
    {
        $formatted = [];
        
        foreach ($this->data as $key => $value) {
            $formatted[ucwords(str_replace('_', ' ', $key))] = $value;
        }
        
        return $formatted;
    }
}
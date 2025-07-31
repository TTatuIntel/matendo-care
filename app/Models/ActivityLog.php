<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'session_id',
        'additional_data',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'additional_data' => 'array',
    ];

    public $timestamps = false;

    /**
     * Get the user that performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject of the activity log.
     */
    public function subject()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }
        
        return null;
    }

    /**
     * Scope for activities by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for activities by action.
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for activities by model.
     */
    public function scopeForModel($query, $modelType, $modelId = null)
    {
        $query->where('model_type', $modelType);
        
        if ($modelId) {
            $query->where('model_id', $modelId);
        }
        
        return $query;
    }

    /**
     * Scope for activities in date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for today's activities.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Log activity.
     */
    public static function log($action, $description, $model = null, $oldValues = null, $newValues = null, $additionalData = null)
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'additional_data' => $additionalData,
        ]);
    }

    /**
     * Log login activity.
     */
    public static function logLogin($user)
    {
        return static::log(
            'login',
            "User {$user->name} logged in",
            $user,
            null,
            null,
            ['login_time' => now()->toDateTimeString()]
        );
    }

    /**
     * Log logout activity.
     */
    public static function logLogout($user)
    {
        return static::log(
            'logout',
            "User {$user->name} logged out",
            $user,
            null,
            null,
            ['logout_time' => now()->toDateTimeString()]
        );
    }

    /**
     * Log create activity.
     */
    public static function logCreate($model, $description = null)
    {
        $modelName = class_basename($model);
        $desc = $description ?? "Created new {$modelName}";
        
        return static::log(
            'create',
            $desc,
            $model,
            null,
            $model->toArray()
        );
    }

    /**
     * Log update activity.
     */
    public static function logUpdate($model, $oldValues, $description = null)
    {
        $modelName = class_basename($model);
        $desc = $description ?? "Updated {$modelName}";
        
        return static::log(
            'update',
            $desc,
            $model,
            $oldValues,
            $model->toArray()
        );
    }

    /**
     * Log delete activity.
     */
    public static function logDelete($model, $description = null)
    {
        $modelName = class_basename($model);
        $desc = $description ?? "Deleted {$modelName}";
        
        return static::log(
            'delete',
            $desc,
            $model,
            $model->toArray(),
            null
        );
    }

    /**
     * Log view activity.
     */
    public static function logView($model, $description = null)
    {
        $modelName = class_basename($model);
        $desc = $description ?? "Viewed {$modelName}";
        
        return static::log(
            'view',
            $desc,
            $model
        );
    }

    /**
     * Get action icon.
     */
    public function getActionIconAttribute()
    {
        return match($this->action) {
            'login' => 'login',
            'logout' => 'logout',
            'create' => 'plus-circle',
            'update' => 'pencil',
            'delete' => 'trash',
            'view' => 'eye',
            default => 'information-circle',
        };
    }

    /**
     * Get action color.
     */
    public function getActionColorAttribute()
    {
        return match($this->action) {
            'login' => 'green',
            'logout' => 'gray',
            'create' => 'blue',
            'update' => 'yellow',
            'delete' => 'red',
            'view' => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get changes made.
     */
    public function getChangesAttribute()
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        
        foreach ($this->new_values as $key => $newValue) {
            if (isset($this->old_values[$key]) && $this->old_values[$key] != $newValue) {
                $changes[$key] = [
                    'old' => $this->old_values[$key],
                    'new' => $newValue,
                ];
            }
        }
        
        return $changes;
    }
}
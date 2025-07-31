<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'patient_id',
        'comment',
        'type',
        'is_private',
        'is_important',
        'parent_id',
        'attachments',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'is_important' => 'boolean',
        'attachments' => 'array',
    ];

    /**
     * Get the parent commentable model.
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the patient associated with the comment.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the parent comment.
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the child comments (replies).
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Scope for public comments.
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * Scope for private comments.
     */
    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    /**
     * Scope for important comments.
     */
    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    /**
     * Scope for comments by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for top-level comments (no parent).
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the comment author's name with role.
     */
    public function getAuthorNameAttribute()
    {
        $user = $this->user;
        $name = $user->name;

        if ($user->isDoctor()) {
            return "Dr. {$name}";
        }

        return $name;
    }

    /**
     * Check if comment can be edited by user.
     */
    public function canBeEditedBy($user)
    {
        // Comment can be edited by the author within 30 minutes
        return $this->user_id === $user->id && 
               $this->created_at->diffInMinutes(now()) <= 30;
    }

    /**
     * Check if comment can be deleted by user.
     */
    public function canBeDeletedBy($user)
    {
        // Comment can be deleted by the author or admin
        return $this->user_id === $user->id || $user->isAdmin();
    }

    /**
     * Get formatted comment with mentions.
     */
    public function getFormattedCommentAttribute()
    {
        // Convert @mentions to links
        return preg_replace(
            '/@(\w+)/',
            '<a href="#" class="text-blue-500">@$1</a>',
            e($this->comment)
        );
    }
}
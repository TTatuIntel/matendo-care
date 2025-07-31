<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'patient_id',
        'filename',
        'original_filename',
        'path',
        'mime_type',
        'size',
        'category',
        'description',
        'file_content',
        'uploader_name',
        'uploader_hospital',
        'uploaded_by',
        'is_verified',
        'verified_by',
        'verified_at',
        'version',
        'parent_document_id',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'size' => 'integer',
        'version' => 'integer',
    ];

    /**
     * Get the user that owns the document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the patient that owns the document.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the user who verified the document.
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the parent document (for versioning).
     */
    public function parentDocument()
    {
        return $this->belongsTo(Document::class, 'parent_document_id');
    }

    /**
     * Get the child documents (versions).
     */
    public function versions()
    {
        return $this->hasMany(Document::class, 'parent_document_id');
    }

    /**
     * Scope for verified documents.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for documents by category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Mark document as verified.
     */
    public function markAsVerified($userId = null)
    {
        $this->update([
            'is_verified' => true,
            'verified_by' => $userId ?? auth()->id(),
            'verified_at' => now(),
        ]);
    }

    /**
     * Get the file URL.
     */
    public function getFileUrlAttribute()
    {
        if ($this->file_content) {
            // If storing as base64
            return 'data:' . $this->mime_type . ';base64,' . $this->file_content;
        }
        
        return Storage::url($this->path);
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedSizeAttribute()
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Create a new version of this document.
     */
    public function createNewVersion($attributes = [])
    {
        $parentId = $this->parent_document_id ?: $this->id;
        $latestVersion = Document::where('parent_document_id', $parentId)
            ->orWhere('id', $parentId)
            ->max('version');

        return Document::create(array_merge($attributes, [
            'parent_document_id' => $parentId,
            'version' => $latestVersion + 1,
            'user_id' => $this->user_id,
            'patient_id' => $this->patient_id,
            'category' => $this->category,
        ]));
    }

    /**
     * Get the latest version of this document.
     */
    public function getLatestVersionAttribute()
    {
        if ($this->parent_document_id) {
            return Document::where('parent_document_id', $this->parent_document_id)
                ->orderBy('version', 'desc')
                ->first();
        }

        return $this->versions()->orderBy('version', 'desc')->first() ?: $this;
    }

    /**
     * Check if this is the latest version.
     */
    public function getIsLatestVersionAttribute()
    {
        return $this->id === $this->latest_version->id;
    }

    /**
     * Get download URL attribute.
     */
    public function getDownloadUrlAttribute()
    {
        return route('documents.download', $this->id);
    }

    /**
     * Get view URL attribute.
     */
    public function getViewUrlAttribute()
    {
        return route('documents.view', $this->id);
    }
}
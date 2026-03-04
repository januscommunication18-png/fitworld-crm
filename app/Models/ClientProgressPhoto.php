<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ClientProgressPhoto extends Model
{
    use HasFactory;

    const TYPE_BEFORE = 'before';
    const TYPE_AFTER = 'after';
    const TYPE_FRONT = 'front';
    const TYPE_SIDE = 'side';
    const TYPE_BACK = 'back';
    const TYPE_OTHER = 'other';

    protected $fillable = [
        'client_progress_report_id',
        'photo_type',
        'file_path',
        'file_name',
        'caption',
        'sort_order',
        'is_private',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
        ];
    }

    /**
     * The report this photo belongs to
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(ClientProgressReport::class, 'client_progress_report_id');
    }

    /**
     * Get the photo URL
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk(config('filesystems.uploads'))->url($this->file_path);
    }

    /**
     * Get available photo types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_BEFORE => 'Before',
            self::TYPE_AFTER => 'After',
            self::TYPE_FRONT => 'Front View',
            self::TYPE_SIDE => 'Side View',
            self::TYPE_BACK => 'Back View',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return static::getTypes()[$this->photo_type] ?? 'Unknown';
    }

    /**
     * Delete the file from storage when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($photo) {
            if ($photo->file_path) {
                Storage::disk(config('filesystems.uploads'))->delete($photo->file_path);
            }
        });
    }

    /**
     * Scope: Public photos only (visible to client)
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * Scope: Photos by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('photo_type', $type);
    }

    /**
     * Scope: Order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}

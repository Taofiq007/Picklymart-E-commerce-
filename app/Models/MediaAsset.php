<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id', 'filename', 'original_name', 'disk', 'path', 'mime_type',
        'size_bytes', 'checksum', 'width', 'height', 'status',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_media')
            ->withPivot(['role', 'position'])
            ->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_media')
            ->withPivot(['role', 'position'])
            ->withTimestamps();
    }

    public function fileExists(): bool
    {
        try {
            return Storage::disk($this->disk)->exists($this->path);
        } catch (\Throwable) {
            return false;
        }
    }

    public function publicUrl(): ?string
    {
        if (! $this->fileExists()) {
            return null;
        }

        try {
            return Storage::disk($this->disk)->url($this->path);
        } catch (\Throwable) {
            return null;
        }
    }
}

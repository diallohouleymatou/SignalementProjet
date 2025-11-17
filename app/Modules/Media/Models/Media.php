<?php

namespace App\Modules\Media\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'mediable_id',
        'mediable_type',
        'collection',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'path',
        'thumbnail_path',
        'size',
        'metadata',
        'order',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
        'order' => 'integer',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    // Polymorphic relationship
    public function mediable()
    {
        return $this->morphTo();
    }

    // Accessors
    public function getUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return Storage::disk($this->disk)->url($this->thumbnail_path);
        }
        return $this->url;
    }

    public function getHumanSizeAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Scopes
    public function scopeForCollection($query, string $collection)
    {
        return $query->where('collection', $collection);
    }

    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }

    // Methods
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function delete()
    {
        // Supprimer les fichiers du storage
        Storage::disk($this->disk)->delete($this->path);
        if ($this->thumbnail_path) {
            Storage::disk($this->disk)->delete($this->thumbnail_path);
        }

        return parent::delete();
    }
}

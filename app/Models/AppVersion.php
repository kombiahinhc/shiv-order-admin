<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AppVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'build_number',
        'apk_path',
        'release_notes',
        'is_force_update',
    ];

    protected $casts = [
        'build_number' => 'integer',
        'is_force_update' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::updating(function (AppVersion $version) {
            if ($version->isDirty('apk_path') && $version->getOriginal('apk_path')) {
                Storage::disk('public_storage')->delete($version->getOriginal('apk_path'));
            }
        });

        static::deleted(function (AppVersion $version) {
            if ($version->apk_path) {
                Storage::disk('public_storage')->delete($version->apk_path);
            }
        });
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if (empty($this->apk_path)) {
            return null;
        }

        $path = 'storage/' . ltrim($this->apk_path, '/');
        $baseUrl = rtrim(config('app.url', ''), '/');

        if (empty($baseUrl)) {
            $scheme = request()->secure() ? 'https' : 'http';
            $baseUrl = $scheme . '://' . request()->getHost();
        }

        return $baseUrl . '/' . $path;
    }
}

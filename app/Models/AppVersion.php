<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function getDownloadUrlAttribute(): ?string
    {
        if (empty($this->apk_path)) {
            return null;
        }

        $path = 'storage/' . ltrim($this->apk_path, '/');
        $baseUrl = rtrim(config('app.url', ''), '/');

        if (empty($baseUrl)) {
            // Fallback to request host if app.url is not configured
            $scheme = request()->secure() ? 'https' : 'http';
            $baseUrl = $scheme . '://' . request()->getHost();
        }

        return $baseUrl . '/' . $path;
    }
}

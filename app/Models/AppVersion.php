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

    public function getDownloadUrlAttribute(): string
    {
        $path = 'storage/' . ltrim($this->apk_path, '/');

        $scheme = request()->secure() ? 'https' : 'http';
        $host = request()->getHost();

        // Check if app is in a subdirectory (e.g. /public)
        $baseUrl = config('app.url');
        $prefix = '';
        if ($baseUrl && parse_url($baseUrl, PHP_URL_PATH)) {
            $prefix = rtrim(parse_url($baseUrl, PHP_URL_PATH), '/');
        }

        return $scheme . '://' . $host . $prefix . '/' . $path;
    }
}

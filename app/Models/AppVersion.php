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
        return asset('storage/' . $this->apk_path);
    }
}

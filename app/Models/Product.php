<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'category', 'unit', 'list_price', 'mrp',
        'tax_rate', 'is_tax_inclusive', 'active', 'image_path',
    ];

    protected $casts = [
        'list_price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_tax_inclusive' => 'boolean',
        'active' => 'boolean',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return config('app.url') . '/storage/' . $this->image_path;
    }

    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::updating(function (Product $product) {
            if ($product->isDirty('image_path') && $product->getOriginal('image_path')) {
                $oldPath = $product->getOriginal('image_path');
                if (Storage::disk('public_storage')->exists($oldPath)) {
                    Storage::disk('public_storage')->delete($oldPath);
                }
            }
        });

        static::deleted(function (Product $product) {
            if ($product->image_path && Storage::disk('public_storage')->exists($product->image_path)) {
                Storage::disk('public_storage')->delete($product->image_path);
            }
        });
    }
}

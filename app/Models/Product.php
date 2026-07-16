<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'category', 'unit', 'list_price', 'tax_rate', 'active',
    ];

    protected $casts = [
        'list_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }
}

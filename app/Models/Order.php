<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    const DISCOUNT_NONE = 'none';
    const DISCOUNT_PERCENT = 'percent';
    const DISCOUNT_AMOUNT = 'amount';

    const SYNC_SYNCED = 'synced';
    const SYNC_EDITED = 'edited';

    protected $fillable = [
        'local_uuid', 'salesperson_id', 'shop_id', 'shop_name_snapshot',
        'order_date', 'notes', 'discount_type', 'discount_value',
        'subtotal', 'tax_total', 'grand_total', 'sync_status', 'synced_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'discount_value' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'synced_at' => 'datetime',
    ];

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }
}

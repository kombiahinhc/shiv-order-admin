<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'name', 'owner', 'phone', 'address', 'status',
        'requested_by', 'approved_by', 'approved_at',
        'gst_number', 'image_path', 'latitude', 'longitude',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function approve(User $approver): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfq extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'buyer_id',
        'product_id',
        'quantity',
        'quantity_unit',
        'specifications',
        'packaging_requirements',
        'shipping_requirements',
        'quality_requirements',
        'certification_requirements',
        'target_delivery_date',
        'target_price_range',
        'payment_terms',
        'delivery_location',
        'status',
        'valid_until',
    ];

    protected $casts = [
        'target_delivery_date' => 'date',
        'valid_until' => 'date',
        'quantity' => 'decimal:2',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(RfqQuote::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || $this->valid_until->isPast();
    }
} 
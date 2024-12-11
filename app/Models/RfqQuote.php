<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RfqQuote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rfq_id',
        'seller_id',
        'price',
        'price_unit',
        'quantity',
        'quantity_unit',
        'specifications',
        'packaging_details',
        'shipping_details',
        'quality_certifications',
        'delivery_date',
        'payment_terms',
        'additional_notes',
        'status',
        'valid_until',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'valid_until' => 'date',
        'price' => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || $this->valid_until->isPast();
    }
} 
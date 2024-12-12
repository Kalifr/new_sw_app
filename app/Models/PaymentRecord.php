<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'notes',
        'metadata',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'verified_at' => 'datetime',
    ];

    const STATUS_PENDING_VERIFICATION = 'pending_verification';
    const STATUS_VERIFIED = 'verified';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REFUNDED = 'refunded';

    const METHOD_WIRE_TRANSFER = 'wire_transfer';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function verify(User $verifier, ?string $notes = null): void
    {
        $this->status = self::STATUS_VERIFIED;
        $this->verified_at = now();
        $this->verified_by = $verifier->id;
        $this->notes = $notes;
        $this->save();

        $this->order->updatePaidAmount();
    }

    public function reject(User $verifier, string $reason): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->verified_at = now();
        $this->verified_by = $verifier->id;
        $this->notes = $reason;
        $this->save();

        $this->order->updatePaidAmount();
    }

    public function refund(User $verifier, string $reason): void
    {
        $this->status = self::STATUS_REFUNDED;
        $this->notes = $reason;
        $this->save();

        $this->order->updatePaidAmount();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING_VERIFICATION;
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING_VERIFICATION => 'Pending Verification',
            self::STATUS_VERIFIED => 'Verified',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_REFUNDED => 'Refunded',
            default => ucfirst($this->status),
        };
    }

    public function getPaymentMethodDisplayAttribute(): string
    {
        return match($this->payment_method) {
            self::METHOD_WIRE_TRANSFER => 'Wire Transfer',
            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }
} 
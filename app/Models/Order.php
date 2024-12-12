<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'rfq_id',
        'quote_id',
        'order_number',
        'status',
        'currency',
        'amount',
        'paid_amount',
        'payment_status',
        'shipping_method',
        'shipping_details',
        'inspection_status',
        'payment_due_date',
        'estimated_delivery_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'shipping_details' => 'array',
        'payment_due_date' => 'datetime',
        'estimated_delivery_date' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PROFORMA_ISSUED = 'proforma_issued';
    const STATUS_PAYMENT_PENDING = 'payment_pending';
    const STATUS_PAYMENT_VERIFIED = 'payment_verified';
    const STATUS_IN_PREPARATION = 'in_preparation';
    const STATUS_INSPECTION_PENDING = 'inspection_pending';
    const STATUS_INSPECTION_PASSED = 'inspection_passed';
    const STATUS_READY_FOR_SHIPPING = 'ready_for_shipping';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PARTIAL = 'partial';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_OVERDUE = 'overdue';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    const INSPECTION_STATUS_PENDING = 'pending';
    const INSPECTION_STATUS_IN_PROGRESS = 'in_progress';
    const INSPECTION_STATUS_PASSED = 'passed';
    const INSPECTION_STATUS_FAILED = 'failed';

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(RfqQuote::class, 'quote_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(OrderDocument::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function paymentRecords(): HasMany
    {
        return $this->hasMany(PaymentRecord::class);
    }

    public function inspectionRecords(): HasMany
    {
        return $this->hasMany(InspectionRecord::class);
    }

    public function updateStatus(string $status, ?string $notes = null, array $metadata = []): void
    {
        $this->status = $status;
        $this->save();

        $this->statusHistory()->create([
            'user_id' => auth()->id(),
            'status' => $status,
            'notes' => $notes,
            'metadata' => $metadata,
        ]);
    }

    public function recordPayment(float $amount, string $currency, array $details = []): PaymentRecord
    {
        $payment = $this->paymentRecords()->create([
            'amount' => $amount,
            'currency' => $currency,
            'payment_method' => 'wire_transfer',
            'status' => 'pending_verification',
            'metadata' => $details,
        ]);

        $this->updatePaidAmount();

        return $payment;
    }

    public function updatePaidAmount(): void
    {
        $this->paid_amount = $this->paymentRecords()
            ->where('status', 'verified')
            ->sum('amount');
        
        $this->payment_status = match(true) {
            $this->paid_amount >= $this->amount => self::PAYMENT_STATUS_PAID,
            $this->paid_amount > 0 => self::PAYMENT_STATUS_PARTIAL,
            $this->payment_due_date < now() => self::PAYMENT_STATUS_OVERDUE,
            default => self::PAYMENT_STATUS_PENDING,
        };

        $this->save();
    }

    public function generateOrderNumber(): void
    {
        $prefix = 'SW-' . date('Y');
        $lastOrder = static::where('order_number', 'like', $prefix . '%')
            ->orderByDesc('order_number')
            ->first();

        $sequence = $lastOrder 
            ? (int)substr($lastOrder->order_number, -6) + 1 
            : 1;

        $this->order_number = $prefix . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    public function getProformaInvoice(): ?OrderDocument
    {
        return $this->documents()
            ->where('type', 'proforma_invoice')
            ->latest()
            ->first();
    }

    public function getPurchaseOrder(): ?OrderDocument
    {
        return $this->documents()
            ->where('type', 'purchase_order')
            ->latest()
            ->first();
    }

    public function getLatestInspectionReport(): ?OrderDocument
    {
        return $this->documents()
            ->where('type', 'inspection_report')
            ->latest()
            ->first();
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_PROFORMA_ISSUED,
            self::STATUS_PAYMENT_PENDING,
        ]);
    }

    public function canBeCancelled(): bool
    {
        return !in_array($this->status, [
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ]);
    }

    public function needsInspection(): bool
    {
        return $this->status === self::STATUS_INSPECTION_PENDING;
    }

    public function isReadyForShipping(): bool
    {
        return $this->status === self::STATUS_READY_FOR_SHIPPING;
    }

    public function determineDefaultCurrency(): string
    {
        // European countries use EUR, others use USD
        $eurCountries = [
            'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 
            'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 
            'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'
        ];

        return in_array($this->buyer->profile->country, $eurCountries) 
            ? 'EUR' 
            : 'USD';
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    const TYPE_PROFORMA_INVOICE = 'proforma_invoice';
    const TYPE_PURCHASE_ORDER = 'purchase_order';
    const TYPE_INSPECTION_REPORT = 'inspection_report';
    const TYPE_SHIPPING_DOCUMENT = 'shipping_document';
    const TYPE_PAYMENT_RECEIPT = 'payment_receipt';
    const TYPE_OTHER = 'other';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(DigitalSignature::class, 'document_id');
    }

    public function isSignedByUser(User $user): bool
    {
        return $this->signatures()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function getSignedByAttribute(): array
    {
        return $this->signatures()
            ->with('user:id,name')
            ->get()
            ->map(fn($signature) => [
                'user' => $signature->user->name,
                'signed_at' => $signature->signed_at,
            ])
            ->toArray();
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('documents.download', $this);
    }

    public function getPreviewUrlAttribute(): ?string
    {
        if (!in_array($this->mime_type, ['application/pdf', 'image/jpeg', 'image/png'])) {
            return null;
        }

        return route('documents.preview', $this);
    }

    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            self::TYPE_PROFORMA_INVOICE => 'Proforma Invoice',
            self::TYPE_PURCHASE_ORDER => 'Purchase Order',
            self::TYPE_INSPECTION_REPORT => 'Inspection Report',
            self::TYPE_SHIPPING_DOCUMENT => 'Shipping Document',
            self::TYPE_PAYMENT_RECEIPT => 'Payment Receipt',
            default => 'Other Document',
        };
    }
} 
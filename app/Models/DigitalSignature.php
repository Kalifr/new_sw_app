<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_id',
        'signature_hash',
        'certificate_hash',
        'metadata',
        'signed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'signed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(OrderDocument::class, 'document_id');
    }

    public function verify(): bool
    {
        // Verify the signature hash and certificate
        // This is a placeholder for actual signature verification logic
        return true;
    }

    public static function sign(OrderDocument $document, User $user, array $metadata = []): self
    {
        // Generate signature hash
        $signatureData = json_encode([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'timestamp' => now()->toIso8601String(),
            'document_hash' => hash_file('sha256', storage_path('app/' . $document->file_path)),
        ]);

        $signatureHash = hash('sha256', $signatureData);

        // Create digital signature record
        return static::create([
            'user_id' => $user->id,
            'document_id' => $document->id,
            'signature_hash' => $signatureHash,
            'metadata' => array_merge($metadata, [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'signed_from' => request()->fullUrl(),
            ]),
            'signed_at' => now(),
        ]);
    }

    public function getSignatureDetailsAttribute(): array
    {
        return [
            'signer' => $this->user->name,
            'signed_at' => $this->signed_at->format('M d, Y H:i:s'),
            'signature_hash' => $this->signature_hash,
            'ip_address' => $this->metadata['ip_address'] ?? null,
            'location' => $this->metadata['location'] ?? null,
        ];
    }
} 
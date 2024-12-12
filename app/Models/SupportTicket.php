<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'category_id',
        'user_id',
        'assigned_to',
        'subject',
        'description',
        'status',
        'priority',
        'metadata',
        'last_response_at',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    const STATUS_NEW = 'new';
    const STATUS_OPEN = 'open';
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    public static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (!$ticket->ticket_number) {
                $ticket->ticket_number = static::generateTicketNumber();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SupportTicketResponse::class, 'ticket_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SupportTicketAttachment::class, 'ticket_id');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(SupportTicketFeedback::class, 'ticket_id');
    }

    public function addResponse(string $content, User $user, array $options = []): SupportTicketResponse
    {
        $response = $this->responses()->create([
            'user_id' => $user->id,
            'content' => $content,
            'response_type' => $options['type'] ?? 'reply',
            'is_internal' => $options['is_internal'] ?? false,
            'email_message_id' => $options['email_message_id'] ?? null,
            'attachments' => $options['attachments'] ?? null,
        ]);

        $this->update([
            'last_response_at' => now(),
            'status' => $options['new_status'] ?? $this->status,
        ]);

        return $response;
    }

    public function updateStatus(string $status, ?User $user = null, ?string $comment = null): void
    {
        $oldStatus = $this->status;
        $this->status = $status;

        if ($status === self::STATUS_RESOLVED) {
            $this->resolved_at = now();
        } elseif ($status === self::STATUS_CLOSED) {
            $this->closed_at = now();
        }

        $this->save();

        if ($comment && $user) {
            $this->addResponse($comment, $user, [
                'type' => 'status_change',
                'metadata' => [
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                ],
            ]);
        }
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_NEW, self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isUrgent(): bool
    {
        return $this->priority === self::PRIORITY_URGENT;
    }

    public function getResolutionTime(): ?int
    {
        if (!$this->resolved_at) {
            return null;
        }

        return $this->created_at->diffInMinutes($this->resolved_at);
    }

    public function getFirstResponseTime(): ?int
    {
        $firstResponse = $this->responses()
            ->where('response_type', 'reply')
            ->where('is_internal', false)
            ->orderBy('created_at')
            ->first();

        if (!$firstResponse) {
            return null;
        }

        return $this->created_at->diffInMinutes($firstResponse->created_at);
    }

    protected static function generateTicketNumber(): string
    {
        $prefix = 'TKT';
        $date = now()->format('ymd');
        $lastTicket = static::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastTicket ? (int)substr($lastTicket->ticket_number, -4) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', [self::STATUS_NEW, self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    public function scopeAssignedTo($query, User $user)
    {
        return $query->where('assigned_to', $user->id);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }
} 
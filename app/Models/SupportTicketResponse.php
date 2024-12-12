<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicketResponse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'content',
        'response_type',
        'is_internal',
        'email_message_id',
        'attachments',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'attachments' => 'array',
    ];

    const TYPE_REPLY = 'reply';
    const TYPE_NOTE = 'note';
    const TYPE_STATUS_CHANGE = 'status_change';
    const TYPE_ASSIGNMENT = 'assignment';
    const TYPE_SYSTEM = 'system';

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SupportTicketAttachment::class, 'response_id');
    }

    public function isReply(): bool
    {
        return $this->response_type === self::TYPE_REPLY;
    }

    public function isNote(): bool
    {
        return $this->response_type === self::TYPE_NOTE;
    }

    public function isStatusChange(): bool
    {
        return $this->response_type === self::TYPE_STATUS_CHANGE;
    }

    public function isAssignment(): bool
    {
        return $this->response_type === self::TYPE_ASSIGNMENT;
    }

    public function isSystem(): bool
    {
        return $this->response_type === self::TYPE_SYSTEM;
    }

    public function isFromSupport(): bool
    {
        return $this->user->hasRole('support');
    }

    public function isFromCustomer(): bool
    {
        return $this->user_id === $this->ticket->user_id;
    }

    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    public function scopeRepliesOnly($query)
    {
        return $query->where('response_type', self::TYPE_REPLY);
    }

    public function scopeNotesOnly($query)
    {
        return $query->where('response_type', self::TYPE_NOTE);
    }

    public function scopeFromSupport($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->whereHas('roles', function ($q) {
                $q->where('name', 'support');
            });
        });
    }

    public function scopeFromCustomer($query)
    {
        return $query->whereColumn('user_id', 'tickets.user_id');
    }

    public function getFormattedContent(): string
    {
        // You can extend this to handle different content formats (markdown, html, etc.)
        return nl2br(e($this->content));
    }

    public function getResponseTypeDisplay(): string
    {
        return match($this->response_type) {
            self::TYPE_REPLY => 'Reply',
            self::TYPE_NOTE => 'Internal Note',
            self::TYPE_STATUS_CHANGE => 'Status Change',
            self::TYPE_ASSIGNMENT => 'Assignment',
            self::TYPE_SYSTEM => 'System',
            default => ucfirst($this->response_type),
        };
    }
} 
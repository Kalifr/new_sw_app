<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'thread_id',
        'user_id',
        'body',
        'type',
        'metadata',
        'email_message_id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): HasMany
    {
        return $this->hasMany(MessageStatus::class);
    }

    public function markAsReadForUser(User $user)
    {
        $this->status()->updateOrCreate(
            ['user_id' => $user->id],
            ['read' => true]
        );
    }

    public function markAsUnreadForUser(User $user)
    {
        $this->status()->updateOrCreate(
            ['user_id' => $user->id],
            ['read' => false]
        );
    }

    public function isReadByUser(User $user): bool
    {
        return $this->status()
            ->where('user_id', $user->id)
            ->where('read', true)
            ->exists();
    }

    public function shouldSendEmailNotification(): bool
    {
        $tenMinutesAgo = now()->subMinutes(10);
        
        return !$this->status()
            ->where('read', true)
            ->orWhere('email_sent', true)
            ->exists() && 
            $this->created_at <= $tenMinutesAgo;
    }

    public function markEmailSentForUser(User $user)
    {
        $this->status()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'email_sent' => true,
                'email_sent_at' => now(),
            ]
        );
    }
} 
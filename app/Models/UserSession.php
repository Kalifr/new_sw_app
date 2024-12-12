<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'started_at',
        'ended_at',
        'device_type',
        'browser',
        'ip_address',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('ended_at');
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function end(): void
    {
        $this->update(['ended_at' => now()]);
    }

    public function getDurationAttribute(): int
    {
        if (!$this->ended_at) {
            return 0;
        }

        return $this->started_at->diffInSeconds($this->ended_at);
    }

    public function getFormattedDurationAttribute(): string
    {
        $duration = $this->duration;
        
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = "{$hours}h";
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes}m";
        }
        if ($seconds > 0 || empty($parts)) {
            $parts[] = "{$seconds}s";
        }

        return implode(' ', $parts);
    }

    public function getDeviceInfoAttribute(): array
    {
        return [
            'device' => $this->device_type,
            'browser' => $this->browser,
            'ip' => $this->ip_address,
            'location' => $this->metadata['location'] ?? null,
            'user_agent' => $this->metadata['user_agent'] ?? null,
        ];
    }
} 
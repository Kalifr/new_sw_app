<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'metric_type',
        'value',
        'date',
        'metadata',
    ];

    protected $casts = [
        'date' => 'date',
        'value' => 'decimal:2',
        'metadata' => 'array',
    ];

    const TYPE_ORDERS_COUNT = 'orders_count';
    const TYPE_ORDERS_VALUE = 'orders_value';
    const TYPE_RFQS_COUNT = 'rfqs_count';
    const TYPE_PRODUCTS_COUNT = 'products_count';
    const TYPE_SEARCH_QUERIES = 'search_queries';
    const TYPE_SESSION_DURATION = 'session_duration';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('metric_type', $type);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function getMetricDisplayNameAttribute(): string
    {
        return match($this->metric_type) {
            self::TYPE_ORDERS_COUNT => 'Orders',
            self::TYPE_ORDERS_VALUE => 'Order Value',
            self::TYPE_RFQS_COUNT => 'RFQs',
            self::TYPE_PRODUCTS_COUNT => 'Products',
            self::TYPE_SEARCH_QUERIES => 'Searches',
            self::TYPE_SESSION_DURATION => 'Session Duration',
            default => ucfirst(str_replace('_', ' ', $this->metric_type)),
        };
    }

    public function getFormattedValueAttribute(): string
    {
        return match($this->metric_type) {
            self::TYPE_ORDERS_VALUE => '$' . number_format($this->value, 2),
            self::TYPE_SESSION_DURATION => $this->formatDuration($this->value),
            default => number_format($this->value),
        };
    }

    protected function formatDuration(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = "{$hours}h";
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes}m";
        }
        if ($remainingSeconds > 0 || empty($parts)) {
            $parts[] = round($remainingSeconds) . "s";
        }

        return implode(' ', $parts);
    }
} 
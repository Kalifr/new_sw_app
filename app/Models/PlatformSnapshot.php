<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'total_users',
        'total_products',
        'total_orders',
        'total_rfqs',
        'total_order_value',
        'average_order_value',
        'category_distribution',
        'location_distribution',
        'additional_metrics',
    ];

    protected $casts = [
        'date' => 'date',
        'total_order_value' => 'decimal:2',
        'average_order_value' => 'decimal:2',
        'category_distribution' => 'array',
        'location_distribution' => 'array',
        'additional_metrics' => 'array',
    ];

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('date');
    }

    public function getGrowthRates(): array
    {
        $previousSnapshot = static::where('date', '<', $this->date)
            ->latest('date')
            ->first();

        if (!$previousSnapshot) {
            return [
                'users' => 0,
                'products' => 0,
                'orders' => 0,
                'rfqs' => 0,
                'order_value' => 0,
            ];
        }

        return [
            'users' => $this->calculateGrowthRate($previousSnapshot->total_users, $this->total_users),
            'products' => $this->calculateGrowthRate($previousSnapshot->total_products, $this->total_products),
            'orders' => $this->calculateGrowthRate($previousSnapshot->total_orders, $this->total_orders),
            'rfqs' => $this->calculateGrowthRate($previousSnapshot->total_rfqs, $this->total_rfqs),
            'order_value' => $this->calculateGrowthRate($previousSnapshot->total_order_value, $this->total_order_value),
        ];
    }

    protected function calculateGrowthRate($previous, $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    public function getTopCategories(int $limit = 5): array
    {
        return collect($this->category_distribution)
            ->sortDesc()
            ->take($limit)
            ->toArray();
    }

    public function getTopLocations(int $limit = 5): array
    {
        return collect($this->location_distribution)
            ->sortDesc()
            ->take($limit)
            ->toArray();
    }

    public function getActiveUsersGrowth(): array
    {
        $current = $this->additional_metrics['active_users_30d'] ?? 0;
        $previous = static::where('date', '<', $this->date)
            ->latest('date')
            ->first()
            ?->additional_metrics['active_users_30d'] ?? 0;

        return [
            'current' => $current,
            'previous' => $previous,
            'growth' => $this->calculateGrowthRate($previous, $current),
        ];
    }
} 
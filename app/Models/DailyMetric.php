<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'metric_type',
        'dimension',
        'value',
        'metadata',
    ];

    protected $casts = [
        'date' => 'date',
        'value' => 'decimal:2',
        'metadata' => 'array',
    ];

    const TYPE_NEW_USERS = 'new_users';
    const TYPE_NEW_PRODUCTS = 'new_products';
    const TYPE_NEW_ORDERS = 'new_orders';
    const TYPE_NEW_RFQS = 'new_rfqs';
    const TYPE_TOTAL_ORDER_VALUE = 'total_order_value';
    const TYPE_AVERAGE_ORDER_VALUE = 'average_order_value';
    const TYPE_ACTIVE_USERS = 'active_users';
    const TYPE_SEARCH_QUERIES = 'search_queries';

    public function scopeOfType($query, string $type)
    {
        return $query->where('metric_type', $type);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeWithDimension($query, string $dimension)
    {
        return $query->where('dimension', $dimension);
    }
} 
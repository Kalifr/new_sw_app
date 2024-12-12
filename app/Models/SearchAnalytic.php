<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'query',
        'results_count',
        'user_id',
        'category',
        'filters',
        'converted',
    ];

    protected $casts = [
        'filters' => 'array',
        'converted' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeConverted($query)
    {
        return $query->where('converted', true);
    }

    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeWithResults($query)
    {
        return $query->where('results_count', '>', 0);
    }

    public function scopeNoResults($query)
    {
        return $query->where('results_count', 0);
    }

    public function scopePopular($query, int $minResults = 1)
    {
        return $query->where('results_count', '>=', $minResults)
            ->select('query', \DB::raw('count(*) as search_count'))
            ->groupBy('query')
            ->orderByDesc('search_count');
    }

    public function markAsConverted(): void
    {
        $this->update(['converted' => true]);
    }

    public function getConversionRateAttribute(): float
    {
        return static::where('query', $this->query)
            ->where('converted', true)
            ->count() / static::where('query', $this->query)
            ->count() * 100;
    }
} 
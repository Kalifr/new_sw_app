<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (!$category->slug) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'category_id');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(SupportFaq::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority')->orderBy('name');
    }

    public function getTicketCount(): int
    {
        return $this->tickets()->count();
    }

    public function getOpenTicketCount(): int
    {
        return $this->tickets()->open()->count();
    }

    public function getAverageResolutionTime(): ?float
    {
        $resolvedTickets = $this->tickets()
            ->whereNotNull('resolved_at')
            ->get();

        if ($resolvedTickets->isEmpty()) {
            return null;
        }

        $totalTime = $resolvedTickets->sum(function ($ticket) {
            return $ticket->getResolutionTime();
        });

        return $totalTime / $resolvedTickets->count();
    }

    public function getPublishedFaqCount(): int
    {
        return $this->faqs()->where('is_published', true)->count();
    }
} 
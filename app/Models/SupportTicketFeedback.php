<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'rating',
        'comments',
        'survey_responses',
    ];

    protected $casts = [
        'rating' => 'integer',
        'survey_responses' => 'array',
    ];

    const RATING_VERY_UNSATISFIED = 1;
    const RATING_UNSATISFIED = 2;
    const RATING_NEUTRAL = 3;
    const RATING_SATISFIED = 4;
    const RATING_VERY_SATISFIED = 5;

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRatingText(): string
    {
        return match($this->rating) {
            self::RATING_VERY_UNSATISFIED => 'Very Unsatisfied',
            self::RATING_UNSATISFIED => 'Unsatisfied',
            self::RATING_NEUTRAL => 'Neutral',
            self::RATING_SATISFIED => 'Satisfied',
            self::RATING_VERY_SATISFIED => 'Very Satisfied',
            default => 'Unknown',
        };
    }

    public function getRatingColor(): string
    {
        return match($this->rating) {
            self::RATING_VERY_UNSATISFIED => 'red-600',
            self::RATING_UNSATISFIED => 'orange-500',
            self::RATING_NEUTRAL => 'yellow-500',
            self::RATING_SATISFIED => 'green-500',
            self::RATING_VERY_SATISFIED => 'green-600',
            default => 'gray-500',
        };
    }

    public function getRatingEmoji(): string
    {
        return match($this->rating) {
            self::RATING_VERY_UNSATISFIED => '😠',
            self::RATING_UNSATISFIED => '☹️',
            self::RATING_NEUTRAL => '😐',
            self::RATING_SATISFIED => '🙂',
            self::RATING_VERY_SATISFIED => '😊',
            default => '❓',
        };
    }

    public function isPositive(): bool
    {
        return $this->rating >= self::RATING_SATISFIED;
    }

    public function isNegative(): bool
    {
        return $this->rating <= self::RATING_UNSATISFIED;
    }

    public function isNeutral(): bool
    {
        return $this->rating === self::RATING_NEUTRAL;
    }

    public function getSurveyResponseValue(string $key): mixed
    {
        return $this->survey_responses[$key] ?? null;
    }

    public function scopePositive($query)
    {
        return $query->where('rating', '>=', self::RATING_SATISFIED);
    }

    public function scopeNegative($query)
    {
        return $query->where('rating', '<=', self::RATING_UNSATISFIED);
    }

    public function scopeNeutral($query)
    {
        return $query->where('rating', self::RATING_NEUTRAL);
    }

    public static function getAverageRating()
    {
        return static::avg('rating');
    }

    public static function getSatisfactionRate()
    {
        $total = static::count();
        if ($total === 0) {
            return 0;
        }

        $satisfied = static::positive()->count();
        return ($satisfied / $total) * 100;
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class SupportFaq extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'category_id',
        'question',
        'answer',
        'helpful_count',
        'not_helpful_count',
        'is_published',
        'related_ticket_ids',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'helpful_count' => 'integer',
        'not_helpful_count' => 'integer',
        'related_ticket_ids' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class, 'category_id');
    }

    public function getHelpfulnessRatio(): float
    {
        $total = $this->helpful_count + $this->not_helpful_count;
        if ($total === 0) {
            return 0;
        }

        return ($this->helpful_count / $total) * 100;
    }

    public function incrementHelpfulCount(): void
    {
        $this->increment('helpful_count');
    }

    public function incrementNotHelpfulCount(): void
    {
        $this->increment('not_helpful_count');
    }

    public function getTotalVotes(): int
    {
        return $this->helpful_count + $this->not_helpful_count;
    }

    public function getRelatedTickets()
    {
        if (!$this->related_ticket_ids) {
            return collect();
        }

        return SupportTicket::whereIn('id', $this->related_ticket_ids)->get();
    }

    public function addRelatedTicket(SupportTicket $ticket): void
    {
        $relatedIds = $this->related_ticket_ids ?? [];
        if (!in_array($ticket->id, $relatedIds)) {
            $relatedIds[] = $ticket->id;
            $this->update(['related_ticket_ids' => $relatedIds]);
        }
    }

    public function removeRelatedTicket(SupportTicket $ticket): void
    {
        $relatedIds = $this->related_ticket_ids ?? [];
        $this->update([
            'related_ticket_ids' => array_values(array_diff($relatedIds, [$ticket->id])),
        ]);
    }

    public function publish(): void
    {
        $this->update(['is_published' => true]);
    }

    public function unpublish(): void
    {
        $this->update(['is_published' => false]);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeDraft($query)
    {
        return $query->where('is_published', false);
    }

    public function scopePopular($query)
    {
        return $query->orderByRaw('(helpful_count + not_helpful_count) DESC');
    }

    public function scopeMostHelpful($query)
    {
        return $query->orderByRaw('(helpful_count / (helpful_count + not_helpful_count)) DESC')
            ->having(DB::raw('(helpful_count + not_helpful_count)'), '>=', 5);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'answer' => $this->answer,
            'category' => $this->category->name,
            'is_published' => $this->is_published,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->is_published;
    }
} 
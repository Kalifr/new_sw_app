<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'inspector_id',
        'status',
        'findings',
        'checklist_results',
        'photos',
        'location',
        'inspection_date',
    ];

    protected $casts = [
        'checklist_results' => 'array',
        'photos' => 'array',
        'inspection_date' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_PASSED = 'passed';
    const STATUS_FAILED = 'failed';
    const STATUS_NEEDS_REVIEW = 'needs_review';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isPassed(): bool
    {
        return $this->status === self::STATUS_PASSED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function needsReview(): bool
    {
        return $this->status === self::STATUS_NEEDS_REVIEW;
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_PASSED => 'Passed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_NEEDS_REVIEW => 'Needs Review',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'gray',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_PASSED => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_NEEDS_REVIEW => 'yellow',
            default => 'gray',
        };
    }

    public function getChecklistSummaryAttribute(): array
    {
        $results = $this->checklist_results ?? [];
        $total = count($results);
        $passed = count(array_filter($results, fn($item) => $item['status'] === 'passed'));
        $failed = count(array_filter($results, fn($item) => $item['status'] === 'failed'));
        $skipped = $total - ($passed + $failed);

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'skipped' => $skipped,
            'score' => $total > 0 ? round(($passed / $total) * 100) : 0,
        ];
    }

    public function getPhotoUrlsAttribute(): array
    {
        return array_map(function($photo) {
            return [
                'url' => asset('storage/' . $photo['path']),
                'caption' => $photo['caption'] ?? null,
                'timestamp' => $photo['timestamp'] ?? null,
            ];
        }, $this->photos ?? []);
    }

    public function addPhoto(string $path, ?string $caption = null): void
    {
        $photos = $this->photos ?? [];
        $photos[] = [
            'path' => $path,
            'caption' => $caption,
            'timestamp' => now()->toIso8601String(),
        ];
        $this->photos = $photos;
        $this->save();
    }

    public function updateChecklist(array $results): void
    {
        $this->checklist_results = $results;
        
        // Automatically determine status based on results
        $summary = $this->getChecklistSummaryAttribute();
        $this->status = match(true) {
            $summary['failed'] > 0 => self::STATUS_FAILED,
            $summary['score'] >= 90 => self::STATUS_PASSED,
            $summary['score'] >= 70 => self::STATUS_NEEDS_REVIEW,
            default => self::STATUS_FAILED,
        };

        $this->save();

        // Update order inspection status
        if ($this->status === self::STATUS_PASSED) {
            $this->order->updateStatus(Order::STATUS_INSPECTION_PASSED);
        }
    }
} 
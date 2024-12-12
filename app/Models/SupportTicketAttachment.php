<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SupportTicketAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'response_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(SupportTicketResponse::class, 'response_id');
    }

    public function getDownloadUrl(): string
    {
        return route('support.attachments.download', $this);
    }

    public function getPreviewUrl(): ?string
    {
        if (!$this->isPreviewable()) {
            return null;
        }

        return route('support.attachments.preview', $this);
    }

    public function isPreviewable(): bool
    {
        return in_array($this->mime_type, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'text/plain',
        ]);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getFileExtension(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIconClass(): string
    {
        return match(true) {
            $this->isImage() => 'fa-image',
            $this->isPdf() => 'fa-file-pdf',
            str_contains($this->mime_type, 'word') => 'fa-file-word',
            str_contains($this->mime_type, 'excel') => 'fa-file-excel',
            str_contains($this->mime_type, 'zip') => 'fa-file-archive',
            str_contains($this->mime_type, 'text') => 'fa-file-text',
            default => 'fa-file',
        };
    }

    public function delete()
    {
        // Delete the actual file
        Storage::delete($this->file_path);

        return parent::delete();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            Storage::delete($attachment->file_path);
        });
    }
} 
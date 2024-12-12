<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_type',
        'entity_type',
        'entity_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    const TYPE_LOGIN = 'login';
    const TYPE_LOGOUT = 'logout';
    const TYPE_CREATE_RFQ = 'create_rfq';
    const TYPE_SUBMIT_QUOTE = 'submit_quote';
    const TYPE_CREATE_ORDER = 'create_order';
    const TYPE_UPDATE_PROFILE = 'update_profile';
    const TYPE_CREATE_PRODUCT = 'create_product';
    const TYPE_SEARCH = 'search';
    const TYPE_VIEW_PRODUCT = 'view_product';
    const TYPE_VIEW_RFQ = 'view_rfq';
    const TYPE_SEND_MESSAGE = 'send_message';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeForEntity($query, Model $entity)
    {
        return $query->where('entity_type', get_class($entity))
            ->where('entity_id', $entity->id);
    }

    public function getActivityDescriptionAttribute(): string
    {
        return match($this->activity_type) {
            self::TYPE_LOGIN => 'Logged in',
            self::TYPE_LOGOUT => 'Logged out',
            self::TYPE_CREATE_RFQ => 'Created a new RFQ',
            self::TYPE_SUBMIT_QUOTE => 'Submitted a quote',
            self::TYPE_CREATE_ORDER => 'Created a new order',
            self::TYPE_UPDATE_PROFILE => 'Updated profile',
            self::TYPE_CREATE_PRODUCT => 'Created a new product',
            self::TYPE_SEARCH => 'Performed a search',
            self::TYPE_VIEW_PRODUCT => 'Viewed a product',
            self::TYPE_VIEW_RFQ => 'Viewed an RFQ',
            self::TYPE_SEND_MESSAGE => 'Sent a message',
            default => ucfirst(str_replace('_', ' ', $this->activity_type)),
        };
    }
} 
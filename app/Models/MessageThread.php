<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageThread extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'thread_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_participants', 'thread_id', 'user_id')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class, 'thread_id')->latest();
    }

    public function addParticipant(User $user)
    {
        $this->participants()->syncWithoutDetaching([$user->id]);
    }

    public function removeParticipant(User $user)
    {
        $this->participants()->detach($user->id);
    }

    public function hasParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    public function markAsReadForUser(User $user)
    {
        $this->participants()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);
    }

    public function unreadMessagesForUser(User $user)
    {
        $lastRead = $this->participants()
            ->where('user_id', $user->id)
            ->first()
            ->pivot
            ->last_read_at;

        return $this->messages()
            ->where('user_id', '!=', $user->id)
            ->where('created_at', '>', $lastRead ?? now())
            ->get();
    }
} 
<?php

namespace App\Policies;

use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessageThreadPolicy
{
    use HandlesAuthorization;

    public function view(User $user, MessageThread $thread): bool
    {
        return $thread->hasParticipant($user);
    }

    public function participate(User $user, MessageThread $thread): bool
    {
        return $thread->hasParticipant($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, MessageThread $thread): bool
    {
        return $thread->hasParticipant($user);
    }
} 
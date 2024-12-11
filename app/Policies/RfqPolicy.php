<?php

namespace App\Policies;

use App\Models\Rfq;
use App\Models\User;

class RfqPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Rfq $rfq): bool
    {
        // Buyer can view their own RFQs
        if ($user->id === $rfq->buyer_id) {
            return true;
        }

        // Seller can view RFQs for their products
        return $user->id === $rfq->product->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Rfq $rfq): bool
    {
        return $user->id === $rfq->buyer_id && $rfq->isOpen();
    }

    public function delete(User $user, Rfq $rfq): bool
    {
        return $user->id === $rfq->buyer_id && $rfq->isOpen();
    }

    public function createQuote(User $user, Rfq $rfq): bool
    {
        // Only the product owner can create quotes
        if ($user->id !== $rfq->product->user_id) {
            return false;
        }

        // Can't create quotes for closed or expired RFQs
        if (!$rfq->isOpen()) {
            return false;
        }

        // Can't create multiple quotes for the same RFQ
        return !$rfq->quotes()->where('seller_id', $user->id)->exists();
    }
} 
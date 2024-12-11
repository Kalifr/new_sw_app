<?php

namespace App\Policies;

use App\Models\RfqQuote;
use App\Models\User;

class RfqQuotePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RfqQuote $quote): bool
    {
        // Buyer can view quotes for their RFQs
        if ($user->id === $quote->rfq->buyer_id) {
            return true;
        }

        // Seller can view their own quotes
        return $user->id === $quote->seller_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, RfqQuote $quote): bool
    {
        // Only the seller can update their quote if it's still pending
        return $user->id === $quote->seller_id && $quote->isPending();
    }

    public function delete(User $user, RfqQuote $quote): bool
    {
        // Only the seller can delete their quote if it's still pending
        return $user->id === $quote->seller_id && $quote->isPending();
    }

    public function updateStatus(User $user, RfqQuote $quote): bool
    {
        // Only the RFQ buyer can update quote status
        return $user->id === $quote->rfq->buyer_id && $quote->isPending();
    }
} 
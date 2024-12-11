<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Rfq;
use App\Notifications\EngagementReminderNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEngagementReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $this->sendIncompleteProfileReminders();
        $this->sendInactiveUserReminders();
        $this->sendPendingRfqReminders();
        $this->sendProductListingReminders();
    }

    protected function sendIncompleteProfileReminders(): void
    {
        // Find users who haven't completed their profile within 3 days of registration
        User::where('created_at', '<=', now()->subDays(3))
            ->whereDoesntHave('profile', function ($query) {
                $query->where('profile_completed', true);
            })
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    $user->notify(new EngagementReminderNotification('incomplete_profile'));
                }
            });
    }

    protected function sendInactiveUserReminders(): void
    {
        // Find users who haven't logged in for 14 days
        User::where('last_login_at', '<=', now()->subDays(14))
            ->whereDoesntHave('notifications', function ($query) {
                $query->where('type', EngagementReminderNotification::class)
                    ->where('created_at', '>=', now()->subDays(7));
            })
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    $user->notify(new EngagementReminderNotification('inactive_user'));
                }
            });
    }

    protected function sendPendingRfqReminders(): void
    {
        // Find sellers with pending RFQs that haven't been responded to
        Rfq::where('status', 'open')
            ->where('created_at', '>=', now()->subDays(7))
            ->whereDoesntHave('quotes')
            ->with('product.user')
            ->chunk(100, function ($rfqs) {
                $sellerRfqs = [];
                
                foreach ($rfqs as $rfq) {
                    $sellerId = $rfq->product->user_id;
                    if (!isset($sellerRfqs[$sellerId])) {
                        $sellerRfqs[$sellerId] = ['count' => 0, 'user' => $rfq->product->user];
                    }
                    $sellerRfqs[$sellerId]['count']++;
                }

                foreach ($sellerRfqs as $data) {
                    $data['user']->notify(new EngagementReminderNotification('pending_rfqs', [
                        'count' => $data['count']
                    ]));
                }
            });
    }

    protected function sendProductListingReminders(): void
    {
        // Find users who haven't listed any products within 7 days of completing their profile
        User::whereHas('profile', function ($query) {
                $query->where('profile_completed', true)
                    ->where('created_at', '<=', now()->subDays(7));
            })
            ->whereDoesntHave('products')
            ->whereDoesntHave('notifications', function ($query) {
                $query->where('type', EngagementReminderNotification::class)
                    ->where('created_at', '>=', now()->subDays(7));
            })
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    $user->notify(new EngagementReminderNotification('product_listing'));
                }
            });
    }
} 
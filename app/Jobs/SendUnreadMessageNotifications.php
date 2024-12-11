<?php

namespace App\Jobs;

use App\Models\Message;
use App\Notifications\UnreadMessageNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendUnreadMessageNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Message::with(['thread.participants', 'user'])
            ->where('created_at', '<=', now()->subMinutes(10))
            ->chunk(100, function ($messages) {
                foreach ($messages as $message) {
                    if ($message->shouldSendEmailNotification()) {
                        $recipients = $message->thread->participants()
                            ->where('user_id', '!=', $message->user_id)
                            ->get();

                        foreach ($recipients as $recipient) {
                            $recipient->notify(new UnreadMessageNotification($message));
                            $message->markEmailSentForUser($recipient);
                        }
                    }
                }
            });
    }
} 
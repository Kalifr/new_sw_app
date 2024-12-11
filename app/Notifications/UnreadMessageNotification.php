<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UnreadMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $threadUrl = url("/messages/threads/{$this->message->thread_id}");
        $replyToken = encrypt([
            'thread_id' => $this->message->thread_id,
            'user_id' => $notifiable->id,
            'expires' => now()->addDays(7)->timestamp,
        ]);

        return (new MailMessage)
            ->subject("New message from {$this->message->user->name}")
            ->greeting("Hi {$notifiable->name},")
            ->line("You have an unread message from {$this->message->user->name}:")
            ->line("\"{$this->message->body}\"")
            ->action('View Conversation', $threadUrl)
            ->line('Or reply to this email to send a message directly.')
            ->withSymfonyMessage(function ($message) use ($replyToken) {
                $message->getHeaders()
                    ->addTextHeader('X-PM-Message-Id', $this->message->id)
                    ->addTextHeader('X-PM-Thread-Id', $this->message->thread_id)
                    ->addTextHeader('X-PM-Reply-Token', $replyToken);
            });
    }

    public function toArray($notifiable): array
    {
        return [
            'message_id' => $this->message->id,
            'thread_id' => $this->message->thread_id,
            'sender_id' => $this->message->user_id,
            'sender_name' => $this->message->user->name,
        ];
    }
} 
<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSupportTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;

    public function __construct(SupportTicket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Support Ticket: ' . $this->ticket->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new support ticket has been created that requires your attention.')
            ->line('Title: ' . $this->ticket->title)
            ->line('Category: ' . $this->ticket->category->name)
            ->line('Priority: ' . ucfirst($this->ticket->priority))
            ->line('Description: ' . str_limit($this->ticket->description, 200))
            ->action('View Ticket', route('support.show', $this->ticket))
            ->line('Please respond to this ticket as soon as possible.');
    }

    public function toArray($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->ticket->title,
            'category' => $this->ticket->category->name,
            'priority' => $this->ticket->priority,
            'user_id' => $this->ticket->user_id,
            'user_name' => $this->ticket->user->name,
        ];
    }
} 
<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\SupportTicketResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;
    protected $response;

    public function __construct(SupportTicket $ticket, SupportTicketResponse $response)
    {
        $this->ticket = $ticket;
        $this->response = $response;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('New Response to Ticket: ' . $this->ticket->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new response has been added to your support ticket.')
            ->line('Ticket Title: ' . $this->ticket->title);

        if (!$this->response->is_internal) {
            $message->line('Response: ' . str_limit($this->response->content, 200));
        }

        if ($this->response->attachments->isNotEmpty()) {
            $message->line('This response includes ' . $this->response->attachments->count() . ' attachment(s).');
        }

        return $message
            ->action('View Ticket', route('support.show', $this->ticket))
            ->line('Thank you for using our support system.');
    }

    public function toArray($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'response_id' => $this->response->id,
            'title' => $this->ticket->title,
            'response_user_id' => $this->response->user_id,
            'response_user_name' => $this->response->user->name,
            'is_internal' => $this->response->is_internal,
            'has_attachments' => $this->response->attachments->isNotEmpty(),
        ];
    }
} 
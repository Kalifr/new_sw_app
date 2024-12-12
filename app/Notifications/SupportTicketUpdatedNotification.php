<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;
    protected $changes;

    public function __construct(SupportTicket $ticket)
    {
        $this->ticket = $ticket;
        $this->changes = $ticket->getChanges();
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Support Ticket Updated: ' . $this->ticket->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your support ticket has been updated.');

        if (isset($this->changes['status'])) {
            $message->line('Status changed to: ' . ucfirst($this->ticket->status));
        }

        if (isset($this->changes['priority'])) {
            $message->line('Priority changed to: ' . ucfirst($this->ticket->priority));
        }

        if (isset($this->changes['assigned_to_id'])) {
            $assignedTo = $this->ticket->assignedTo;
            if ($assignedTo) {
                $message->line('Assigned to: ' . $assignedTo->name);
            } else {
                $message->line('Ticket has been unassigned.');
            }
        }

        return $message
            ->action('View Ticket', route('support.show', $this->ticket))
            ->line('Thank you for your patience.');
    }

    public function toArray($notifiable)
    {
        $changes = [];

        if (isset($this->changes['status'])) {
            $changes['status'] = [
                'from' => $this->ticket->getOriginal('status'),
                'to' => $this->ticket->status,
            ];
        }

        if (isset($this->changes['priority'])) {
            $changes['priority'] = [
                'from' => $this->ticket->getOriginal('priority'),
                'to' => $this->ticket->priority,
            ];
        }

        if (isset($this->changes['assigned_to_id'])) {
            $changes['assigned_to'] = [
                'from' => $this->ticket->getOriginal('assigned_to_id'),
                'to' => $this->ticket->assigned_to_id,
            ];
        }

        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->ticket->title,
            'changes' => $changes,
        ];
    }
} 
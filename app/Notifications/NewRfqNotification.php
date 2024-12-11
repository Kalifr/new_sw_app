<?php

namespace App\Notifications;

use App\Models\Rfq;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRfqNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Rfq $rfq)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New RFQ Received')
            ->greeting('Hello ' . $notifiable->name)
            ->line('You have received a new Request for Quote (RFQ) for your product: ' . $this->rfq->product->name)
            ->line('Quantity: ' . $this->rfq->quantity . ' ' . $this->rfq->quantity_unit)
            ->line('Delivery Location: ' . $this->rfq->delivery_location)
            ->line('Valid Until: ' . $this->rfq->valid_until->format('M d, Y'))
            ->action('View RFQ', route('rfqs.show', $this->rfq))
            ->line('Please review the RFQ and submit your quote if interested.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'rfq_id' => $this->rfq->id,
            'product_id' => $this->rfq->product_id,
            'buyer_id' => $this->rfq->buyer_id,
            'message' => 'New RFQ received for ' . $this->rfq->product->name,
        ];
    }
} 
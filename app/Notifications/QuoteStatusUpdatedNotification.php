<?php

namespace App\Notifications;

use App\Models\RfqQuote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public RfqQuote $quote)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = ucfirst($this->quote->status);
        $message = $this->quote->isAccepted()
            ? 'Congratulations! Your quote has been accepted.'
            : 'Your quote has been rejected.';

        return (new MailMessage)
            ->subject("Quote {$status}")
            ->greeting('Hello ' . $notifiable->name)
            ->line($message)
            ->line('Product: ' . $this->quote->rfq->product->name)
            ->line('RFQ Details:')
            ->line('- Price: ' . $this->quote->price . ' ' . $this->quote->price_unit)
            ->line('- Quantity: ' . $this->quote->quantity . ' ' . $this->quote->quantity_unit)
            ->line('- Delivery Date: ' . $this->quote->delivery_date->format('M d, Y'))
            ->action('View Quote', route('rfqs.show', $this->quote->rfq))
            ->line($this->quote->isAccepted()
                ? 'Please contact the buyer to proceed with the transaction.'
                : 'Thank you for your participation.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'quote_id' => $this->quote->id,
            'rfq_id' => $this->quote->rfq_id,
            'status' => $this->quote->status,
            'message' => 'Your quote has been ' . $this->quote->status,
        ];
    }
} 
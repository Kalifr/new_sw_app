<?php

namespace App\Notifications;

use App\Models\RfqQuote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewQuoteNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('New Quote Received')
            ->greeting('Hello ' . $notifiable->name)
            ->line('You have received a new quote for your RFQ for: ' . $this->quote->rfq->product->name)
            ->line('From: ' . $this->quote->seller->name)
            ->line('Price: ' . $this->quote->price . ' ' . $this->quote->price_unit)
            ->line('Quantity: ' . $this->quote->quantity . ' ' . $this->quote->quantity_unit)
            ->line('Delivery Date: ' . $this->quote->delivery_date->format('M d, Y'))
            ->line('Valid Until: ' . $this->quote->valid_until->format('M d, Y'))
            ->action('View Quote', route('rfqs.show', $this->quote->rfq))
            ->line('Please review the quote and take appropriate action.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'quote_id' => $this->quote->id,
            'rfq_id' => $this->quote->rfq_id,
            'seller_id' => $this->quote->seller_id,
            'message' => 'New quote received from ' . $this->quote->seller->name,
        ];
    }
} 
<?php

namespace App\Notifications;

use App\Models\Rfq;
use Illuminate\Notifications\Messages\MailMessage;

class RfqUpdateNotification extends BaseNotification
{
    protected $rfq;
    protected $action;
    protected $details;

    public function __construct(Rfq $rfq, string $action, array $details = [])
    {
        $this->rfq = $rfq;
        $this->action = $action;
        $this->details = $details;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = $this->getEmailTemplate()
            ->subject("RFQ Update: {$this->rfq->product->name}")
            ->greeting("Hello {$notifiable->name}")
            ->line($this->getUpdateMessage());

        foreach ($this->getDetailLines() as $line) {
            $message->line($line);
        }

        return $message;
    }

    public function toArray($notifiable): array
    {
        return [
            'rfq_id' => $this->rfq->id,
            'action' => $this->action,
            'details' => $this->details,
            'product_name' => $this->rfq->product->name,
        ];
    }

    protected function getActionText(): string
    {
        return 'View RFQ Details';
    }

    protected function getActionUrl(): string
    {
        return route('rfqs.show', $this->rfq);
    }

    protected function getUpdateMessage(): string
    {
        return match($this->action) {
            'new_quote' => 'You have received a new quote for your RFQ.',
            'quote_updated' => 'A quote for your RFQ has been updated.',
            'quote_accepted' => 'Congratulations! Your quote has been accepted.',
            'quote_rejected' => 'Your quote was not selected for this RFQ.',
            'rfq_expired' => 'Your RFQ has expired.',
            'rfq_closed' => 'The RFQ has been closed.',
            default => 'There has been an update to your RFQ.',
        };
    }

    protected function getDetailLines(): array
    {
        return [
            "Product: {$this->rfq->product->name}",
            "Quantity: {$this->rfq->quantity} {$this->rfq->quantity_unit}",
            "Delivery Location: {$this->rfq->delivery_location}",
            "Valid Until: {$this->rfq->valid_until->format('M d, Y')}",
        ];
    }

    protected function getLevel(): string
    {
        return match($this->action) {
            'quote_accepted' => 'success',
            'quote_rejected', 'rfq_expired' => 'error',
            default => 'info',
        };
    }
} 
<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

class EngagementReminderNotification extends BaseNotification
{
    protected $type;
    protected $data;

    public function __construct(string $type, array $data = [])
    {
        $this->type = $type;
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = $this->getEmailTemplate()
            ->subject($this->getSubject())
            ->greeting("Hello {$notifiable->name}")
            ->line($this->getMessage());

        foreach ($this->getDetailLines() as $line) {
            $message->line($line);
        }

        return $message;
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
        ];
    }

    protected function getActionText(): string
    {
        return match($this->type) {
            'incomplete_profile' => 'Complete Your Profile',
            'inactive_user' => 'Explore Platform',
            'pending_rfqs' => 'View RFQs',
            'product_listing' => 'List Your Products',
            default => 'Visit Platform',
        };
    }

    protected function getActionUrl(): string
    {
        return match($this->type) {
            'incomplete_profile' => route('profile.complete'),
            'inactive_user' => route('dashboard'),
            'pending_rfqs' => route('rfqs.index'),
            'product_listing' => route('products.create'),
            default => route('dashboard'),
        };
    }

    protected function getSubject(): string
    {
        return match($this->type) {
            'incomplete_profile' => 'Complete Your Profile to Access All Features',
            'inactive_user' => 'We Miss You! Check Out What\'s New',
            'pending_rfqs' => 'You Have Pending RFQs to Review',
            'product_listing' => 'Start Selling: List Your Products Today',
            default => 'Platform Update',
        };
    }

    protected function getMessage(): string
    {
        return match($this->type) {
            'incomplete_profile' => 'Your profile is almost complete! Take a moment to fill in the remaining details to unlock all platform features.',
            'inactive_user' => 'We noticed you haven\'t visited in a while. There\'s a lot of activity you might be interested in!',
            'pending_rfqs' => 'You have ' . ($this->data['count'] ?? 'several') . ' RFQs waiting for your response.',
            'product_listing' => 'Start reaching more buyers by listing your products on our platform.',
            default => 'Check out what\'s new on our platform.',
        };
    }

    protected function getDetailLines(): array
    {
        return match($this->type) {
            'incomplete_profile' => [
                '✓ Access to RFQ system',
                '✓ Direct messaging with buyers/sellers',
                '✓ Product listing capabilities',
                '✓ Real-time notifications',
            ],
            'inactive_user' => [
                '• New RFQs in your product categories',
                '• Updated platform features',
                '• Growing network of buyers and sellers',
            ],
            'pending_rfqs' => [
                '• Don\'t miss out on potential business opportunities',
                '• Quick and easy quote submission',
                '• Real-time negotiation capabilities',
            ],
            'product_listing' => [
                '• Reach thousands of potential buyers',
                '• Easy product management',
                '• Secure transaction process',
                '• 24/7 support',
            ],
            default => [],
        };
    }
} 
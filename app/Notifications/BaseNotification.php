<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected function getEmailTemplate(): MailMessage
    {
        return (new MailMessage)
            ->theme('selina') // Custom theme matching your brand
            ->greeting($this->getGreeting())
            ->salutation('Best regards,<br>The Selina Team')
            ->action($this->getActionText(), $this->getActionUrl())
            ->level($this->getLevel());
    }

    protected function getGreeting(): string
    {
        return 'Hello!';
    }

    protected function getLevel(): string
    {
        return 'info'; // Can be success, error, or info
    }

    abstract protected function getActionText(): string;
    abstract protected function getActionUrl(): string;
} 
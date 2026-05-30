<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WholesaleAccountApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject(__('Your wholesale account has been approved'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your wholesale partnership request has been approved.'))
            ->line(__('Please create your password using the secure link below, then log in to browse wholesale prices and quantity tiers.'))
            ->action(__('Create Password'), $url)
            ->line(__('This link will expire in :minutes minutes.', ['minutes' => config('auth.passwords.users.expire', 60)]))
            ->line(__('If you did not request a wholesale account, please ignore this email.'));
    }
}

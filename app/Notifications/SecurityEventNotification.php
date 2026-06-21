<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SecurityEventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $event;
    public $details;

    /**
     * Create a new notification instance.
     */
    public function __construct($event, $details = [])
    {
        $this->event = $event;
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = match($this->event) {
            'login' => 'Security Alert: New Login Detected',
            'password_change' => 'Security Alert: Password Changed',
            'failed_login' => 'Security Alert: Failed Login Attempt',
            default => 'Security Alert'
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name);

        switch ($this->event) {
            case 'login':
                $message
                    ->line('A new login was detected on your portfolio admin panel.')
                    ->line('**IP Address:** ' . ($this->details['ip'] ?? 'Unknown'))
                    ->line('**Time:** ' . ($this->details['time'] ?? now()->toDateTimeString()))
                    ->line('**User Agent:** ' . ($this->details['user_agent'] ?? 'Unknown'))
                    ->line('If this was you, no action is required.')
                    ->line('If you did not initiate this login, please change your password immediately.')
                    ->action('View Admin Panel', url('/admin'));
                break;

            case 'password_change':
                $message
                    ->line('Your admin password was recently changed.')
                    ->line('**Time:** ' . ($this->details['time'] ?? now()->toDateTimeString()))
                    ->line('If you initiated this change, no action is required.')
                    ->line('If you did not change your password, please contact support immediately.')
                    ->action('View Admin Panel', url('/admin'));
                break;

            case 'failed_login':
                $message
                    ->line('A failed login attempt was detected on your portfolio admin panel.')
                    ->line('**IP Address:** ' . ($this->details['ip'] ?? 'Unknown'))
                    ->line('**Time:** ' . ($this->details['time'] ?? now()->toDateTimeString()))
                    ->line('**User Agent:** ' . ($this->details['user_agent'] ?? 'Unknown'))
                    ->line('If this was you, please try logging in again.')
                    ->line('If you did not attempt to login, please review your security settings.')
                    ->action('View Admin Panel', url('/admin'));
                break;
        }

        return $message->line('Thank you for using your portfolio application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'details' => $this->details,
        ];
    }
}

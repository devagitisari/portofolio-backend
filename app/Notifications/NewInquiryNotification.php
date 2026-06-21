<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewInquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $inquiry;

    /**
     * Create a new notification instance.
     */
    public function __construct($inquiry)
    {
        $this->inquiry = $inquiry;
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
        return (new MailMessage)
            ->subject('New Inquiry Received - ' . $this->inquiry->subject)
            ->greeting('Hello ' . $notifiable->name)
            ->line('You have received a new inquiry from your portfolio website.')
            ->line('**Name:** ' . $this->inquiry->name)
            ->line('**Email:** ' . $this->inquiry->email)
            ->line('**Subject:** ' . $this->inquiry->subject)
            ->line('**Category:** ' . $this->inquiry->category)
            ->line('')
            ->line('**Message:**')
            ->line($this->inquiry->message)
            ->action('View in Admin Panel', url('/admin/inbox'))
            ->line('Thank you for using your portfolio application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'inquiry_id' => $this->inquiry->id,
            'name' => $this->inquiry->name,
            'email' => $this->inquiry->email,
            'subject' => $this->inquiry->subject,
        ];
    }
}

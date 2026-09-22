<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue as ShouldQueueContract;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdated extends Notification implements ShouldQueueContract
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $application)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Application status updated')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your application for ' . ($this->application->jobPost?->title ?? 'a job') . ' is now ' . $this->application->status . '.')
            ->action('View your applications', url('/profile'))
            ->line('Thank you for using Job Search.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Application Status Updated',
            'message' => 'Your application for ' . ($this->application->jobPost?->title ?? 'a job') . ' is now ' . $this->application->status . '.',
            'application_id' => $this->application->id,
            'job_title' => $this->application->jobPost?->title,
            'status' => $this->application->status,
            'action_url' => '/profile',
            'action_label' => 'View Application',
            'icon_type' => 'status',
        ];
    }
}

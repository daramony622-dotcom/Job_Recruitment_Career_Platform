<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewScheduled extends Notification implements ShouldQueue
{
    use Queueable;

    public $interview;

    /**
     * Create a new notification instance.
     */
    public function __construct($interview)
    {
        $this->interview = $interview;
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
        $jobTitle = $this->interview->jobApplication?->jobPost?->title ?? 'Position';
        $scheduledAt = $this->interview->scheduled_at?->format('F j, Y, g:i a') ?? 'TBD';
        $meetingLink = $this->interview->meeting_link;

        $mail = (new MailMessage)
            ->subject('Interview Scheduled: ' . $jobTitle)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have an upcoming interview scheduled for the position of **' . $jobTitle . '**.')
            ->line('**Date & Time:** ' . $scheduledAt);

        if ($meetingLink) {
            $mail->action('Join Meeting', $meetingLink);
        }

        return $mail->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'interview_id' => $this->interview->id,
            'job_title' => $this->interview->jobApplication?->jobPost?->title,
            'scheduled_at' => $this->interview->scheduled_at,
            'meeting_link' => $this->interview->meeting_link,
            'message' => 'Your interview has been scheduled.',
        ];
    }
}
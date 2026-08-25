<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SendOtpNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $code, protected string $purpose)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->purpose === 'password_reset'
            ? 'Your password reset code'
            : 'Verify your email';

        return (new MailMessage)
            ->subject($subject)
            ->view('mails.PasswordOtp', [
                'user' => $notifiable,
                'code' => $this->code,
                'purpose' => $this->purpose,
                'expiresInMinutes' => 5,
            ]);
    }
}

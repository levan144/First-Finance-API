<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginNotification extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via($notifiable)
    {
        return ['database']; // Or ['mail', 'database'] if you want email too
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Unusual sign in to your aacount.',
            'time' => now(),
        ];
    }
}

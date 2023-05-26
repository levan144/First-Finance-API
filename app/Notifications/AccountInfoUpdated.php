<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountInfoUpdated extends Notification
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
            'message' => 'Your account information has been updated.',
            'time' => now(),
        ];
    }
}

<?php

namespace App\Notifications\Nova;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MessageReceived extends Notification
{
    use Queueable;
    private $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database']; // This can be mail, database, broadcast, nexmo, or slack
    }

    /**
 * Get the Nova representation of the notification.
 *
 * @param  mixed  $notifiable
 * @return array
 */
    public function toNova($notifiable)
    {
        return [
            'message' => $this->message,
            'actionText' => 'View Message',
            'actionUrl' => '/path/to/message',
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
        ];
    }
}

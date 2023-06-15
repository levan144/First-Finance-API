<?php

namespace App\Notifications\Nova;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\Notifications\NovaChannel;
use Laravel\Nova\URL;

class MessageReceived extends Notification
{
    use Queueable;
    private $message, $message_id;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $message_id)
    {
        $this->message = $message;
        $this->ticket_id = $message_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable) {
        return [NovaChannel::class];
    }

    /**
     * Get the Nova representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toNova($notifiable) {
        return (new NovaNotification)
            ->message(__($this->message))
            ->action('See message', URL::remote('/nova/resources/messages/' . $this->ticket_id))
            ->icon('annotation')
            ->type('info');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable, $message_id)
    {
        return [
            'message' => $this->message,
        ];
    }
}

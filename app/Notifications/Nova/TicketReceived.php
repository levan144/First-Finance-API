<?php

namespace App\Notifications\Nova;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\Notifications\NovaChannel;
use Laravel\Nova\URL;

class TicketReceived extends Notification
{
    use Queueable;
    private $message, $ticket_id;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $ticket_id)
    {
        $this->message = $message;
        $this->ticket_id = $ticket_id;
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
            ->action('See ticket', URL::remote('/nova/resources/tickets/' . $this->ticket_id))
            ->icon('ticket')
            ->type('info');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable, $ticket_id)
    {
        return [
            'message' => $this->message,
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExchangeRateChanged extends Notification
{
    use Queueable;
    protected $transactionId;
    
    public function __construct($transactionId)
    {
        
        $this->transactionId = $transactionId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'transaction_id' => $this->transactionId,
            'message' => 'Exchange rate has been changed for transaction.',
            'time' => now(),
        ];
    }
}
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransactionUpdated extends Notification
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
            'message' => 'Your transaction request has been updated.',
            'time' => now(),
        ];
    }
}
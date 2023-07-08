<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransactionRejected extends Notification
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
            'message' => 'Your transaction has been rejected.',
            'time' => now(),
        ];
    }
}

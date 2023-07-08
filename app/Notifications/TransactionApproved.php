<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransactionApproved extends Notification
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
            'message' => 'Your transaction has been approved.',
            'time' => now(),
        ];
    }
}

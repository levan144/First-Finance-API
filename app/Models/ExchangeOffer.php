<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ExchangeRateChanged;

class ExchangeOffer extends Model
{
    use HasFactory;
    protected $fillable = [
        'rate',
        'status',
        'transaction_id',
    ];
    
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
    
    protected static function booted()
{
    static::created(function ($exchangeOffer) {
        $transactionId = $exchangeOffer->transaction_id;
        $user = $exchangeOffer->transaction->user;
        Notification::send($user, new ExchangeRateChanged($transactionId));
    });
}
}

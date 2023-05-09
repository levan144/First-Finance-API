<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'account_name', 
        'bank_id', 
        'iban', 
        'balance', 
        'bic', 
        'user_bank_id', 
        'currency_id'
    ];
    
    protected $casts = [
        'balance' => 'float',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
    
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function userBank()
    {
        return $this->belongsTo(UserBank::class);
    }
    
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function deposit($amount, $currency = 'GEL')
    {
        $convertedAmount = $this->convertCurrency($amount, $currency, $this->currency->code);

        if (!$convertedAmount) {
            return false;
        }

        $this->balance += $convertedAmount;
        $this->save();

        return true;
    }
    
    public function withdraw($amount, $currency = 'GEL')
    {
        $convertedAmount = $this->convertCurrency($amount, $currency, $this->currency->code);

        if (!$convertedAmount || $this->balance < $convertedAmount) {
            return false;
        }

        $this->balance -= $convertedAmount;
        $this->save();

        return true;
    }

    private function convertCurrency($amount, $fromCurrency, $toCurrency)
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $fromRate = CurrencyExchangeRate::where('currency', "{$fromCurrency}/{$toCurrency}")->first();
        $toRate = CurrencyExchangeRate::where('currency', "{$toCurrency}/{$fromCurrency}")->first();

        if (!$fromRate || !$toRate) {
            return null;
        }

        $baseAmount = $amount / $fromRate->rate;

        return $baseAmount * $toRate->rate;
    }
}

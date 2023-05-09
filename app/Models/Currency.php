<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function exchangeRates()
    {
        return $this->hasMany(ExchangeRate::class);
    }
    
    public static function getSymbol($code)
    {
        $currency = static::where('code', strtoupper($code))->first();

        return $currency ? $currency->code : '';
    }

    public function getExchangeRate($toCurrencyCode)
    {
        $exchangeRate = $this->exchangeRates()
            ->where('to_currency_code', $toCurrencyCode)
            ->orderByDesc('created_at')
            ->first();

        return optional($exchangeRate)->rate;
    }

    public function updateExchangeRate($toCurrencyCode, $rate)
    {
        $exchangeRate = $this->exchangeRates()
            ->where('to_currency_code', $toCurrencyCode)
            ->latest()
            ->first();

        if (!$exchangeRate || $exchangeRate->rate != $rate) {
            $this->exchangeRates()->create([
                'to_currency_code' => $toCurrencyCode,
                'rate' => $rate,
            ]);
        }
    }

    public function updateExchangeRates(array $exchangeRates)
    {
        foreach ($exchangeRates as $exchangeRate) {
            $toCurrencyCode = $exchangeRate['code'];
            $rate = $exchangeRate['rate'];

            if (in_array($toCurrencyCode, self::$supportedCurrencies)) {
                $this->updateExchangeRate($toCurrencyCode, $rate);
            }
        }
    }
}

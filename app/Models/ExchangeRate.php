<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency_id',
        'to_currency_id',
        'rate',
    ];
    
    public static function createOrUpdate($currencyId, $toCurrencyId, $rate)
    {
        $exchangeRate = self::where('currency_id', $currencyId)
            ->where('to_currency_id', $toCurrencyId)
            ->first();

        if ($exchangeRate) {
            $exchangeRate->rate = $rate;
            $exchangeRate->save();
        } else {
            $exchangeRate = new self();
            $exchangeRate->currency_id = $currencyId;
            $exchangeRate->to_currency_id = $toCurrencyId;
            $exchangeRate->rate = $rate;
            $exchangeRate->save();
        }

        return $exchangeRate;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateExchangeRates extends Command
{
    protected $signature = 'exchange-rates:update';
    protected $description = 'Update exchange rates from National Bank of Georgia API';

    public function handle()
    {
        $currencies = Currency::all()->pluck('id')->toArray();

        $response = Http::get('https://nbg.gov.ge/gw/api/ct/monetarypolicy/currencies/ka/json');

        if (!$response->ok()) {
            $this->error('Failed to retrieve exchange rates');
            return;
        }

        $rates = $response->json();

        if (!isset($rates[0]['currencies'])) {
            $this->error('Unexpected response format');
            return;
        }

        // Check if we have GEL to EUR and GEL to USD rates
        $gelToEur = ExchangeRate::where('currency_id', Currency::where('code', 'GEL')->value('id'))
            ->where('to_currency_id', Currency::where('code', 'EUR')->value('id'))
            ->first();
        $gelToUsd = ExchangeRate::where('currency_id', Currency::where('code', 'GEL')->value('id'))
            ->where('to_currency_id', Currency::where('code', 'USD')->value('id'))
            ->first();

        foreach ($currencies as $fromCurrencyId) {
            $fromCurrencyCode = Currency::find($fromCurrencyId)->code;
            foreach ($rates[0]['currencies'] as $currency) {
                if ($currency['code'] === $fromCurrencyCode) {
                    continue;
                }

                $toCurrencyId = Currency::where('code', $currency['code'])->value('id');

                if (!$toCurrencyId) {
                    continue;
                }

                // Calculate the exchange rate based on GEL, if possible
                $exchangeRate = null;
                if ($fromCurrencyCode === 'USD' && $gelToUsd && $gelToEur) {
                    $gelToToCurrency = ExchangeRate::where('currency_id', Currency::where('code', 'GEL')->value('id'))
                        ->where('to_currency_id', $toCurrencyId)
                        ->first();

                    if ($gelToToCurrency) {
                        $usdToGel = 1 / $gelToUsd->rate;
                        $usdToEur = $usdToGel / $gelToEur->rate;
                        $toCurrencyToEur = $gelToToCurrency->rate / $gelToEur->rate;
                        $exchangeRate = ExchangeRate::createOrUpdate(
                            $fromCurrencyId,
                            $toCurrencyId,
                            $toCurrencyToEur / $usdToEur
                        );
                    }
                }

                // Calculate the exchange rate based on the direct rate or the inverse rate
                if (!$exchangeRate) {
                    $directExchangeRate = ExchangeRate::where('currency_id', $fromCurrencyId)
                        ->where('to_currency_id', $toCurrencyId)
                        ->first();
                    $inverseExchangeRate = ExchangeRate::where('currency_id', $toCurrencyId)
                        ->where('to_currency_id', $fromCurrencyId)
                        ->first();

                    if ($directExchangeRate) {
                        $exchangeRate = $directExchangeRate;
                    } elseif ($inverseExchangeRate) {
                        $exchangeRate = $inverseExchangeRate;
                    } elseif ($gelToToCurrency) {
                    $gelToFromCurrency = ExchangeRate::where('currency_code', 'GEL')
                        ->where('to_currency_id', $fromCurrencyId)
                        ->first();

                    if ($gelToFromCurrency) {
                        $usdToGel = 1 / $gelToUsd->rate;
                        $usdToEur = $usdToGel / $gelToEur->rate;
                        $fromCurrencyToEur = $gelToFromCurrency->rate / $gelToEur->rate;
                        $exchangeRate = ExchangeRate::createOrUpdate(
                            $fromCurrencyId,
                            $toCurrencyId,
                            $fromCurrencyToEur * $usdToEur / $gelToToCurrency->rate
                        );
                    }
                } else {
                    $exchangeRate = ExchangeRate::createOrUpdate(
                        $fromCurrencyId,
                        $toCurrencyId,
                        $currency['rate']
                    );
                }
            }

            if ($exchangeRate) {
                $this->info(sprintf('Exchange rate for %s => %s: %f', $fromCurrencyCode, $currency['code'], $exchangeRate->rate));
            }
        }
    }
}
}
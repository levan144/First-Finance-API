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

        $gelToEurRate = $this->getRate($rates, 'EUR');
        $gelToUsdRate = $this->getRate($rates, 'USD');

        $eurId = Currency::where('code', 'EUR')->value('id');
        $usdId = Currency::where('code', 'USD')->value('id');
        $gelId = Currency::where('code', 'GEL')->value('id');

        // Update or create GEL to EUR and GEL to USD
        $this->updateOrCreateRate($gelId, $eurId, $gelToEurRate);
        $this->updateOrCreateRate($gelId, $usdId, $gelToUsdRate);
    
        // Update or create EUR to GEL and USD to GEL
        $this->updateOrCreateRate($eurId, $gelId, 1 / $gelToEurRate);
        $this->updateOrCreateRate($usdId, $gelId, 1 / $gelToUsdRate);

        // Calculate and update or create EUR to USD and USD to EUR
        if ($gelToEurRate && $gelToUsdRate) {
            $eurToUsdRate = $gelToUsdRate / $gelToEurRate;
            $usdToEurRate = 1 / $eurToUsdRate;

            $this->updateOrCreateRate($eurId, $usdId, $eurToUsdRate);
            $this->updateOrCreateRate($usdId, $eurId, $usdToEurRate);
        }
    }

    private function getRate($rates, $currencyCode)
    {
        foreach ($rates[0]['currencies'] as $currency) {
            if ($currency['code'] === $currencyCode) {
                return $currency['rate'];
            }
        }

        return null;
    }

    private function updateOrCreateRate($fromCurrencyId, $toCurrencyId, $rate) {
        $exchangeRate = ExchangeRate::firstOrNew(
            ['currency_id' => $fromCurrencyId, 'to_currency_id' => $toCurrencyId],
            ['rate' => 1 / $rate]
        );
    
        if ($exchangeRate->exists) {
            $exchangeRate->rate = 1 / $rate;
        }
    
        $exchangeRate->save();
    
        $this->info(sprintf('Exchange rate for %s => %s: %f', $fromCurrencyId, $toCurrencyId, 1 / $rate));
    }

}

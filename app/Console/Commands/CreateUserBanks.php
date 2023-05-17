<?php

namespace App\Console\Commands;

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\User;
use App\Models\Currency;
use App\Models\UserBank;
use Illuminate\Console\Command;

class CreateUserBanks extends Command
{
    protected $signature = 'create:userbanks';
    protected $description = 'Create UserBanks and BankAccounts for users who are missing them';

    public function handle()
    {
        $users = User::all();

        $bank = Bank::where('code', 'pasha')->first(); // Replace 'YOUR_BANK_CODE' with your desired bank code

        foreach ($users as $user) {
            $userBank = UserBank::where('user_id', $user->id)->first();

            if (!$userBank) {
                // Create UserBank record
                $userBank = UserBank::create([
                    'user_id' => $user->id,
                    'bank_id' => $bank->id,
                ]);

                // Create BankAccount records for each currency
                $currencies = Currency::all(); // Add your desired currencies
                foreach ($currencies as $currency) {
                    // Generate random IBAN and BIC numbers
                    $iban = $this->generateRandomIban();
                    $bic = $this->generateRandomBic();

                    BankAccount::create([
                        'account_name' => $currency->code . ' Account',
                        'currency_id' => $currency->id,
                        'user_bank_id' => $userBank->id,
                        'iban' => $iban,
                        'bic' => $bic,
                        'balance' => 0
                    ]);
                }
            }
        }

        $this->info('UserBanks and BankAccounts created successfully for missing users.');
    }

    private function generateRandomIban()
    {
        // Generate a random IBAN number
        $ibanPrefix = '1FINANCE'; // Replace with your desired IBAN prefix
        $ibanDigits = mt_rand(1000000000000000, 9999999999999999);

        return $ibanPrefix . $ibanDigits;
    }

    private function generateRandomBic()
    {
        // Generate a random BIC number
        $bicPrefix = '1FINBIC'; // Replace with your desired BIC prefix
        $bicSuffix = mt_rand(100, 999);

        return $bicPrefix . $bicSuffix;
    }
}

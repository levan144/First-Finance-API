<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Fee;
use App\Notifications\VerificationRequestApproved;
use App\Models\UserBank;
use App\Models\BankAccount;
use App\Models\Bank;
use App\Models\Currency;


class UserObserver
{
    public function created(User $user)
    {
        $transactionTypes = ['deposit', 'transfer', 'exchange'];

        foreach ($transactionTypes as $type) {
            Fee::create([
                'user_id' => $user->id,
                'transaction_type' => $type,
                'amount' => 0, // default amount
            ]);
        }
        
        //create bank accounts
        // Retrieve the banks and currencies from your existing tables
        $bank = Bank::where('code', 'pasha')->first();
        $currencies = Currency::all();
        
        // Loop through the banks and currencies to create UserBank and BankAccount models for the user
            
                $userBank = UserBank::create([
                    'user_id' => $user->id,
                    'bank_id' => $bank->id,
                ]);
                foreach ($currencies as $currency) {
                // Generate random IBAN and BIC numbers
                    $iban = $this->generateRandomIban();
                    $bic = $this->generateRandomBic();

                // Create BankAccount models for the user based on the bank and currency details
                BankAccount::create([
                    'user_bank_id' => $userBank->id,
                    'currency_id' => $currency->id,
                    'account_name' => $currency->code . ' Account',
                    'iban' => $iban,
                    'bic' => $bic,
                    'balance' => 0
                    // Add other required bank account fields
                ]);
            }
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
    
    public function updated(User $user): void
    {
        $originalStatus = $user->getOriginal('verified_at');
        // Check if the 'verified_at' field was changed
        if ($user->wasChanged('verified_at') && !$originalStatus) {
            // Notify the user
            $user->notify(new VerificationRequestApproved());
        }
    }
}

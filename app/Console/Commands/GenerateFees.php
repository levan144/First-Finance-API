<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Fee;

class GenerateFees extends Command
{
    protected $signature = 'fees:generate';

    protected $description = 'Generate fees for each transaction type for each user';

    public function handle()
    {
        $transactionTypes = ['deposit', 'transfer', 'exchange'];

        User::each(function ($user) use ($transactionTypes) {
            foreach ($transactionTypes as $type) {
                Fee::firstOrCreate([
                    'user_id' => $user->id,
                    'transaction_type' => $type,
                    'amount' => 0, // default amount
                ]);
            }
        });
    }
}

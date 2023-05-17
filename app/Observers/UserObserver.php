<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Fee;

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
    }
}

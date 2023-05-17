<?php

namespace App\Observers;

use App\Models\Transaction;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction)
    {
        if ($transaction->isDirty('status') && $transaction->status === 'Approved') {
            // Perform the actions to charge balance here
            // For example, you can retrieve the associated bank account and deduct the amount from the balance
            $bankAccount = $transaction->bankAccount;
            $bankAccount->balance -= $transaction->amount;
            $bankAccount->save();
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}

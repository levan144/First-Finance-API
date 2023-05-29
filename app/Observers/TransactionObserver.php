<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Notifications\TransactionApproved;
use App\Notifications\TransactionRejected;
use App\Notifications\TransactionSubmitted;
use App\Notifications\TransactionUpdated;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        // Notify the user that a transaction has been submitted.
        $transaction->user->notify(new TransactionSubmitted($transaction));
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction)
    {
        if ($transaction->isDirty('status')){
            //Get Transaction FEE
            $fee = $transaction->fee;
            if ($transaction->status === 'Approved') {
                $user = $transaction->user;
                // Add the fee to the user's balance due if the transaction is approved
                $user->balance_due += $fee;
                if($transaction->type === 'transfer'){
                    // Perform the actions to charge balance here
                    
                    // For example, you can retrieve the associated bank account and deduct the amount from the balance
                    $bankAccount = $transaction->bankAccount;
                    $bankAccount->balance -= $transaction->amount;
                    $bankAccount->save();
                }
                $user->save();
                
            } elseif ($transaction->getOriginal('status') === 'Approved' && $transaction->status !== 'Approved') {
                // Deduct the fee from the user's balance due if the transaction was previously approved and now NOT.
                
                if($transaction->type === 'transfer'){
                    // Perform the actions to charge balance here
                    // For example, you can retrieve the associated bank account and deduct the amount from the balance
                    $bankAccount = $transaction->bankAccount;
                    $bankAccount->balance += $transaction->amount;
                    $bankAccount->save();
                }
                $user->balance_due -= $fee;
                $user->save();
            }
        
            $this->sendNotificationBasedOnStatus($transaction);
        }

    }
    
    private function sendNotificationBasedOnStatus($transaction) {
        $changes = $transaction->getChanges();

        // If the status was changed...
        if(isset($changes['status'])) {
            // Determine the type of the notification...
            switch ($changes['status']) {
                case 'Approved':
                    $notification = new TransactionApproved();
                    break;

                case 'Rejected':
                    $notification = new TransactionRejected();
                    break;

                default:
                    $notification = new TransactionUpdated();
                    break;
            }

            // Notify the user...
            $transaction->user->notify($notification);
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

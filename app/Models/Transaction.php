<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\TransactionResource;
use App\Models\BankAccount;

class Transaction extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'sender_id',
        'recipient_type',
        'recipient_name',
        'sender_iban',
        'recipient_iban',
        'currency_id',
        'reference',
        'amount',
        'fee',
        'type',
        'status',
    ];

    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }
    
    public function userAccount()
    {
        return $this->belongsTo(BankAccount::class, 'sender_iban', 'iban');
    }
    
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }
    
    public function processTransfer() {
        // Get the sender account
        $senderAccount = $this->bankAccount;
        $senderUser = $senderAccount->userBank->user;
    
        // Get the fee percentage for transfer transactions for this user
        $feePercentage = $senderUser->fees()->where('transaction_type', 'transfer')->first()->amount;
    
        // Calculate the fee for this transaction
        $fee = ($this->amount * $feePercentage) / 100;
    
        // Calculate the final amount to be transferred after deducting the fee
        $transferAmount = $this->amount - $fee;
    
        // Check if sender has enough balance to cover the total amount (including the fee)
        if ($senderAccount->balance < $this->amount) {
            // If sender doesn't have enough balance, return false
            return false;
        }
    
        // Deduct the total amount (including the fee) from the sender's balance
        $senderAccount->balance -= $this->amount;
        $senderAccount->save();
    
        // Perform the transfer to the recipient (using the recipient name and IBAN)
        // You can implement the logic here to handle the transfer to the recipient's external account
    
        // Update transaction status and fee
        $this->status = 'approved';
        $this->amount = $transferAmount;
        $this->fee = $fee;
        $this->save();
    
        return $this;
    }





    public function reject()
    {
        // Return sender balance
        $senderAccount = $this->bankAccount;
        $senderAccount->balance += ($this->amount + $this->fee);
        $senderAccount->save();

        // Update transaction status
        $this->status = 'Rejected';
        $this->save();
    }
    
    public function processExchange($senderAccountId, $recipientAccountId) {
    // Get the sender and recipient accounts
    $senderAccount = BankAccount::find($senderAccountId);
    $recipientAccount = BankAccount::find($recipientAccountId);

    // Get the user of the sender account
    $senderUser = $senderAccount->userBank->user;
    
     // Check if sender has enough balance in the source currency account (including the amount and fee)
    if ($senderAccount->balance < ($this->amount)) {
        // If sender doesn't have enough balance, exit the function
        return false;
    }

    
    // Get the appropriate fee for the transaction type 'exchange'
    $feePercentage = $senderUser->fees()->where('transaction_type', 'exchange')->first()->amount;

    // Calculate the fee
    $fee = $this->amount * ($feePercentage / 100);

    // Calculate the amount after deducting the fee
    $amountAfterFee = $this->amount - $fee;
   
    // Deduct the amount and fee from the sender account
    $senderAccount->balance -= $this->amount;
    $senderAccount->save();
    
     // Set the to_currency value
    $this->to_currency = $recipientAccount->currency->code;
    
    // Perform the currency exchange
    $exchangeRate = $this->getExchangeRate($senderAccount->currency, $recipientAccount->currency);
    $convertedAmount = $amountAfterFee * $exchangeRate;
    $this->received_amount = $convertedAmount;

    // Add the converted amount to the recipient account
    $recipientAccount->balance += $convertedAmount;
    $recipientAccount->save();

    // Update transaction status and fee
    $this->status = 'approved';
    $this->fee = $fee;
    $this->save();
    return $this;
}


    
    public function processDeposit() {
        $userAccount = $this->userAccount;
        $user = $userAccount->userBank->user;
    
        // Get the fee for deposit transactions for this user
        $feePercentage = $user->fees->where('transaction_type', 'deposit')->first()->amount;
    
        // Calculate the fee for this transaction
        $this->fee = ($this->amount * $feePercentage) / 100;
    
        // Calculate the final amount to be deposited after deducting the fee
        $finalAmount = $this->amount - $this->fee;
    
        // Increase user balance
        $userAccount->balance += $finalAmount; // user gets the final amount after fee deduction
        $userAccount->save();
    
        // Update transaction status
        $this->status = 'approved';
        $this->save();
    }

       /**
     * Withdraw the given amount from the account.
     *
     * @param  float  $amount
     * @param  string  $currency
     * @return bool
     */
     
     public function withdraw($amount, $currency = 'GEL') {
        $convertedAmount = $this->convertCurrency($amount, $currency, $this->currency->code);

        if (!$convertedAmount || $this->balance < $convertedAmount) {
            return false;
        }

        $this->balance -= $convertedAmount;
        $this->save();

        return true;
    }

}

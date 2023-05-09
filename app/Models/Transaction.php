<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    
    public function processTransfer(){
        // Check if sender has enough balance to complete the transfer
        $senderAccount = $this->bankAccount;
        if ($senderAccount->balance < ($this->amount + $this->fee)) {
            // If sender doesn't have enough balance, reject the transaction
            $this->status = 'rejected';
            $this->save();
            return;
        }
        
        // Decrease sender balance
        $senderAccount->balance -= ($this->amount + $this->fee);
        $senderAccount->save();
        
        // Update transaction status
        $this->status = 'approved';
        $this->save();
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
    
    public function processExchange()
    {
        // Implement the logic to exchange money between different currency accounts.
        // You will need to use a currency conversion API or package to get the exchange rate.
    }
    
    public function processDeposit()
    {
        $userAccount = $this->userAccount;
        $userAccount->balance += $this->amount;
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

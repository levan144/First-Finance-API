<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\TransactionRequest;
use App\Models\Transaction;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Fee;
class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = $request->user()
            ->bankAccounts()
            ->with('transactions')
            ->get()
            ->pluck('transactions')
            ->collapse();

        return response()->json([
            'status' => true,
            'transactions' => $transactions,
        ]);
    }
    
    public function getBankAccountTransactions(BankAccount $bankAccount)
    {
        try {
            $transactions = $bankAccount->transactions;

            return response()->json([
                'status' => true,
                'transactions' => $transactions
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    public function transfer(Request $request) {
    try {
        $user = auth('sanctum')->user();
        $senderAccountId = $request->input('sender_account_id');
        $recipientIban = $request->input('recipient_iban');
        $recipientType = $request->input('recipient_type');
        $recipientName = $request->input('recipient_name');
        $reference = $request->input('reference');
        
        $amount = $request->input('amount');
        // Calculate the fee
        $fee = $this->calculateFeeForTransaction('transfer', $amount, $user);
        // Calculate the amount after deducting the fee
        $amountAfterFee = $amount - $fee;
        // Get the sender's bank account
        $senderAccount = $user->bankAccounts->find($senderAccountId);
        // Check if the sender account belongs to the authenticated user
        if (!$senderAccount) {
            return response()->json([
                'status' => false,
                'message' => __('Invalid sender account.'),
            ], 400);
        }
        
        // Check if the sender has enough balance
        if ($senderAccount->balance < $amount) {
            return response()->json([
                'status' => false,
                'message' => __('Insufficient balance in the sender account.'),
            ], 400);
        }
        
        // Create the transaction
        $transaction = new Transaction();
        $transaction->sender_id = $user->id;
        $transaction->bank_account_id = $senderAccountId;
        $transaction->recipient_type = $recipientType;
        $transaction->recipient_name = $recipientName; // Add recipient name if available
        $transaction->sender_iban = $senderAccount->iban;
        $transaction->recipient_iban = $recipientIban;
        $transaction->currency_id = $senderAccount->currency_id;
        $transaction->amount = $amount;
        $transaction->fee = $fee; // Calculate fee if applicable
        $transaction->type = 'transfer';
        $transaction->reference = $reference;
        $transaction->status = 'pending'; // Set initial status as pending
        $transaction->save();
        
        // Deduct the amount from the sender's balance
        // $senderAccount->balance -= $amount;
        // $senderAccount->save();
        
        return response()->json([
            'status' => true,
            'message' => __('Transfer transaction created successfully.'),
            'transaction' => $transaction,
        ]);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage(),
        ], 500);
    }
}

    public function calculateFee(Request $request) {
        try {
            $user = auth('sanctum')->user();
            $request->validate([
                'amount' => 'required|numeric',
                'type' => 'required|in:exchange,transfer',
            ]);
    
            $amount = $request->input('amount');
            $type = $request->input('type');
    
            // Perform fee calculation based on transaction type and amount
            $fee = $this->calculateFeeForTransaction($type, $amount, $user);
    
            return response()->json([
                'status' => true,
                'fee' => $fee,
            ]);
        } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage(),
        ], 500);
    }
    }
    
    private function calculateFeeForTransaction($type, $amount, $user) {
        // Retrieve the fee from the fee table based on the transaction type
        $feePercentage = $user->fees()->where('transaction_type', $type)->first()->amount;
    
        // Calculate the fee based on the fee percentage and the transaction amount
        $fee = $amount * ($feePercentage / 100);
        $fee = number_format($fee, 2);
        return $fee;
    }


    public function store(TransactionRequest $request)
    {
        $validatedData = $request->validated();
        $user = $request->user();
        $userAccount = BankAccount::findOrFail($validatedData['user_account_id']);

        if ($userAccount->user_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => __('Invalid user account.'),
            ], 400);
        }

        // Create the transaction with the initial "pending" status
        $validatedData['status'] = 'pending';
        $transaction = Transaction::create($validatedData);

        // Process the transaction
        switch ($transaction->type) {
            case 'transfer':
                $transaction->processTransfer();
                break;
            case 'exchange':
                $transaction->processExchange();
                break;
            case 'deposit':
                $transaction->processDeposit();
                break;
        }

        return response()->json([
            'status' => true,
            'message' => __('Transaction created successfully.'),
            'transaction' => $transaction,
        ]);
    }
}

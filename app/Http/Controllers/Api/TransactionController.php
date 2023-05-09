<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\TransactionRequest;
use App\Models\Transaction;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

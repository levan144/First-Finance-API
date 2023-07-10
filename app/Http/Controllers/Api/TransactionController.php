<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\TransactionRequest;
use App\Http\Requests\Api\Transactions\TransferRequest;
use App\Http\Requests\Api\Transactions\ExchangeRequest;
use App\Models\Transaction;
use App\Models\BankAccount;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\ExchangeOffer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Fee;
use Illuminate\Support\Facades\Validator;
use App\Models\Beneficiary;
use PDF;

class TransactionController extends Controller
{
    const BANK_FEE_PERCENTAGE = 0.01; // 1%
    
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
    
    public function show($id){
        $transaction = Transaction::findOrFail($id);
        $user = auth('sanctum')->user();
        // Check if the authenticated user owns the transaction
        if ($transaction->sender_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => __('Unauthorized'),
            ], 403);
        }
    
        return response()->json([
            'status' => true,
            'transaction' => $transaction,
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
    
    public function getBankTransactions($id)
    {
        try {
            $bank = Bank::findOrFail($id);
            $user = auth('sanctum')->user();
            
            $transactions = Transaction::whereHas('bankAccount.userBank', function ($query) use ($user, $bank) {
                $query->where('user_id', $user->id)
                      ->where('bank_id', $bank->id);
            })->get();

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
    
    public function transfer(TransferRequest $request) {
        try {
            $user = auth('sanctum')->user();
            $senderAccountId = $request->input('sender_account_id');
            $recipientIban = $request->input('recipient_iban');
            $recipientType = $request->input('recipient_type');
            $recipientName = $request->input('recipient_name');
            $comment = $request->input('comment');
            $reference = $request->input('reference');
            $charges = $request->input('charges');
            $amount = $request->input('amount');
    
            // New Fields
            $beneficiaryCountryCode = $request->input('beneficiary_country_code');
            $beneficiaryAddress = $request->input('beneficiary_address');
            $bankName = $request->input('bank_name');
            $bankCode = $request->input('bank_code');
            $intermediaryBankName = $request->input('intermediary_bank_name');
            $intermediaryBankCode = $request->input('intermediary_bank_code');
    
            // Calculate the fee
            $fee = $this->calculateFeeForTransaction('transfer', $amount, $user);
            
            // Calculate the bank fee
            $bankFee = $amount * self::BANK_FEE_PERCENTAGE;
            
            // Calculate the amount after deducting the fee
            $amountAfterFee = $amount - $bankFee;
            
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
            $transaction->bank_fee = number_format($bankFee,2);
            $transaction->type = 'transfer';
            $transaction->comment = $comment;
            $transaction->reference = $reference;
            $transaction->charges = $charges;
            $transaction->status = 'Pending'; // Set initial status as pending
            
            // Set new beneficiary fields
            $transaction->beneficiary_country_code = $beneficiaryCountryCode;
            $transaction->beneficiary_address = $beneficiaryAddress;
            $transaction->bank_name = $bankName;
            $transaction->bank_code = $bankCode;
            $transaction->intermediary_bank_name = $intermediaryBankName;
            $transaction->intermediary_bank_code = $intermediaryBankCode;
            
            $transaction->save();
            
            // Update user balance_due with fee
            $user->addFee($transaction->fee);
            
            // Deduct the amount from the sender's balance
            $senderAccount->balance -= $amount;
            $senderAccount->save();
            
            $saveBeneficiary = $request->input('save_beneficiary');
            if ($saveBeneficiary) {
                $beneficiary = new Beneficiary([
                    'name' => $recipientName,
                    'country' => $beneficiaryCountryCode,
                    'address' => $beneficiaryAddress,
                    'type' => $recipientType,
                    'account_number' => $recipientIban,
                    'bank_name' => $bankName,
                    'bank_code' => $bankCode,
                    'intermediary_bank_name' => $intermediaryBankName,
                    'intermediary_bank_code' => $intermediaryBankCode
                ]);
                $user->beneficiaries()->save($beneficiary);
            }
            
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
    
    public function exchange_offers($id) {
        $offers = Transaction::findOrFail($id)->offers;
        return response()->json([
                'status' => true,
                'offers' => $offers,
            ]);
    }
    
    public function exchange_offer_show($id) {
        $offer = ExchangeOffer::findOrFail($id);
        $transaction = $offer->transaction;
        // Calculate the fee
        $fee = $this->calculateFeeForTransaction('exchange', $transaction->amount, $transaction->user);
        $gel = Currency::where('code', 'GEL')->first();
        $fee = number_format($fee * $this->getExchangeRate($transaction->currency, $gel), 2);
        
        $exchangeRate = $offer->rate;
        
        // Calculate the amount after deducting the fee
        $amountAfterFee = $transaction->amount - $fee;
        $data = [];
        $data['currency'] = $transaction->currency;
        $data['to_currency'] = $transaction->to_currency;
        $data['amount'] = $transaction->amount;
        $data['converted_amount'] = number_format($amountAfterFee * $exchangeRate, 2);
        $data ['fee'] = $fee;
        $data['rate'] = $offer->rate;
        
        return response()->json([
                'status' => true,
                'data' => $data,
            ]);
    }
    
    
    
    public function exchange_offer_update(Request $request, $id){
        $validatedData = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);
        
       
        $exchangeOffer = ExchangeOffer::findOrFail($id);
        
        if($exchangeOffer->status !== 'pending') {
             return response()->json([
                'status' => true,
                'message' => __('Offer Not Valid'),
            ]);
        }
        
        $exchangeOffer->status = $validatedData['status'];
        $exchangeOffer->save();
        $transaction = $exchangeOffer->transaction;

        // Calculate the fee
        $fee = $this->calculateFeeForTransaction('exchange', $transaction->amount, $transaction->user);
        $gel = Currency::where('code', 'GEL')->first();
        $fee = number_format($fee * $this->getExchangeRate($transaction->currency, $gel), 2);
        
        
        // Calculate the amount after deducting the fee
        $exchangeRate = $exchangeOffer->rate;
        // Calculate the amount after deducting the fee
        $amountAfterFee = $transaction->amount - $fee;
            
        // Update the corresponding transaction based on the offer status
        if ($exchangeOffer->status === 'approved') {
            $transaction = $exchangeOffer->transaction;
            $transaction->converted_amount = number_format($amountAfterFee * $exchangeRate, 2);
            $transaction->status = 'Approved';
            $transaction->save();
            
            $transaction->approveExchange();
            
            return response()->json([
                    'status' => true,
                    'message' => __('Offer & Transaction Completed'),
                ]);
        }
        
        return response()->json([
                    'status' => true,
                    'message' => __('The offer is rejected'),
                ]);
    }

    public function benecifiary_show(Request $request)
        {
            $user = auth('sanctum')->user();
        
            // Check if the beneficiary exists
            $beneficiary = Beneficiary::find($request->id);
            if (!$beneficiary) {
                return response()->json([
                    'status' => false,
                    'message' => __('Beneficiary not found.'),
                ], 404);
            }
        
            // Ensure the authenticated user owns the beneficiary
            if ($beneficiary->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => __('Unauthorized'),
                ], 403);
            }
        
            return response()->json([
                'status' => true,
                'beneficiary' => $beneficiary,
            ]);
        }
        
    public function benecifiary_all(Request $request)
        {
            $user = auth()->user();
            $beneficiaries = $user->beneficiaries;
        
            return response()->json([
                'status' => true,
                'beneficiaries' => $beneficiaries,
            ]);
        }

    public function beneficiary_destroy(Request $request){
        $user = auth('sanctum')->user();
        // Check if the beneficiary exists
        $beneficiary = Beneficiary::find($request->id);
        if (!$beneficiary) {
            return response()->json([
                'status' => false,
                'message' => __('Beneficiary not found.'),
            ], 404);
        }
        
        // Ensure the authenticated user owns the beneficiary
        if ($beneficiary->user_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => __('Unauthorized'),
            ], 403);
        }
    
        $beneficiary->delete();
    
        return response()->json([
            'status' => true,
            'message' => __('Beneficiary deleted successfully.'),
        ]);
    }
    

    


    
    public function exchange(ExchangeRequest $request) {
        $user = auth('sanctum')->user();
        
        $senderAccountId = $request->input('sender_account_id');
        $recipientAccountId = $request->input('recipient_account_id');
        $amount = $request->input('amount');
        $comment = $request->input('comment');
        // $reference = $request->input('reference');
        
        // Get the sender's bank account
        $senderAccount = $user->bankAccounts->find($senderAccountId);
        // Get the recipient's bank account
        $recipientAccount = $user->bankAccounts->find($recipientAccountId);
        
        if (!$senderAccount || !$recipientAccount) {
            return response()->json([
                'status' => false,
                'message' => __('Invalid sender or recipient account'),
            ], 400);
        }
        
        $amount = number_format(floatval($amount), 2);
        
        // Check if the sender has enough balance
        if ($senderAccount->balance < $amount) {
            return response()->json([
                    'status' => false,
                    'message' => __('Insufficient balance in the sender account.'),
                ], 400);
        }
        
        // Calculate the fee
        $fee = $this->calculateFeeForTransaction('exchange', $amount, $user);
       
        // Calculate the bank fee
        $bankFee = $amount * self::BANK_FEE_PERCENTAGE;
            
        // Calculate the amount after deducting the fee
        $amountAfterFee = $amount - $bankFee;
       
        
        // Perform the currency exchange
        $sourceCurrency = $senderAccount->currency;
        $targetCurrency = $recipientAccount->currency;
        $exchangeRate = $this->getExchangeRate($sourceCurrency, $targetCurrency);
        
        //get GEL currency
        $gel = Currency::where('code', 'GEL')->first();
        $fee = number_format($fee * $this->getExchangeRate($sourceCurrency, $gel), 2);

        // Calculate the converted amount after deducting the fee
        $convertedAmount = $amountAfterFee * $exchangeRate;
        
        // Create the transaction
            $transaction = new Transaction();
            $transaction->sender_id = $user->id;
            $transaction->bank_account_id = $senderAccountId;
            $transaction->recipient_bank_account_id = $recipientAccountId;
            // $transaction->recipient_type = $recipientType;
            // $transaction->recipient_name = $recipientName; // Add recipient name if available
            $transaction->sender_iban = $senderAccount->iban;
            $transaction->recipient_iban = $recipientAccount->iban;
            $transaction->currency_id = $senderAccount->currency_id;
            $transaction->to_currency_id = $recipientAccount->currency_id;
            $transaction->amount = $amount;
            $transaction->converted_amount = number_format($convertedAmount, 2);
            $transaction->fee = $fee; // Calculate fee if applicable
            $transaction->bank_fee = number_format($bankFee,2);
            $transaction->type = 'exchange';
            $transaction->comment = $comment;
            // $transaction->reference = $reference;
            $transaction->status = 'Pending'; // Set initial status as pending
            $transaction->save();
        
            // Deduct the amount and fee from the sender account
            // $senderAccount->balance -= $amount;
            // $senderAccount->save();
            
            // Add the converted amount to the recipient account
            // $recipientAccount->balance += $convertedAmount;
            // $recipientAccount->save();
        
            return response()->json([
                'status' => true,
                'message' => __('Exchange transaction created successfully.'),
                'transaction' => $transaction,
            ]);
        
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
            $bankFee = $amount * self::BANK_FEE_PERCENTAGE;
            return response()->json([
                'status' => true,
                'fee' => $fee,
                'bank_fee' => $bankFee,
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
        if(!$feePercentage) {
            $feePercentage = Fee::where('transaction_type', $type)->whereNull('user_id')->first()->amount;
        }
        // Calculate the fee based on the fee percentage and the transaction amount
        $fee = $amount * ($feePercentage / 100);
        $fee = number_format($fee, 2);
        return $fee;
    }
    
    private function getExchangeRate($sourceCurrency, $targetCurrency) {
        // Retrieve the exchange rate from the exchange_rates table
        $exchangeRate = ExchangeRate::where('currency_id', $sourceCurrency->id)
            ->where('to_currency_id', $targetCurrency->id)
            ->value('rate');
    
        return $exchangeRate;
    }
    
    public function calculateExchange(Request $request) {
        $user = auth('sanctum')->user();
        $validator = Validator::make($request->all(), [
            'sender_account_id' => 'required|exists:bank_accounts,id',
            'recipient_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 400);
        }
    
        $fromAccountId = $request->input('sender_account_id');
        $toAccountId = $request->input('recipient_account_id');
        $amount = $request->input('amount');
    
        // Get the from and to bank accounts
        $fromAccount = BankAccount::findOrFail($fromAccountId);
        $toAccount = BankAccount::findOrFail($toAccountId);
        
        // Calculate the fee
        $fee = $this->calculateFeeForTransaction('exchange', $amount, $user);
        // Calculate the amount after deducting the fee
        // $amountAfterFee = $amount - $fee; //DEPRECATED, NOW GOES TO DUE BALANCE
        $amountAfterFee = $amount;
        // Perform the currency exchange calculation
        $exchangeRate = $this->getExchangeRate($fromAccount->currency, $toAccount->currency);
        $convertedAmount = number_format($amountAfterFee * $exchangeRate, 2);
    
        return response()->json([
            'status' => true,
            // 'from_account' => $fromAccount,
            // 'to_account' => $toAccount,
            'amount' => $amount,
            'converted_amount' => $convertedAmount,
            'fee' => $fee
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
    
    public function showInvoice(Transaction $transaction){
        // You can pass data to the view as needed
        $data = ['transaction' => $transaction];
    
        $pdf = PDF::loadView('invoices.invoice', $data);
        
        // Return the PDF response
        return $pdf->stream('Invoice_'.$transaction->id.'.pdf');
    }
    
    public function downloadInvoice(Transaction $transaction){
        // You can pass data to the view as needed
        $data = ['transaction' => $transaction];
    
        $pdf = PDF::loadView('invoices.invoice', $data);
        
        $publicDirectory = public_path('pdfs');

        if (!is_dir($publicDirectory)) {
            mkdir($publicDirectory, 0755, true);
        }
        
        $pdfFileName = 'Invoice_' . uniqid() . '.pdf';
        $pdfFilePath = $publicDirectory . '/' . $pdfFileName;
        
        $pdf->save($pdfFilePath);
        
        $publicUrl = url('pdfs/' . $pdfFileName);
    
        // Return the path if you want to do something with it
        return response()->json([
            'status' => true,
            'invoice_url' => $publicUrl,
        ]);
        
    }
}

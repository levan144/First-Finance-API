<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Models\Bank;
class BankAccountController extends Controller
{
    /**
     * Display a listing of the bank accounts by Bank ID
     *
     * @return \Illuminate\Http\Response
     */
    public function getBankAccountsByBankId($bankId)
    {
        try {
            // Retrieve the bank by ID
            $bank = Bank::findOrFail($bankId);
    
            // Retrieve the bank accounts associated with the bank for the authenticated user
            $user = auth('sanctum')->user();
            $bankAccounts = $user->userBanks()
                ->where('bank_id', $bankId)
                ->with('bankAccounts')
                ->get()
                ->pluck('bankAccounts')
                ->collapse();
            // Return the bank accounts as a JSON response
            return response()->json([
                'status' => true,
                'bank_accounts' => $bankAccounts,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified bank account.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
            $user = auth('sanctum')->user();
            $bankAccount = $user->bankAccounts()
                ->with('currency')
                ->findOrFail($id);
            
            return response()->json([
                'status' => true,
                'bank_account' => $bankAccount,
            ]);
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created bank account in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $bankAccount = BankAccount::create($request->all());

        return response()->json($bankAccount, 201);
    }

    /**
     * Update the specified bank account in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        $bankAccount->update($request->all());

        return response()->json($bankAccount, 200);
    }

    /**
     * Remove the specified bank account from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        $bankAccount->delete();

        return response()->json(null, 204);
    }
}

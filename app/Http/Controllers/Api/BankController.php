<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Currency;

use Illuminate\Http\Request;
use App\Http\Resources\BankAccountResource;
use App\Http\Resources\BankResource;
use App\Models\ExchangeRate;
class BankController extends Controller
{
    /**
     * Display a listing of the banks.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        try {
           $user = auth('sanctum')->user();
           
            $show_in_currency = Currency::find($request->currency_id);
            
            $userBanks = $user->userBanks()->with('bankAccounts')->get();
            $unattachedBanks = Bank::whereDoesntHave('userBanks', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with(['userBanks.bankAccounts' => function ($query) {
                $query->whereNull('user_bank_id');
            }])->get();
            
            $banks = BankResource::collection($userBanks);
            
            $unattachedBanks->each(function ($bank) use ($banks) {
                $banks->push(new BankResource($bank));
            });
            if($show_in_currency){
                $banks->each(function ($bank) use ($show_in_currency){
                    $sum_in_currency = 0;
                    $bank->bankAccounts->each(function ($bankAccount)  use ($show_in_currency, &$sum_in_currency){
                        $sourceCurrency = Currency::findOrFail($bankAccount->currency_id);
                        $targetCurrency = Currency::where('code', $show_in_currency->code)->first();
                        $balance = $bankAccount->balance * $this->getExchangeRate($sourceCurrency, $targetCurrency);

                        $sum_in_currency += $balance;
                    });
                    $bank->total_balance = $sum_in_currency;
                });
            }
            return response()->json([
                'status' => true,
                'banks' => $banks
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified bank.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
        $bank = Bank::findOrFail($id);

        // Get the user bank accounts associated with the bank
        $bankAccounts = $bank->userBanks->where('user_id', auth()->user()->id)->flatMap(function ($userBank) {
            return $userBank->bankAccounts;
        });

        return response()->json([
            'status' => true,
            'bank' => [
                'id' => $bank->id,
                'name' => $bank->name,
                'code' => $bank->code,
                'logo' => $bank->logoUrl(),
                'is_active' => $bank->is_active,
                'bank_accounts' => $bankAccounts,
            ],
        ]);
    } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        

    }
    
    private function getExchangeRate($sourceCurrency, $targetCurrency) {
        if ($sourceCurrency->id === $targetCurrency->id) {
        return 1;
    }

        // Retrieve the exchange rate from the exchange_rates table
        $exchangeRate = ExchangeRate::where('currency_id', $sourceCurrency->id)
            ->where('to_currency_id', $targetCurrency->id)
            ->value('rate');
    
        return $exchangeRate;
    }

    /**
     * Store a newly created bank in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request)
    // {
    //     $bank = Bank::create($request->all());

    //     return response()->json($bank, 201);
    // }

    /**
     * Update the specified bank in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, $id)
    // {
    //     $bank = Bank::findOrFail($id);
    //     $bank->update($request->all());

    //     return response()->json($bank, 200);
    // }

    /**
     * Remove the specified bank from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function destroy($id)
    // {
    //     $bank = Bank::findOrFail($id);
    //     $bank->delete();

    //     return response()->json(null, 204);
    // }
}

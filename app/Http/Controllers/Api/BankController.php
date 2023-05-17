<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use App\Http\Resources\BankAccountResource;
use App\Http\Resources\BankResource;
class BankController extends Controller
{
    /**
     * Display a listing of the banks.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        try {
           $user = auth('sanctum')->user();

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

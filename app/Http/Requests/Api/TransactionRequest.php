<?php
namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Auth;

class TransactionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $user = Auth::user();
        $user_account_id = $this->input('user_account_id');
        $userAccount = BankAccount::where('id', $user_account_id)->where('user_id', $user->id)->first();

        return [
            'user_account_id' => [
                'required',
                Rule::exists('bank_accounts', 'id')->where(function ($query) use ($user) {
                    return $query->where('user_id', $user->id);
                }),
            ],
            'type' => 'required|in:transfer,exchange,deposit',
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) use ($userAccount) {
                    if ($userAccount && $value > $userAccount->balance) {
                        $fail('Insufficient balance in the account.');
                    }
                },
            ],
            'fee' => 'required|numeric|min:0',
            'recipient_iban' => 'required_if:type,transfer,exchange',
            'recipient_details' => 'required',
            'recipient_currency' => 'required_if:type,exchange',
            'note' => 'nullable|string',
        ];
    }
}

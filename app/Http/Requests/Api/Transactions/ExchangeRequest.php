<?php

namespace App\Http\Requests\Api\Transactions;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DifferentAccountIds;

class ExchangeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'sender_account_id' => [
            'required',
            'exists:bank_accounts,id',
            new DifferentAccountIds($this->input('recipient_account_id'))
            ],
            'recipient_account_id' => [
                'required',
                'exists:bank_accounts,id',
                new DifferentAccountIds($this->input('sender_account_id'))
            ],
            'amount' => 'required|numeric|min:0',
        ];
    }
    
    public function messages() {
        return [
            'sender_account_id.required' => __('The sender account ID is required.'),
            'sender_account_id.exists' => __('Invalid sender account.'),
            'recipient_account_id.required' => __('The recipient account ID is required.'),
            'recipient_account_id.exists' => __('Invalid recipient account.'),
            'amount.required' => __('The amount is required.'),
            'amount.numeric' => __('The amount must be a numeric value.'),
            'amount.min' => __('The amount must be at least :min.'),
        ];
    }

}

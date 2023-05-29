<?php

namespace App\Http\Requests\Api\Transactions;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
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
            'sender_account_id' => 'required|exists:bank_accounts,id',
            'recipient_type' => 'required|in:individual,company',
            'recipient_name' => 'required|string',
            'recipient_iban' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'save_beneficiary' => 'required|boolean',
            'charges' => 'required|in:SHA,OUR'
        ];
    }
    
    public function messages() {
        return [
            'sender_account_id.required' => __('The sender account ID is required.'),
            'sender_account_id.exists' => __('Invalid sender account.'),
            'recipient_iban.required' => __('The recipient IBAN is required.'),
            'amount.required' => __('The amount is required.'),
            'amount.numeric' => __('The amount must be a numeric value.'),
            'amount.min' => __('The amount must be at least :min.'),
        ];
    }

}

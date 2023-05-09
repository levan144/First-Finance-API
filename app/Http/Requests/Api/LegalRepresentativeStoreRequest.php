<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Arr;
class LegalRepresentativeStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:legal_representatives,email',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'representative_type' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:legal_representatives,id',
            'is_company' => 'nullable|boolean',
            'share' => 'nullable|numeric|min:0|max:100',
        ];
    }
    
  
}
<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Arr;
class LegalRepresentativeUpdateRequest extends FormRequest
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
            'name' => 'required|max:255',
            // 'email' => 'required|email|unique:legal_representatives,email,'.$this->id,
            'address' => 'nullable|max:255',
            'phone' => 'nullable|max:20',
            'representative_type' => 'nullable|max:100',
            'representative_id' => 'required|exists:legal_representatives,id',
            // 'parent_id' => 'nullable|exists:legal_representatives,id',
            'is_company' => 'nullable|boolean',
            'share' => 'numeric|min:0|max:100',
        ];
    }
    
  
}
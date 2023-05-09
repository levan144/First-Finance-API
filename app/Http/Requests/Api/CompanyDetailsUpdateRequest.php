<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Arr;
class CompanyDetailsUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'legal_form' => ['required', 'string', 'max:100'],
            'registration_date' => ['required', 'date'],
            'registration_number' => ['required', 'string', 'max:255'],
            // 'address' => ['required', 'string', 'max:255'],
        ];
    }
    
  
}
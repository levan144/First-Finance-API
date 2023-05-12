<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DocumentShowRequest extends FormRequest
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
        $rules = [
            // 'type' => 'required|in:user,legal_representative',
        ];

        // if ($this->type === 'user') {
        //     $rules['id'] = 'required|integer|exists:users,id';
        // } elseif ($this->type === 'legal_representative') {
        //     $rules['id'] = 'required|integer|exists:legal_representatives,id';
        // }

        return $rules;
    }
}

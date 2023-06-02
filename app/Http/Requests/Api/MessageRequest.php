<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Arr;
class MessageRequest extends FormRequest
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
            'message' => 'required|string',
            'ticket_id' => [
                'required',
                'exists:tickets,id',
                Rule::exists('tickets', 'id')->where(function ($query) {
                    $query->where('user_id', Auth::id());
                })
            ],
            'attachments.*' => 'nullable|file|max:12048', // Adjust the file validation rules as needed
            'locale' => ['required', Rule::in(array_keys(config('nova-translatable.locales'))),],
        ];
    }
    
    public function validationData()
    {
        $data = $this->all();
        $data['user_id'] = Auth::id();

        return $data;
    }
}

<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Arr;
class TicketRequest extends FormRequest
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
            'attachments.*' => 'nullable|file|max:2048', // Adjust the file validation rules as needed
        ];
    }
    
    public function validationData()
    {
        $data = $this->all();
        $data['user_id'] = Auth::id();

        return $data;
    }
}

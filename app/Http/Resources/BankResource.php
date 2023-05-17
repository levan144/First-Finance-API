<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\BankAccountResource;
use Storage;
class BankResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->bank->id ?? $this->id,
            'name' => $this->name ?? $this->bank->name,
            'code' => $this->code ?? $this->bank->code,
            'logo' => Storage::disk('public')->url($this->logo ?? $this->bank->logo) ,
            'is_active' => $this->is_active ?? $this->bank->is_active,
            'bank_accounts' => BankAccountResource::collection($this->whenLoaded('bankAccounts')),
        ];
    }
}
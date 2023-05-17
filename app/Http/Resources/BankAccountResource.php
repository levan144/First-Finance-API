<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'account_name' => $this->account_name,
            'bic' => $this->bic,
            'iban' => $this->iban,
            'balance' => $this->balance,
            'currency' => $this->currency
        ];
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'name',
        'country',
        'address',
        'type',
        'account_number',
        'bank_name',
        'bank_code',
        'intermediary_bank_name',
        'intermediary_bank_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

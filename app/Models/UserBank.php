<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBank extends Model
{
    use HasFactory;
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function bank() {
        return $this->belongsTo(Bank::class);
    }
    
    public function bankAccounts() {
        return $this->hasMany(BankAccount::class);
    }
}

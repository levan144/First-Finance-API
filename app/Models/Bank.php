<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'code', 'logo'];

    
    public function userBanks() {
        return $this->hasMany(UserBank::class);
    }
    
    public function logoUrl()
    {
        if ($this->logo) {
            return Storage::disk('public')->url($this->logo);
        }

        return null;
    }
    
    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }
    
    public function deleteLogo()
    {
        if ($this->logo) {
            Storage::disk('public')->delete($this->logo);
            $this->logo = null;
            $this->save();
        }
    }

    public function setLogo($file)
    {
        $this->deleteLogo();

        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = 'bank_logos/' . $fileName;

        Storage::disk('public')->putFileAs('bank_logos', $file, $fileName);

        $this->logo = $filePath;
        $this->save();
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Scopes\UserScope;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Fee;
class User extends Authenticatable implements MustVerifyEmail
{
     use HasRoles, HasApiTokens, HasFactory, Notifiable, LogsActivity;
    // use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'legal_form',
    ];
    
    protected static $logAttributes = ['name', 'email', 'phone', 'password','verified_at'];
    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'verified_at' => 'datetime',
        'registration_date' => 'date',
        'address' => 'array',
        'registration_address' => 'array',
    ];
    
     public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
    
    public function isSuperAdmin()
    {
        return $this->hasRole('super-admin');
    }
    
    public function legalForm(){
        return $this->belongsTo(LegalForm::class);
    }

    public function representatives(){
        return $this->hasMany(LegalRepresentative::class);
    }
    
    public function allRepresentativesVerified() {
        $representatives = $this->representatives;
        foreach ($representatives as $representative) {
            if (!$representative->verified_at) {
                return false;
            }
        }
        return true;
    }
    
    public function userBanks() {
        return $this->hasMany(UserBank::class);
    }
    
    public function documents() {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function bankAccounts()
    {
        return $this->hasManyThrough(
            BankAccount::class, // The related model
            UserBank::class, // The intermediate model
            'user_id', // Foreign key on the intermediate table for the User model
            'user_bank_id', // Foreign key on the related table for the BankAccount model
            'id', // Local key on the User model
            'id' // Local key on the BankAccount model
        );
    }
    
    public function transactions() {
        return $this->hasMany(Transaction::class, 'sender_id');
    }
    
    public function fees() {
        return $this->hasMany(Fee::class);
    }
    
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new UserScope);
    }
    
    
}

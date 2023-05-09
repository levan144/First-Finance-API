<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalRepresentative extends Model
{
    use HasFactory;
    protected $appends = [
        'percent_complete'
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'representative_type_id',
        'representative_type',
        'share',
        'parent_id',
        'user_id',
        'main_account',
        'is_company'
    ];
    
    public function getPercentCompleteAttribute(): float
    {
        $attributes = [
            'name',
            'email',
            'phone',
            'representative_type',
            'share'
        ];
        $complete = collect($attributes)->filter(fn ($field) => !empty($this->attributes[$field]))->count();
        return ($complete / count($attributes)) * 100;
    }
    
    public function representativeType(){
        return $this->belongsTo(RepresentativeType::class);
    }
    
    public function parent(){
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }
    
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }
    
    public function parentCompany(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    // this is a recommended way to declare event handlers
    public static function boot() {
        parent::boot();

        static::deleting(function($representative) { // before delete() method call this
             $representative->children()->delete();
             // do the rest of the cleanup...
        });
    }
}

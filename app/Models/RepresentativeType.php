<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class RepresentativeType extends Model
{
    use HasFactory, HasTranslations;
    
    public $timestamps = false;
    
    public $translatable = ['name'];
}

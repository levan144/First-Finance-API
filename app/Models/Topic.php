<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Topic extends Model
{
    use HasFactory, HasTranslations;
    public $translatable = ['name'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}

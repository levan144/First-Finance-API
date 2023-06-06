<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Carbon\Carbon;
class Ticket extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $fillable = ['user_id', 'topic_id', 'subject', 'message'];
    protected $dates = ['read_at', 'closed_at'];
    
    public function registerMediaCollections(): void
{
    $this->addMediaCollection('attachments');
}
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('id', 'asc');
    }
    
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
    
    public function getUnreadAttribute()
    {
        $readAt = $this->read_at;

        if ($readAt === null) {
            return null; // or any other value to indicate unread status
        }

        return Carbon::parse($readAt)->format('Y-m-d H:i:s');
    }
}

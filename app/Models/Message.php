<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
class Message extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;
    
    protected $fillable = ['ticket_id', 'user_id', 'message'];
    protected $dates = ['read_at'];
    
    public function registerMediaCollections(): void
{
    $this->addMediaCollection('attachments');
}
    
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
    
    protected static function boot()
    {
        parent::boot();

        static::saved(function (self $message) {
            if ($message->attachment_path) {
                collect($message->attachment_path)->each(function ($path) use ($message) {
                    Attachment::create([
                        'file' => $path,
                        'attachable_id' => $message->id,
                        'attachable_type' => get_class($message),
                    ]);

                    // Remove the temporary path from the model
                    $message->attachment_path = null;
                    $message->save();
                });
            }
        });
    }
}

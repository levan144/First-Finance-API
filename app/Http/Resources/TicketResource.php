<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public static $wrap = null;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'subject' => $this->subject,
            'topic_id' => $this->topic_id,
            'topic_name' => $this->topic->name,
            'message' => $this->message,
            'messages' => $this->messages,
            // other message fields...
            'attachments' => $this->getMedia('attachments')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'file' => $media->getUrl(),
                    // other media fields...
                ];
            }),
            'read_at' => $this->read_at,
            'unread' => $this->messages->whereNull('read_at')->isNotEmpty(),
            'messages_count' => $this->messages->count(),
            'user' => $this->user,
            'closed_at' => $this->closed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

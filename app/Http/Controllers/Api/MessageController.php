<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Ticket;
// use App\Models\Attachment;

use Illuminate\Http\Request;
use App\Http\Requests\Api\MessageRequest;
use Illuminate\Support\Facades\Auth;
use Storage;
use App\Http\Resources\MessageResource;

class MessageController extends Controller
{
    // public function index(LocaleRequest $request, $id)
    // {
    //     return Message::all();
    // }

    public function store(MessageRequest $request)
    {
        $user = Auth::user();
        $locale = $request->locale;
        $messageData = $request->validated();
        $messageData['user_id'] = $user->id;
        $ticketId = $request->ticket_id;
        $messageData['ticket_id'] = $ticketId;
        $message = Message::create($messageData);
        // Set the attachable_id for each attachment
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachmentFile) {
                
                $message
                   ->addMedia($attachmentFile)
                   ->toMediaCollection('attachments', 'attachments');
            }
        }
        return new MessageResource($message);

    }
    
    public function markAsRead($id)
    {
        $message = Message::findOrFail($id);
        $message->read_at = now();
        $message->save();

        return response()->json($message);
    }

    // public function show(Message $message)
    // {
    //     return $message;
    // }

    // public function update(Request $request, Message $message)
    // {
    //     $message->update($request->all());
    //     return response()->json($message, 200);
    // }

    // public function delete(Message $message)
    // {
    //     $message->delete();
    //     return response()->json(null, 204);
    // }
}

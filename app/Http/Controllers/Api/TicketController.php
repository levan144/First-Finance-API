<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\TicketRequest;
use App\Http\Requests\Api\LocaleRequest;
// use App\Models\Attachment;
use Storage;
use App\Models\Ticket;
use App\Models\User;
use Auth;
use App\Http\Resources\TicketResource;
class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(LocaleRequest $request)
    {
        $user = Auth::user();
        $locale = $request->locale;
        $tickets = Ticket::where('user_id', $user->id)->with('messages')
            ->get()
            ->map(function ($ticket) use ($user, $locale) {
                $unreadMessages = $ticket->messages->whereNull('read_at');
                // $ticket->unread = $unreadMessages->isNotEmpty();
                $ticket->messages_count = $ticket->messages->count();
                $ticket->topic_name = $ticket->topic->getTranslation('name', $locale);
                return new TicketResource($ticket);
            });

        return response()->json($tickets);
    }
    
    public function markAsRead($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->read_at = now();
        $ticket->save();

        return response()->json($ticket);
    }
    
    public function markAllMessagesAsRead($id)
    {
        $ticket = Ticket::findOrFail($id);
        $user = Auth::user();

        // Update the read_at timestamp of all unread messages under the ticket
        $ticket->messages()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Update the ticket's read_at timestamp
        if(!$ticket->read_at){
            $ticket->read_at = now();
            $ticket->save();
        }

        return response()->json($ticket);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(TicketRequest $request)
    {
        $locale = $request->locale;
        $ticketData = $request->validated();
        $ticketData['user_id'] = Auth::id();
       
        $ticket = Ticket::create($ticketData);
        // Set the attachable_id for each attachment
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachmentFile) {
                
                $ticket
                   ->addMedia($attachmentFile)
                   ->toMediaCollection('attachments', 'public');
            }
        }
        $ticket->load(['messages']);
        $ticket->topic_name = $ticket->topic->getTranslation('name', $locale);
        
        $admin = User::find(1);
        $admin->notify(new \App\Notifications\Nova\TicketReceived(__('The customer has opened a new support ticket'), $ticket->id));

        return new TicketResource($ticket);
    }

    /**
     * Display the specified resource.
     */
    public function show(LocaleRequest $request, $id)
    {
        $user = Auth::user();
        $locale = $request->locale;
        $ticket = Ticket::with(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }])->findOrFail($id);

        return new TicketResource($ticket);
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(TicketRequest $request, $id)
    // {
    //     $ticket = Ticket::findOrFail($id);
    //     $ticket->update($request->validated());

    //     return response()->json($ticket);
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy($id)
    // {
    //     $ticket = Ticket::findOrFail($id);
    //     $ticket->delete();

    //     return response()->json(null, 204);
    // }
    
    /**
     * Close the specified resource from storage.
     */
    public function close($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->closed_at = now();
        $ticket->save();
        return response()->json($ticket);
    }
}

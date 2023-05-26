<?php

namespace App\Observers;

use App\Models\Document;
use App\Notifications\VerificationRequestSubmitted;
use App\Notifications\VerificationStatusUpdated;
use App\Notifications\VerificationRequestRejected;

class DocumentObserver
{
    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        // Assuming that the document is related to a user through a 'user' relationship
        $user = $document->user;

        // Notify the user about the verification request
        $user->notify(new VerificationRequestSubmitted());
    }

    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        if ($document->wasChanged('status')) {
            // Get the original status value
            // $originalStatus = $document->getOriginal('status');
            // // Notify the user about the status change
            if ($document->status == 'rejected' && $originalStatus != 'rejected') {
                // Assuming that the document is related to a user through a 'user' relationship
                $document->user->notify(new VerificationRequestRejected());
            } elseif ($document->status == 'approved' && $originalStatus != 'approved') {
                // $document->user->notify(new \App\Notifications\DocumentApprovedNotification());
            }
            // Notify the user about the verification request
            // $document->user->notify(new VerificationSubmittedNotification());
        }
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(Document $document): void
    {
        //
    }

    /**
     * Handle the Document "restored" event.
     */
    public function restored(Document $document): void
    {
        //
    }

    /**
     * Handle the Document "force deleted" event.
     */
    public function forceDeleted(Document $document): void
    {
        //
    }
}

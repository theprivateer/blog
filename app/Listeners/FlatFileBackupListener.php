<?php

namespace App\Listeners;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Services\FlatFileBackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FlatFileBackupListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PostSaved|PostDeleted $event): void
    {
        $provider = new FlatFileBackupService;
        if ($event instanceof PostDeleted) {
            $provider->delete($event->record);
            return;
        }

        $provider->save($event->record);
    }
}

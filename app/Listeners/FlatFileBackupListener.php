<?php

namespace App\Listeners;

use App\Events\PostSaved;
use App\Events\PostDeleted;
use App\Services\SitemapService;
use App\Services\FlatFileBackupService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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

        // TODO: break off into separate listener that calls the Artisan command
        (new SitemapService)->generate();
    }
}

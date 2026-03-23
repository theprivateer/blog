<?php

namespace App\Listeners;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Services\FlatFileBackupService;
use App\Services\SitemapService;

class FlatFileBackupListener
{
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

        /** @todo Break off into separate listener that calls the Artisan command */
        (new SitemapService)->generate();
    }
}

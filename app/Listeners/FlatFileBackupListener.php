<?php

namespace App\Listeners;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Services\FlatFileBackupService;
use App\Services\SitemapService;

class FlatFileBackupListener
{
    public function __construct(
        private FlatFileBackupService $flatFileBackupService,
        private SitemapService $sitemapService,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(PostSaved|PostDeleted $event): void
    {
        if ($event instanceof PostDeleted) {
            $this->flatFileBackupService->delete($event->record);

            return;
        }

        $this->flatFileBackupService->save($event->record);

        /** @todo Break off into separate listener that calls the Artisan command */
        $this->sitemapService->generate();
    }
}

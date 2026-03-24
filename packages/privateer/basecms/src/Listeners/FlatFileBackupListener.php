<?php

namespace Privateer\Basecms\Listeners;

use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Services\FlatFileBackupService;

class FlatFileBackupListener
{
    public function __construct(
        private FlatFileBackupService $flatFileBackupService,
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

        $sitemapService = config('basecms.services.sitemap');

        if (is_string($sitemapService) && class_exists($sitemapService)) {
            app($sitemapService)->generate();
        }
    }
}

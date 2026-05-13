<?php

namespace App\Services;

use App\Models\Note;
use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Services\SitemapService as BasecmsSitemapService;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

// Extends the package's SitemapService rather than replacing it. The package generates
// entries for Posts, Pages, and Categories; extendSitemap() is the hook to add app-specific
// content types. Registered in config/basecms.php under services.sitemap so the backup
// listener and static site generator both resolve this subclass automatically.
class SitemapService extends BasecmsSitemapService
{
    protected function extendSitemap(Sitemap $sitemap, Site $site): Sitemap
    {
        $notes = Note::query()
            ->forSite($site)
            ->latest()
            ->get();

        foreach ($notes as $note) {
            $sitemap->add(
                Url::create(route('notes.show', $note))
                    ->setLastModificationDate($note->updated_at)
            );
        }

        return $sitemap;
    }
}

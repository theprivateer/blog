<?php

namespace App\Services;

use App\Models\Note;
use Privateer\Basecms\Services\SitemapService as BasecmsSitemapService;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapService extends BasecmsSitemapService
{
    protected function extendSitemap(Sitemap $sitemap): Sitemap
    {
        $notes = Note::query()->latest()->get();

        foreach ($notes as $note) {
            $sitemap->add(
                Url::create(route('notes.show', $note))
                    ->setLastModificationDate($note->updated_at)
            );
        }

        return $sitemap;
    }
}

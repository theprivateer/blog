<?php

namespace App\Services;

use App\Models\Moment;
use App\Models\Note;
use App\Models\Page;
use App\Models\Post;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    public function generate(): void
    {
        $homepage = Page::where('is_homepage', true)->firstOrFail();

        $sitemap = Sitemap::create()
                        ->add(Url::create('/')->setLastModificationDate($homepage->updated_at));

        $sitemap = $this->pages($sitemap);
        $sitemap = $this->posts($sitemap);
        $sitemap = $this->notes($sitemap);
        $sitemap = $this->moments($sitemap);

        $sitemap->writeToFile(public_path('sitemap.xml'));
    }

    private function pages($sitemap)
    {
        $pages = Page::where('draft', false)->where('is_homepage', false)->get();

        foreach ($pages as $page) {
            $sitemap->add(
                Url::create($page->slug)->setLastModificationDate($page->updated_at)
            );
        }

        return $sitemap;
    }

    private function posts($sitemap)
    {
        $posts = Post::published()->get();

        foreach ($posts as $post) {
            $sitemap->add(
                Url::create(route('posts.show', $post->slug))
                    ->setLastModificationDate(
                        $post->updated_at->gte($post->published_at) ? $post->updated_at : $post->published_at
                    )
            );
        }

        return $sitemap;
    }

    private function notes($sitemap)
    {
        $notes = Note::latest()->get();

        foreach ($notes as $note) {
            $sitemap->add(
                Url::create(route('notes.show', $note))
                    ->setLastModificationDate($note->updated_at)
            );
        }

        return $sitemap;
    }

    private function moments($sitemap)
    {
        $moments = Moment::latest()->get();

        foreach ($moments as $moment) {
            $sitemap->add(
                Url::create(route('moments.show', $moment))
                    ->setLastModificationDate($moment->updated_at)
            );
        }

        return $sitemap;
    }
}

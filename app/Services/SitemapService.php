<?php

namespace App\Services;

use App\Models\Category;
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
        $sitemap = $this->categories($sitemap);
        $sitemap = $this->posts($sitemap);
        $sitemap = $this->notes($sitemap);

        $sitemap->writeToFile(public_path('sitemap.xml'));
    }

    private function pages(Sitemap $sitemap): Sitemap
    {
        $pages = Page::where('draft', false)->where('is_homepage', false)->get();

        foreach ($pages as $page) {
            $sitemap->add(
                Url::create($page->slug)->setLastModificationDate($page->updated_at)
            );
        }

        return $sitemap;
    }

    private function categories(Sitemap $sitemap): Sitemap
    {
        $categories = Category::get();

        foreach ($categories as $category) {
            $sitemap->add(
                Url::create(route('categories.show', $category))->setLastModificationDate($category->updated_at)
            );
        }

        return $sitemap;
    }

    private function posts(Sitemap $sitemap): Sitemap
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

    private function notes(Sitemap $sitemap): Sitemap
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
}

<?php

namespace Privateer\Basecms\Services;

use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
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
        $sitemap = $this->extendSitemap($sitemap);

        $sitemap->writeToFile(public_path('sitemap.xml'));
    }

    protected function pages(Sitemap $sitemap): Sitemap
    {
        $pages = Page::query()
            ->where('draft', false)
            ->where('is_homepage', false)
            ->get();

        foreach ($pages as $page) {
            $sitemap->add(
                Url::create($page->slug)->setLastModificationDate($page->updated_at)
            );
        }

        return $sitemap;
    }

    protected function categories(Sitemap $sitemap): Sitemap
    {
        $categories = Category::query()->get();

        foreach ($categories as $category) {
            $sitemap->add(
                Url::create(route('categories.show', $category))->setLastModificationDate($category->updated_at)
            );
        }

        return $sitemap;
    }

    protected function posts(Sitemap $sitemap): Sitemap
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

    protected function extendSitemap(Sitemap $sitemap): Sitemap
    {
        return $sitemap;
    }
}

<?php

namespace Privateer\Basecms\Services;

use Illuminate\Support\Facades\URL;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Models\Site;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url as SitemapUrl;

class SitemapService
{
    public function __construct(private readonly SiteManager $siteManager) {}

    public function generate(?Site $site = null): void
    {
        $site ??= $this->siteManager->required();

        $this->siteManager->runFor($site, function () use ($site): void {
            $this->forceRootUrl($site);

            $homepage = Page::query()
                ->forSite($site)
                ->where('is_homepage', true)
                ->firstOrFail();

            $sitemap = Sitemap::create()
                ->add(SitemapUrl::create('/')->setLastModificationDate($homepage->updated_at));

            $sitemap = $this->pages($sitemap, $site);
            $sitemap = $this->categories($sitemap, $site);
            $sitemap = $this->posts($sitemap, $site);
            $sitemap = $this->extendSitemap($sitemap, $site);

            $sitemap->writeToFile(public_path('sitemap.xml'));
        });
    }

    protected function pages(Sitemap $sitemap, Site $site): Sitemap
    {
        $pages = Page::query()
            ->forSite($site)
            ->where('draft', false)
            ->where('is_homepage', false)
            ->get();

        foreach ($pages as $page) {
            $sitemap->add(
                SitemapUrl::create($page->slug)->setLastModificationDate($page->updated_at)
            );
        }

        return $sitemap;
    }

    protected function categories(Sitemap $sitemap, Site $site): Sitemap
    {
        $categories = Category::query()
            ->forSite($site)
            ->get();

        foreach ($categories as $category) {
            $sitemap->add(
                SitemapUrl::create(route('categories.show', $category))->setLastModificationDate($category->updated_at)
            );
        }

        return $sitemap;
    }

    protected function posts(Sitemap $sitemap, Site $site): Sitemap
    {
        $posts = Post::query()
            ->forSite($site)
            ->published()
            ->get();

        foreach ($posts as $post) {
            $sitemap->add(
                SitemapUrl::create(route('posts.show', $post->slug))
                    ->setLastModificationDate(
                        $post->updated_at->gte($post->published_at) ? $post->updated_at : $post->published_at
                    )
            );
        }

        return $sitemap;
    }

    protected function extendSitemap(Sitemap $sitemap, Site $site): Sitemap
    {
        return $sitemap;
    }

    protected function forceRootUrl(Site $site): void
    {
        $baseUrl = $site->primaryUrl() ?: (string) config('app.url');

        URL::forceRootUrl($baseUrl);

        $scheme = parse_url($baseUrl, PHP_URL_SCHEME);

        if (is_string($scheme) && $scheme !== '') {
            URL::forceScheme($scheme);
        }
    }
}

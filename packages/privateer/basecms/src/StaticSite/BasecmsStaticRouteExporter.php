<?php

namespace Privateer\Basecms\StaticSite;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Services\SiteManager;

class BasecmsStaticRouteExporter implements StaticRouteExporter
{
    public function __construct(private readonly SiteManager $siteManager) {}

    /**
     * @return iterable<StaticRoute>
     */
    public function export(): iterable
    {
        $routes = [
            StaticRoute::html('/', '/', 'index.html', 'home'),
        ];

        $routes = [...$routes, ...$this->blogRoutes()];
        $routes = [...$routes, ...$this->postRoutes()];
        $routes = [...$routes, ...$this->categoryRoutes()];
        $routes = [...$routes, ...$this->pageRoutes()];
        $routes = [...$routes, ...$this->legacyRedirectRoutes()];

        return $routes;
    }

    /**
     * @return array<int, StaticRoute>
     */
    private function blogRoutes(): array
    {
        $routes = [];
        $site = $this->siteManager->required();
        $pageCount = $this->paginationCount((new Post)->getPerPage(), Post::query()->forSite($site)->published()->count());

        for ($page = 1; $page <= $pageCount; $page++) {
            $sourceUri = $page === 1 ? '/blog' : '/blog?page='.$page;
            $publicUri = $page === 1 ? '/blog/' : '/blog/page/'.$page.'/';
            $outputPath = $page === 1 ? 'blog/index.html' : 'blog/page/'.$page.'/index.html';

            $routes[] = StaticRoute::html($sourceUri, $publicUri, $outputPath, 'posts.index');
        }

        return $routes;
    }

    /**
     * @return array<int, StaticRoute>
     */
    private function postRoutes(): array
    {
        return Post::query()
            ->forSite($this->siteManager->required())
            ->published()
            ->get()
            ->map(fn (Post $post): StaticRoute => StaticRoute::html(
                sourceUri: route('posts.show', $post, false),
                publicUri: route('posts.show', $post, false).'/',
                outputPath: 'blog/'.$post->slug.'/index.html',
                routeName: 'posts.show',
                routeParameters: ['post' => $post->slug],
            ))
            ->all();
    }

    /**
     * @return array<int, StaticRoute>
     */
    private function categoryRoutes(): array
    {
        $routes = [];
        $site = $this->siteManager->required();

        foreach (Category::query()->forSite($site)->get() as $category) {
            $postCount = Post::query()
                ->forSite($site)
                ->published()
                ->where('category_id', $category->id)
                ->count();

            if ($postCount === 0) {
                continue;
            }

            $pageCount = $this->paginationCount((new Post)->getPerPage(), $postCount);

            for ($page = 1; $page <= $pageCount; $page++) {
                $sourcePath = route('categories.show', $category, false);
                $sourceUri = $page === 1 ? $sourcePath : $sourcePath.'?page='.$page;
                $publicUri = $page === 1 ? $sourcePath.'/' : $sourcePath.'/page/'.$page.'/';
                $outputPath = $page === 1
                    ? 'category/'.$category->slug.'/index.html'
                    : 'category/'.$category->slug.'/page/'.$page.'/index.html';

                $routes[] = StaticRoute::html(
                    sourceUri: $sourceUri,
                    publicUri: $publicUri,
                    outputPath: $outputPath,
                    routeName: 'categories.show',
                    routeParameters: ['category' => $category->slug],
                );
            }
        }

        return $routes;
    }

    /**
     * @return array<int, StaticRoute>
     */
    private function pageRoutes(): array
    {
        $routes = [];

        foreach (Page::query()->forSite($this->siteManager->required())->where('draft', false)->get() as $page) {
            if ($page->is_homepage) {
                continue;
            }

            $path = '/'.$page->slug;
            $matchedRoute = Route::getRoutes()->match(Request::create($path));

            if ($matchedRoute->getName() !== 'pages.show') {
                continue;
            }

            $routes[] = StaticRoute::html(
                sourceUri: $path,
                publicUri: $path.'/',
                outputPath: $page->slug.'/index.html',
                routeName: 'pages.show',
                routeParameters: ['page' => $page->slug],
            );
        }

        return $routes;
    }

    /**
     * @return array<int, StaticRoute>
     */
    private function legacyRedirectRoutes(): array
    {
        $routes = [
            StaticRoute::redirect('/posts', '/posts/', 'posts/index.html', '/blog'),
        ];

        foreach (Post::query()->forSite($this->siteManager->required())->published()->get() as $post) {
            $routes[] = StaticRoute::redirect(
                sourceUri: '/posts/'.$post->slug,
                publicUri: '/posts/'.$post->slug.'/',
                outputPath: 'posts/'.$post->slug.'/index.html',
                redirectTo: route('posts.show', $post, false),
            );
        }

        return $routes;
    }

    private function paginationCount(int $perPage, int $total): int
    {
        return max(1, (int) ceil($total / max(1, $perPage)));
    }
}

<?php

namespace Privateer\Basecms\Services;

use Closure;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Privateer\Basecms\Models\Site;
use Privateer\Basecms\StaticSite\BasecmsStaticRouteExporter;
use Privateer\Basecms\StaticSite\FeedStaticRouteExporter;
use Privateer\Basecms\StaticSite\StaticRoute;
use Privateer\Basecms\StaticSite\StaticRouteExporter;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class StaticSiteGenerator
{
    private const BOOST_INJECT_MIDDLEWARE = 'Laravel\\Boost\\Middleware\\InjectBoost';

    public function __construct(private readonly SiteManager $siteManager) {}

    /**
     * @return array<int, StaticRoute>
     */
    public function routes(?Site $site = null): array
    {
        $warnings = [];

        return $this->resolveRoutes($warnings, $site);
    }

    /**
     * @param  null|callable(StaticRoute): void  $onRouteProcessed
     * @return array{exported_count: int, output_path: string, skipped_count: int, total_count: int, warnings: array<int, string>}
     */
    public function generate(?callable $onRouteProcessed = null, ?Site $site = null): array
    {
        $outputPath = (string) config('basecms.static_site.output_path', storage_path('app/static-site'));
        $warnings = [];
        $exportedCount = 0;
        $skippedCount = 0;
        $site ??= $this->siteManager->required();

        $routes = $this->resolveRoutes($warnings, $site);
        $totalCount = count($routes);
        $linkMap = $this->buildLinkMap($routes);

        $this->prepareOutputDirectory($outputPath);
        $this->copyPublicAssets($outputPath);

        $runtimeState = $this->applyRuntimeOverrides();
        $baseUrl = rtrim($site->primaryUrl() ?: (string) config('basecms.static_site.base_url', (string) config('app.url')), '/');

        try {
            foreach ($routes as $route) {
                if ($route->kind === 'redirect') {
                    $this->writeFile(
                        $outputPath,
                        $route->outputPath,
                        $this->renderRedirectDocument($this->rewriteUri($route->redirectTo ?? '/', $linkMap, $baseUrl))
                    );
                    $exportedCount++;
                    if ($onRouteProcessed !== null) {
                        $onRouteProcessed($route);
                    }

                    continue;
                }

                $response = $this->dispatch($route->sourceUri, $baseUrl);
                $statusCode = $response->getStatusCode();

                if ($statusCode !== Response::HTTP_OK) {
                    $warnings[] = "Skipping [{$route->sourceUri}] because it returned status [{$statusCode}].";
                    $skippedCount++;
                    if ($onRouteProcessed !== null) {
                        $onRouteProcessed($route);
                    }

                    continue;
                }

                $content = $response->getContent();

                if (! is_string($content)) {
                    $warnings[] = "Skipping [{$route->sourceUri}] because it did not return string content.";
                    $skippedCount++;
                    if ($onRouteProcessed !== null) {
                        $onRouteProcessed($route);
                    }

                    continue;
                }

                if ($route->kind === 'html') {
                    $content = $this->rewriteHtml($content, $linkMap, $baseUrl);
                }

                $this->writeFile($outputPath, $route->outputPath, $content);
                $exportedCount++;
                if ($onRouteProcessed !== null) {
                    $onRouteProcessed($route);
                }
            }

            if (config('basecms.static_site.generate_sitemap', true)) {
                $this->generateAndCopySitemap($outputPath, $warnings, $site);
            }
        } finally {
            $this->restoreRuntimeOverrides($runtimeState);
        }

        return [
            'exported_count' => $exportedCount,
            'output_path' => $outputPath,
            'skipped_count' => $skippedCount,
            'total_count' => $totalCount,
            'warnings' => $warnings,
        ];
    }

    /**
     * @param  array<int, string>  $warnings
     * @return array<int, StaticRoute>
     */
    private function resolveRoutes(array &$warnings, ?Site $site = null): array
    {
        $routes = [];
        $site ??= $this->siteManager->required();
        $exporters = [app(BasecmsStaticRouteExporter::class)];

        if (config('basecms.static_site.generate_feeds', true)) {
            $exporters[] = app(FeedStaticRouteExporter::class);
        }

        foreach (config('basecms.static_site.exporters', []) as $configuredExporter) {
            if ($configuredExporter instanceof Closure) {
                $exporters[] = $configuredExporter;

                continue;
            }

            if (is_string($configuredExporter) && class_exists($configuredExporter)) {
                $instance = app($configuredExporter);

                if ($instance instanceof StaticRouteExporter) {
                    $exporters[] = $instance;

                    continue;
                }
            }

            $warnings[] = 'Skipping an invalid static site exporter configuration entry.';
        }

        foreach ($exporters as $exporter) {
            $exportedRoutes = $this->siteManager->runFor($site, function () use ($exporter) {
                return $exporter instanceof Closure
                    ? $exporter()
                    : $exporter->export();
            });

            foreach ($exportedRoutes as $route) {
                if (! $route instanceof StaticRoute) {
                    continue;
                }

                $routes[] = $route;
            }
        }

        $this->assertUniqueOutputPaths($routes);

        return $routes;
    }

    /**
     * @param  array<int, StaticRoute>  $routes
     * @return array<string, string>
     */
    private function buildLinkMap(array $routes): array
    {
        $map = [];

        foreach ($routes as $route) {
            $map[$this->normalizeUri($route->sourceUri)] = $route->publicUri;
        }

        return $map;
    }

    /**
     * @return array{
     *     app_env: mixed,
     *     config: array<string, mixed>,
     *     middleware_groups: array<string, array<int, string>>
     * }
     */
    private function applyRuntimeOverrides(): array
    {
        $router = app(Router::class);
        $originalMiddlewareGroups = $router->getMiddlewareGroups();
        $originalAppEnvironment = app()->environment();
        $originalConfig = [];

        foreach ($this->configuredRuntimeOverrides() as $key => $value) {
            $originalConfig[$key] = config($key);
            config()->set($key, $value);
        }

        if (array_key_exists('app.env', $this->configuredRuntimeOverrides())) {
            app()->instance('env', config('app.env'));
        }

        $baseUrl = rtrim((string) config('basecms.static_site.base_url', (string) config('app.url')), '/');
        $originalConfig['app.url'] = $originalConfig['app.url'] ?? config('app.url');
        config()->set('app.url', $baseUrl);

        URL::forceRootUrl($baseUrl);

        $scheme = parse_url($baseUrl, PHP_URL_SCHEME);
        if (is_string($scheme) && $scheme !== '') {
            URL::forceScheme($scheme);
        }

        if (
            config('boost.browser_logs_watcher') === false
            && class_exists(self::BOOST_INJECT_MIDDLEWARE)
        ) {
            $router->removeMiddlewareFromGroup('web', self::BOOST_INJECT_MIDDLEWARE);
        }

        return [
            'app_env' => $originalAppEnvironment,
            'config' => $originalConfig,
            'middleware_groups' => $originalMiddlewareGroups,
        ];
    }

    /**
     * @param  array{
     *     app_env: mixed,
     *     config: array<string, mixed>,
     *     middleware_groups: array<string, array<int, string>>
     * }  $runtimeState
     */
    private function restoreRuntimeOverrides(array $runtimeState): void
    {
        foreach ($runtimeState['config'] as $key => $value) {
            config()->set($key, $value);
        }

        app()->instance('env', $runtimeState['app_env']);

        $router = app(Router::class);
        $router->flushMiddlewareGroups();

        foreach ($runtimeState['middleware_groups'] as $group => $middleware) {
            $router->middlewareGroup($group, $middleware);
        }

        $appUrl = (string) config('app.url');

        URL::forceRootUrl($appUrl);

        $scheme = parse_url($appUrl, PHP_URL_SCHEME);
        if (is_string($scheme) && $scheme !== '') {
            URL::forceScheme($scheme);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function configuredRuntimeOverrides(): array
    {
        $runtimeOverrides = config('basecms.static_site.runtime_overrides', []);

        return is_array($runtimeOverrides) ? $runtimeOverrides : [];
    }

    private function prepareOutputDirectory(string $outputPath): void
    {
        if (config('basecms.static_site.clean_output_before_build', true) && File::exists($outputPath)) {
            File::deleteDirectory($outputPath);
        }

        File::ensureDirectoryExists($outputPath);
    }

    private function copyPublicAssets(string $outputPath): void
    {
        if (! File::isDirectory(public_path())) {
            return;
        }

        foreach (File::allFiles(public_path()) as $file) {
            $relativePath = $file->getRelativePathname();

            if (in_array($relativePath, ['.htaccess', 'index.php', 'sitemap.xml'], true)) {
                continue;
            }

            $destination = rtrim($outputPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$relativePath;
            File::ensureDirectoryExists(dirname($destination));
            File::copy($file->getPathname(), $destination);
        }
    }

    private function dispatch(string $sourceUri, string $baseUrl): Response
    {
        $request = Request::create($baseUrl.$sourceUri, 'GET');
        $kernel = app(Kernel::class);
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        return $response;
    }

    /**
     * @param  array<string, string>  $linkMap
     */
    private function rewriteHtml(string $html, array $linkMap, string $baseUrl): string
    {
        $previousState = libxml_use_internal_errors(true);
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML($html);

        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query('//*[@href or @src or @action]');

        if ($nodes !== false) {
            foreach ($nodes as $node) {
                foreach (['href', 'src', 'action'] as $attribute) {
                    if (! $node->hasAttribute($attribute)) {
                        continue;
                    }

                    $rewritten = $this->rewriteUri($node->getAttribute($attribute), $linkMap, $baseUrl);
                    $node->setAttribute($attribute, $rewritten);
                }
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        return $document->saveHTML() ?: $html;
    }

    /**
     * @param  array<string, string>  $linkMap
     */
    private function rewriteUri(string $uri, array $linkMap, string $baseUrl): string
    {
        if ($uri === '' || str_starts_with($uri, '#')) {
            return $uri;
        }

        if (preg_match('/^(mailto:|tel:|data:|javascript:)/', $uri) === 1) {
            return $uri;
        }

        $fragment = parse_url($uri, PHP_URL_FRAGMENT);
        $path = parse_url($uri, PHP_URL_PATH);
        $query = parse_url($uri, PHP_URL_QUERY);
        $host = parse_url($uri, PHP_URL_HOST);
        $baseHost = parse_url($baseUrl, PHP_URL_HOST);

        if (is_string($host) && is_string($baseHost) && $host !== $baseHost) {
            return $uri;
        }

        if (! is_string($path) || $path === '') {
            return $uri;
        }

        if (! str_starts_with($path, '/')) {
            return $uri;
        }

        $normalized = $this->normalizeUri($path.(is_string($query) ? '?'.$query : ''));
        $rewritten = $linkMap[$normalized] ?? $path.(is_string($query) && $query !== '' ? '?'.$query : '');

        if (is_string($fragment) && $fragment !== '') {
            $rewritten .= '#'.$fragment;
        }

        return $rewritten;
    }

    private function normalizeUri(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $query = parse_url($uri, PHP_URL_QUERY);

        if (! is_string($path) || $path === '') {
            $path = '/';
        }

        if (! is_string($query) || $query === '') {
            return $path;
        }

        parse_str($query, $queryParameters);
        ksort($queryParameters);

        $normalizedQuery = http_build_query($queryParameters);

        return $normalizedQuery === '' ? $path : $path.'?'.$normalizedQuery;
    }

    private function writeFile(string $outputPath, string $relativePath, string $contents): void
    {
        $destination = rtrim($outputPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.ltrim($relativePath, DIRECTORY_SEPARATOR);

        File::ensureDirectoryExists(dirname($destination));
        File::put($destination, $contents);
    }

    private function renderRedirectDocument(string $destination): string
    {
        $escapedDestination = e($destination);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="refresh" content="0;url={$escapedDestination}">
        <link rel="canonical" href="{$escapedDestination}">
        <title>Redirecting...</title>
    </head>
    <body>
        <p>Redirecting to <a href="{$escapedDestination}">{$escapedDestination}</a>.</p>
    </body>
</html>
HTML;
    }

    /**
     * @param  array<int, StaticRoute>  $routes
     */
    private function assertUniqueOutputPaths(array $routes): void
    {
        $outputPaths = [];

        foreach ($routes as $route) {
            if (isset($outputPaths[$route->outputPath])) {
                throw new RuntimeException("Duplicate static output path detected: [{$route->outputPath}].");
            }

            $outputPaths[$route->outputPath] = true;
        }
    }

    /**
     * @param  array<int, string>  $warnings
     */
    private function generateAndCopySitemap(string $outputPath, array &$warnings, Site $site): void
    {
        $sitemapService = config('basecms.services.sitemap');

        if (! is_string($sitemapService) || ! class_exists($sitemapService)) {
            $warnings[] = 'Skipping sitemap generation because no sitemap service is configured.';

            return;
        }

        $service = app($sitemapService);

        if (! method_exists($service, 'generate')) {
            $warnings[] = "Skipping sitemap generation because [{$sitemapService}] does not define a generate method.";

            return;
        }

        $service->generate($site);

        $sitemapPath = public_path('sitemap.xml');
        if (! File::exists($sitemapPath)) {
            $warnings[] = 'Skipping sitemap copy because the sitemap file was not created.';

            return;
        }

        File::copy($sitemapPath, rtrim($outputPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'sitemap.xml');
    }
}

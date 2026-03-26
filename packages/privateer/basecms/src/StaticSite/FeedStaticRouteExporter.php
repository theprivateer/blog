<?php

namespace Privateer\Basecms\StaticSite;

class FeedStaticRouteExporter implements StaticRouteExporter
{
    /**
     * @return iterable<StaticRoute>
     */
    public function export(): iterable
    {
        $routes = [];

        foreach (config('feed.feeds', []) as $name => $feed) {
            $url = data_get($feed, 'url');

            if (! is_string($url) || $url === '') {
                continue;
            }

            $routes[] = StaticRoute::artifact(
                sourceUri: $url,
                publicUri: $url,
                outputPath: ltrim($url, '/'),
                routeName: is_string($name) ? $name : null,
            );
        }

        return $routes;
    }
}

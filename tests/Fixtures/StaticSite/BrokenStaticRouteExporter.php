<?php

namespace Tests\Fixtures\StaticSite;

use Privateer\Basecms\StaticSite\StaticRoute;
use Privateer\Basecms\StaticSite\StaticRouteExporter;

class BrokenStaticRouteExporter implements StaticRouteExporter
{
    /**
     * @return iterable<StaticRoute>
     */
    public function export(): iterable
    {
        return [
            StaticRoute::html('/missing-page', '/missing-page/', 'missing-page/index.html'),
        ];
    }
}

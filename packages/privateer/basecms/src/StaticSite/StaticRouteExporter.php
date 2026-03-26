<?php

namespace Privateer\Basecms\StaticSite;

interface StaticRouteExporter
{
    /**
     * @return iterable<StaticRoute>
     */
    public function export(): iterable;
}

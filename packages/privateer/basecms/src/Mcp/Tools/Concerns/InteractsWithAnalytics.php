<?php

namespace Privateer\Basecms\Mcp\Tools\Concerns;

use Closure;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Privateer\Basecms\Services\SiteManager;
use Privateer\Basecms\Services\VisitAnalyticsSnapshot;

trait InteractsWithAnalytics
{
    use ResolvesMcpSite;

    /**
     * @return array<string, mixed>
     */
    protected function filterSchema(JsonSchema $schema): array
    {
        return [
            'window' => $schema->string()
                ->description('One of: '.implode(', ', array_keys(VisitAnalyticsSnapshot::windowOptions())).'.')
                ->enum(array_keys(VisitAnalyticsSnapshot::windowOptions())),
            'start_date' => $schema->string()->description('Start date (Y-m-d), used when window is "custom".'),
            'end_date' => $schema->string()->description('End date (Y-m-d), used when window is "custom".'),
            'response_status' => $schema->string()->description('Filter by HTTP response status code.'),
            'visitor_type' => $schema->string()->description('Filter by visitor classification.'),
            'site' => $schema->string()->description('Optional site key to scope analytics to (multisite installs only).'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedFilters(Request $request): array
    {
        return $request->validate([
            'window' => 'nullable|string',
            'start_date' => 'nullable|string',
            'end_date' => 'nullable|string',
            'response_status' => 'nullable|string',
            'visitor_type' => 'nullable|string',
            'site' => 'nullable|string',
        ]);
    }

    /**
     * @template TReturn
     *
     * @param  Closure(VisitAnalyticsSnapshot): TReturn  $callback
     * @return TReturn
     */
    protected function withSnapshot(?string $siteKey, Closure $callback): mixed
    {
        $site = $this->resolveSite($siteKey);

        return app(SiteManager::class)->runFor(
            $site,
            fn (): mixed => $callback(app(VisitAnalyticsSnapshot::class)),
        );
    }
}

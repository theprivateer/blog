<?php

namespace Privateer\Basecms\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Privateer\Basecms\Models\Visit;

class VisitAnalyticsSnapshot
{
    public function totals(): array
    {
        $baseQuery = $this->baseQuery();

        $totalVisits = (clone $baseQuery)->count();
        $uniqueVisits = (clone $baseQuery)->distinct('session_id')->count('session_id');

        return [
            'total_visits' => $totalVisits,
            'unique_visits' => $uniqueVisits,
            'average_daily_visits' => (int) ceil($totalVisits / 7),
        ];
    }

    public function topPaths(int $limit = 10): Collection
    {
        return $this->topPathsQuery()
            ->limit($limit)
            ->get();
    }

    public function topPathsQuery(): Builder
    {
        return Visit::query()
            ->selectRaw('MIN(id) as id, path, COUNT(*) as visit_count, COUNT(DISTINCT session_id) as unique_visit_count')
            ->where('created_at', '>=', $this->windowStart())
            ->groupBy('path')
            ->orderByDesc('visit_count')
            ->orderByDesc('unique_visit_count')
            ->orderBy('path');
    }

    public function formatPath(string $path): string
    {
        if ($path === '/') {
            return '/';
        }

        return '/'.ltrim($path, '/');
    }

    protected function baseQuery(): Builder
    {
        return Visit::query()
            ->where('created_at', '>=', $this->windowStart());
    }

    protected function windowStart(): Carbon
    {
        return now()->subDays(7);
    }
}

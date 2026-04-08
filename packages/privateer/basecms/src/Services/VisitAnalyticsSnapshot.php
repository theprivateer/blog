<?php

namespace Privateer\Basecms\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Privateer\Basecms\Models\Visit;

class VisitAnalyticsSnapshot
{
    public const DEFAULT_WINDOW = '7_days';

    public const DEFAULT_RESPONSE_STATUS = 'all';

    public const WINDOW_SEVEN_DAYS = '7_days';

    public const WINDOW_THREE_DAYS = '3_days';

    public const WINDOW_TWENTY_FOUR_HOURS = '24_hours';

    public const WINDOW_CUSTOM = 'custom';

    public function __construct(private readonly SiteManager $siteManager) {}

    /**
     * @return array<string, string>
     */
    public static function windowOptions(): array
    {
        return [
            self::WINDOW_SEVEN_DAYS => '7 days',
            self::WINDOW_THREE_DAYS => '3 days',
            self::WINDOW_TWENTY_FOUR_HOURS => '24 hours',
            self::WINDOW_CUSTOM => 'Custom date range',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function responseStatusOptions(): array
    {
        $options = [
            self::DEFAULT_RESPONSE_STATUS => 'All',
        ];

        Visit::query()
            ->forSite($this->siteManager->required())
            ->select('response_status')
            ->whereNotNull('response_status')
            ->distinct()
            ->orderBy('response_status')
            ->pluck('response_status')
            ->each(function (int $status) use (&$options): void {
                $options[(string) $status] = (string) $status;
            });

        return $options;
    }

    /**
     * @param  array<string, mixed>|null  $filters
     * @return array{total_visits: int, unique_visits: int, average_daily_visits: int, window_label: string, average_label: string}
     */
    public function totals(?array $filters = null): array
    {
        $window = $this->resolveWindow($filters);
        $baseQuery = $this->baseQuery($filters);

        $totalVisits = (clone $baseQuery)->count();
        $uniqueVisits = (clone $baseQuery)->distinct('session_id')->count('session_id');

        return [
            'total_visits' => $totalVisits,
            'unique_visits' => $uniqueVisits,
            'average_daily_visits' => (int) ceil($totalVisits / $window['average_day_count']),
            'window_label' => $window['window_label'],
            'average_label' => $window['average_label'],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $filters
     * @return array<int, array{visitor_type: string, label: string, count: int, percentage: float, formatted_percentage: string}>
     */
    public function classificationBreakdown(?array $filters = null): array
    {
        $baseQuery = $this->baseQuery($filters);
        $totalVisits = (clone $baseQuery)->count();

        $countsByType = (clone $baseQuery)
            ->selectRaw('COALESCE(visitor_type, ?) as visitor_type, COUNT(*) as visit_count', [VisitClassifier::TYPE_UNKNOWN])
            ->groupBy('visitor_type')
            ->pluck('visit_count', 'visitor_type');

        $breakdown = [];

        foreach ($this->classificationLabels() as $visitorType => $label) {
            $count = (int) ($countsByType[$visitorType] ?? 0);
            $percentage = $totalVisits > 0 ? round(($count / $totalVisits) * 100, 1) : 0.0;

            $breakdown[] = [
                'visitor_type' => $visitorType,
                'label' => $label,
                'count' => $count,
                'percentage' => $percentage,
                'formatted_percentage' => number_format($percentage, 1).'%',
            ];
        }

        return $breakdown;
    }

    /**
     * @param  array<string, mixed>|null  $filters
     */
    public function topPaths(int $limit = 10, ?array $filters = null): Collection
    {
        return $this->topPathsQuery($filters)
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<string, mixed>|null  $filters
     */
    public function topPathsQuery(?array $filters = null): Builder
    {
        return $this->baseQuery($filters)
            ->selectRaw('MIN(id) as id, path, COUNT(*) as visit_count, COUNT(DISTINCT session_id) as unique_visit_count')
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

    /**
     * @param  array<string, mixed>|null  $filters
     */
    protected function baseQuery(?array $filters = null): Builder
    {
        $window = $this->resolveWindow($filters);
        $responseStatus = Arr::get($filters, 'response_status', self::DEFAULT_RESPONSE_STATUS);

        return Visit::query()
            ->forSite($this->siteManager->required())
            ->whereBetween('created_at', [$window['start'], $window['end']])
            ->when(
                $responseStatus !== self::DEFAULT_RESPONSE_STATUS,
                fn (Builder $query): Builder => $query->where('response_status', (int) $responseStatus),
            );
    }

    /**
     * @return array<string, string>
     */
    protected function classificationLabels(): array
    {
        return [
            VisitClassifier::TYPE_LIKELY_HUMAN => 'Likely human',
            VisitClassifier::TYPE_AI_CRAWLER => 'AI crawler',
            VisitClassifier::TYPE_SEARCH_CRAWLER => 'Search crawler',
            VisitClassifier::TYPE_OTHER_BOT => 'Other bot',
            VisitClassifier::TYPE_UNKNOWN => 'Unknown',
        ];
    }

    /**
     * @param  array<string, mixed>|null  $filters
     * @return array{
     *     start: Carbon,
     *     end: Carbon,
     *     average_day_count: int,
     *     window_label: string,
     *     average_label: string
     * }
     */
    protected function resolveWindow(?array $filters = null): array
    {
        $window = Arr::get($filters, 'window', self::DEFAULT_WINDOW);

        return match ($window) {
            self::WINDOW_THREE_DAYS => $this->makeRelativeWindow(
                now()->subDays(3),
                now(),
                3,
                'Past 3 days',
                'Rolling 3-day average',
            ),
            self::WINDOW_TWENTY_FOUR_HOURS => $this->makeRelativeWindow(
                now()->subHours(24),
                now(),
                1,
                'Past 24 hours',
                '24-hour average',
            ),
            self::WINDOW_CUSTOM => $this->resolveCustomWindow($filters),
            default => $this->makeRelativeWindow(
                now()->subDays(7),
                now(),
                7,
                'Past 7 days',
                'Rolling 7-day average',
            ),
        };
    }

    protected function makeRelativeWindow(
        Carbon $start,
        Carbon $end,
        int $averageDayCount,
        string $windowLabel,
        string $averageLabel,
    ): array {
        return [
            'start' => $start,
            'end' => $end,
            'average_day_count' => $averageDayCount,
            'window_label' => $windowLabel,
            'average_label' => $averageLabel,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $filters
     * @return array{
     *     start: Carbon,
     *     end: Carbon,
     *     average_day_count: int,
     *     window_label: string,
     *     average_label: string
     * }
     */
    protected function resolveCustomWindow(?array $filters = null): array
    {
        $startDate = Arr::get($filters, 'start_date');
        $endDate = Arr::get($filters, 'end_date');

        if (! is_string($startDate) || ! is_string($endDate) || $startDate === '' || $endDate === '') {
            return $this->resolveWindow([
                'window' => self::DEFAULT_WINDOW,
            ]);
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        $dayCount = max($start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1, 1);
        $windowLabel = sprintf('From %s to %s', $start->toFormattedDateString(), $end->toFormattedDateString());

        return [
            'start' => $start,
            'end' => $end,
            'average_day_count' => $dayCount,
            'window_label' => $windowLabel,
            'average_label' => sprintf('Average per day (%d-day range)', $dayCount),
        ];
    }
}

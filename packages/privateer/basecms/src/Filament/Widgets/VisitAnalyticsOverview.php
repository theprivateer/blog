<?php

namespace Privateer\Basecms\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Privateer\Basecms\Services\VisitAnalyticsSnapshot;

class VisitAnalyticsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Visit analytics';

    protected function getStats(): array
    {
        $totals = app(VisitAnalyticsSnapshot::class)->totals($this->pageFilters);

        return [
            Stat::make('Total visits', number_format($totals['total_visits']))
                ->description($totals['window_label']),
            Stat::make('Unique visits', number_format($totals['unique_visits']))
                ->description('Distinct sessions'),
            Stat::make('Average daily visits', number_format($totals['average_daily_visits']))
                ->description($totals['average_label']),
        ];
    }
}

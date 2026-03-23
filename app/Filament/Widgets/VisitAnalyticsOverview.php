<?php

namespace App\Filament\Widgets;

use App\Services\VisitAnalyticsSnapshot;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VisitAnalyticsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Visit analytics';

    protected function getStats(): array
    {
        $totals = app(VisitAnalyticsSnapshot::class)->totals();

        return [
            Stat::make('Total visits', number_format($totals['total_visits']))
                ->description('Past 7 days'),
            Stat::make('Unique visits', number_format($totals['unique_visits']))
                ->description('Distinct sessions'),
            Stat::make('Average daily visits', number_format($totals['average_daily_visits']))
                ->description('Rolling 7-day average'),
        ];
    }
}

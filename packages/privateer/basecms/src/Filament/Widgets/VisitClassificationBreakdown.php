<?php

namespace Privateer\Basecms\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Privateer\Basecms\Services\VisitAnalyticsSnapshot;

class VisitClassificationBreakdown extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Visit classification';

    protected function getStats(): array
    {
        $snapshot = app(VisitAnalyticsSnapshot::class);
        $totals = $snapshot->totals($this->pageFilters);

        return collect($snapshot->classificationBreakdown($this->pageFilters))
            ->map(fn (array $entry): Stat => Stat::make($entry['label'], $entry['formatted_percentage'])
                ->description(number_format($entry['count']).' visits'))
            ->all();
    }

    protected function getDescription(): ?string
    {
        return app(VisitAnalyticsSnapshot::class)->totals($this->pageFilters)['window_label'];
    }
}

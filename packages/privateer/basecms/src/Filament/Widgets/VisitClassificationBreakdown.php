<?php

namespace Privateer\Basecms\Filament\Widgets;

use Filament\Schemas\Components\Component;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Privateer\Basecms\Services\VisitAnalyticsSnapshot;

class VisitClassificationBreakdown extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Visit classification';

    public static function canView(): bool
    {
        return (bool) config('basecms.visits.track_visits');
    }

    protected function getStats(): array
    {
        $snapshot = app(VisitAnalyticsSnapshot::class);

        return collect($snapshot->classificationBreakdown($this->pageFilters))
            ->map(fn (array $entry): Stat => Stat::make($entry['label'], $entry['formatted_percentage'])
                ->description(number_format($entry['count']).' visits'))
            ->all();
    }

    protected function getDescription(): ?string
    {
        return app(VisitAnalyticsSnapshot::class)->totals($this->pageFilters)['window_label'];
    }

    public function getSectionContentComponent(): Component
    {
        return parent::getSectionContentComponent()
            ->hidden(fn (): bool => ($this->pageFilters['visitor_type'] ?? VisitAnalyticsSnapshot::DEFAULT_VISITOR_TYPE) !== VisitAnalyticsSnapshot::DEFAULT_VISITOR_TYPE);
    }
}

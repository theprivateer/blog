<?php

namespace Privateer\Basecms\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Privateer\Basecms\Services\VisitAnalyticsSnapshot;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm {
        mountHasFilters as protected baseMountHasFilters;
    }

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return static::trackingEnabled() ? Heroicon::OutlinedChartBarSquare : parent::getNavigationIcon();
    }

    public static function getNavigationLabel(): string
    {
        return static::trackingEnabled() ? 'Analytics' : parent::getNavigationLabel();
    }

    public function getTitle(): string|Htmlable
    {
        return static::trackingEnabled() ? 'Analytics' : parent::getTitle();
    }

    protected static function trackingEnabled(): bool
    {
        return (bool) config('basecms.visits.track_visits');
    }

    public function mountHasFilters(): void
    {
        $this->baseMountHasFilters();

        $this->filters = array_merge([
            'window' => VisitAnalyticsSnapshot::DEFAULT_WINDOW,
            'response_status' => VisitAnalyticsSnapshot::DEFAULT_RESPONSE_STATUS,
            'visitor_type' => VisitAnalyticsSnapshot::DEFAULT_VISITOR_TYPE,
        ], $this->filters ?? []);

        $this->getFiltersForm()->fill($this->filters);

        if ($this->persistsFiltersInSession()) {
            session()->put($this->getFiltersSessionKey(), $this->filters);
        }
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'md' => 3,
                'xl' => 3,
            ])
            ->components([
                Select::make('window')
                    ->label('Visit window')
                    ->options(VisitAnalyticsSnapshot::windowOptions())
                    ->default(VisitAnalyticsSnapshot::DEFAULT_WINDOW)
                    ->required()
                    ->live(),
                Select::make('response_status')
                    ->label('Response status')
                    ->options(fn (): array => app(VisitAnalyticsSnapshot::class)->responseStatusOptions())
                    ->default(VisitAnalyticsSnapshot::DEFAULT_RESPONSE_STATUS)
                    ->required()
                    ->live(),
                Select::make('visitor_type')
                    ->label('Visit classification')
                    ->options(fn (): array => app(VisitAnalyticsSnapshot::class)->visitorTypeOptions())
                    ->default(VisitAnalyticsSnapshot::DEFAULT_VISITOR_TYPE)
                    ->required()
                    ->live(),
                Grid::make([
                    'md' => 2,
                ])
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Start date'),
                        DatePicker::make('end_date')
                            ->label('End date'),
                    ])
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => $get('window') === VisitAnalyticsSnapshot::WINDOW_CUSTOM),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        if (! static::trackingEnabled()) {
            return $schema->components([
                $this->getWidgetsContentComponent(),
            ]);
        }

        return $schema
            ->components([
                Grid::make([
                    'md' => 4,
                ])
                    ->schema([
                        $this->getFiltersFormContentComponent()
                            ->columnSpan([
                                'md' => 3,
                            ]),
                        View::make('basecms::filament.pages.partials.dashboard-loading-indicator')
                            ->columnSpan([
                                'md' => 1,
                            ]),
                    ]),
                $this->getWidgetsContentComponent(),
            ]);
    }
}

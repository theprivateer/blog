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
use Privateer\Basecms\Services\VisitAnalyticsSnapshot;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

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
                DatePicker::make('start_date')
                    ->label('Start date')
                    ->visible(fn (Get $get): bool => $get('window') === VisitAnalyticsSnapshot::WINDOW_CUSTOM),
                DatePicker::make('end_date')
                    ->label('End date')
                    ->visible(fn (Get $get): bool => $get('window') === VisitAnalyticsSnapshot::WINDOW_CUSTOM),
            ]);
    }

    public function content(Schema $schema): Schema
    {
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

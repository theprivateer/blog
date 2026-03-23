<?php

namespace App\Filament\Widgets;

use App\Services\VisitAnalyticsSnapshot;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopVisitedPaths extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Top visited pages';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => app(VisitAnalyticsSnapshot::class)->topPathsQuery())
            ->columns([
                TextColumn::make('path')
                    ->label('Path')
                    ->formatStateUsing(fn (string $state): string => app(VisitAnalyticsSnapshot::class)->formatPath($state))
                    ->searchable(),
                TextColumn::make('visit_count')
                    ->label('Visits')
                    ->numeric(),
                TextColumn::make('unique_visit_count')
                    ->label('Unique visits')
                    ->numeric(),
            ])
            ->defaultPaginationPageOption(10)
            ->paginated([10])
            ->defaultSort('visit_count', 'desc')
            ->recordUrl(null);
    }
}

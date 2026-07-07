<?php

namespace Privateer\Basecms\Filament\Resources\McpTokens\Tables;

use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Privateer\Basecms\Models\McpToken;

class McpTokensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('abilities_label')
                    ->label('Abilities')
                    ->wrap(),
                TextColumn::make('site.name')
                    ->label('Site')
                    ->placeholder('All sites'),
                TextColumn::make('last_used_at')
                    ->label('Last used')
                    ->since()
                    ->placeholder('Never')
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->placeholder('Never')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                DeleteAction::make()
                    ->label('Revoke')
                    ->modalHeading(fn (McpToken $record): string => "Revoke \"{$record->name}\"")
                    ->modalDescription('This access key will stop working immediately. This cannot be undone.')
                    ->modalSubmitActionLabel('Revoke')
                    ->successNotificationTitle('Access key revoked'),
            ]);
    }
}

<?php

namespace Privateer\Basecms\Filament\Resources\McpTokens\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Privateer\Basecms\Mcp\Support\ContentTypeRegistry;

class McpTokenForm
{
    public static function configure(Schema $schema): Schema
    {
        $abilities = app(ContentTypeRegistry::class)->allAbilities();

        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. Claude agent'),
                CheckboxList::make('abilities')
                    ->options([
                        '*' => 'Full access (all abilities)',
                        ...array_combine($abilities, $abilities),
                    ])
                    ->required()
                    ->columns(2)
                    ->helperText('Selecting "Full access" grants every ability, including any content types registered later.'),
                DateTimePicker::make('expires_at')
                    ->label('Expires at')
                    ->helperText('Leave blank for a key that never expires.'),
                Select::make('site_id')
                    ->label('Restrict to site')
                    ->relationship('site', 'name')
                    ->searchable()
                    ->helperText('Leave blank to allow access to all sites.')
                    ->visible(fn (): bool => (bool) config('basecms.multisite.enabled', false)),
            ]);
    }
}

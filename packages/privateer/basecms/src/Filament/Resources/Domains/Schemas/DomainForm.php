<?php

namespace Privateer\Basecms\Filament\Resources\Domains\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DomainForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('domain')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('example.com')
                    ->maxLength(255),
                Toggle::make('is_primary')
                    ->label('Primary domain'),
            ]);
    }
}

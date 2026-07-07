<?php

namespace Privateer\Basecms\Filament\Resources\McpTokens;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Privateer\Basecms\Filament\Resources\McpTokens\Pages\ManageMcpTokens;
use Privateer\Basecms\Filament\Resources\McpTokens\Schemas\McpTokenForm;
use Privateer\Basecms\Filament\Resources\McpTokens\Tables\McpTokensTable;
use Privateer\Basecms\Models\McpToken;

class McpTokenResource extends Resource
{
    protected static ?string $model = McpToken::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'MCP Access Keys';

    protected static ?string $modelLabel = 'MCP access key';

    protected static ?string $pluralModelLabel = 'MCP access keys';

    public static function form(Schema $schema): Schema
    {
        return McpTokenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return McpTokensTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMcpTokens::route('/'),
        ];
    }
}

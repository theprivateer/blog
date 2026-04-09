<?php

namespace Privateer\Basecms\Filament\Resources\Domains\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Privateer\Basecms\Filament\Resources\Domains\DomainResource;

class ManageDomains extends ManageRecords
{
    protected static string $resource = DomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

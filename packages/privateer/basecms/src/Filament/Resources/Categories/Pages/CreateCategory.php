<?php

namespace Privateer\Basecms\Filament\Resources\Categories\Pages;

use Filament\Resources\Pages\CreateRecord;
use Privateer\Basecms\Filament\Resources\Categories\CategoryResource;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}

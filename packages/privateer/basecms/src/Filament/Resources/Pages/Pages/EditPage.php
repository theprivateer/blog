<?php

namespace Privateer\Basecms\Filament\Resources\Pages\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Privateer\Basecms\Filament\GenerateMetaDescriptionAction;
use Privateer\Basecms\Filament\Resources\Pages\PageResource;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (config('basecms.ai.generate_meta_descriptions.enabled')) {
            $actions[] = GenerateMetaDescriptionAction::make();
        }

        $actions[] = DeleteAction::make();

        return $actions;
    }
}

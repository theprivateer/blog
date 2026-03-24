<?php

namespace Privateer\Basecms\Filament\Resources\Posts\Pages;

use Filament\Resources\Pages\CreateRecord;
use Privateer\Basecms\Filament\Resources\Posts\PostResource;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;
}

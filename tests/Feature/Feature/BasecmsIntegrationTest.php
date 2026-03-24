<?php

namespace Tests\Feature\Feature;

use App\Filament\Resources\Notes\NoteResource;
use Filament\Facades\Filament;
use Tests\TestCase;

class BasecmsIntegrationTest extends TestCase
{
    public function test_package_panel_discovers_app_note_resource(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();

        $this->assertContains(NoteResource::class, Filament::getResources());
    }
}

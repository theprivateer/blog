<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Notes\Pages\CreateNote;
use App\Filament\Resources\Notes\Pages\EditNote;
use App\Filament\Resources\Notes\Pages\ListNotes;
use App\Models\Note;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Models\Site;
use Tests\TestCase;

class NoteResourceTest extends TestCase
{
    use RefreshDatabase;

    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
        config()->set('basecms.multisite.enabled', true);

        $this->site = $this->actingOnTenant($this->makeSite());
        $this->actingAs(User::factory()->create());
    }

    public function test_list_notes_page_loads(): void
    {
        Livewire::test(ListNotes::class)->assertOk();
    }

    public function test_list_notes_displays_records(): void
    {
        $notes = Note::factory()->count(3)->create();

        Livewire::test(ListNotes::class)
            ->assertCanSeeTableRecords($notes);
    }

    public function test_create_note_page_loads(): void
    {
        Livewire::test(CreateNote::class)->assertOk();
    }

    public function test_can_create_note(): void
    {
        Livewire::test(CreateNote::class)
            ->fillForm([
                'title' => 'Test Note',
                'body' => 'Note body',
                'link' => 'https://example.com',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('notes', [
            'title' => 'Test Note',
            'link' => 'https://example.com',
        ]);
    }

    public function test_edit_note_page_loads(): void
    {
        $note = Note::factory()->create();

        Livewire::test(EditNote::class, ['record' => $note->getRouteKey()])
            ->assertOk();
    }

    public function test_can_update_note(): void
    {
        $note = Note::factory()->create();

        Livewire::test(EditNote::class, ['record' => $note->getRouteKey()])
            ->fillForm([
                'title' => 'Updated Note',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => 'Updated Note',
        ]);
    }

    public function test_list_notes_searches_by_title(): void
    {
        $target = Note::factory()->create(['title' => 'Unique Search Note']);
        $other = Note::factory()->create(['title' => 'Something Else']);

        Livewire::test(ListNotes::class)
            ->searchTable('Unique Search Note')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_can_delete_note(): void
    {
        $note = Note::factory()->create();

        Livewire::test(EditNote::class, ['record' => $note->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_list_notes_only_displays_active_tenant_records(): void
    {
        $visibleNote = Note::factory()->create(['title' => 'Visible Note', 'site_id' => $this->site->id]);
        $hiddenNote = Note::factory()->for(Site::factory(), 'site')->create(['title' => 'Hidden Note']);

        Livewire::test(ListNotes::class)
            ->assertCanSeeTableRecords([$visibleNote])
            ->assertCanNotSeeTableRecords([$hiddenNote]);
    }
}

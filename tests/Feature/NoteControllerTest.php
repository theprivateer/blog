<?php

namespace Tests\Feature;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Models\Note;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NoteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);

        Page::factory()->create(['title' => 'Notes', 'slug' => 'notes']);
    }

    public function test_notes_index_returns_ok(): void
    {
        $response = $this->get('/notes');

        $response->assertStatus(200);
        $response->assertViewIs('notes.index');
    }

    public function test_notes_index_displays_notes(): void
    {
        $note = Note::factory()->create(['title' => 'My Note']);

        $response = $this->get('/notes');

        $response->assertSee('My Note');
    }

    public function test_notes_index_paginates_notes(): void
    {
        Note::factory()->count(20)->create();

        $response = $this->get('/notes');
        $response->assertStatus(200);

        $response = $this->get('/notes?page=2');
        $response->assertStatus(200);
    }

    public function test_note_show_returns_ok(): void
    {
        $note = Note::factory()->create(['title' => 'My Note']);

        $response = $this->get('/notes/'.$note->id);

        $response->assertStatus(200);
        $response->assertViewIs('notes.show');
        $response->assertSee('My Note');
    }

    public function test_note_show_returns_404_for_nonexistent(): void
    {
        $response = $this->get('/notes/999');

        $response->assertStatus(404);
    }
}

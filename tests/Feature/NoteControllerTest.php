<?php

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Models\Page;
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

    public function test_notes_are_scoped_to_the_request_domain_when_multisite_is_enabled(): void
    {
        config()->set('basecms.multisite.enabled', true);

        $alphaSite = $this->makeSite('alpha', 'alpha.test');
        $betaSite = $this->makeSite('beta', 'beta.test');

        Page::factory()->create(['site_id' => $alphaSite->id, 'title' => 'Notes', 'slug' => 'notes']);
        Page::factory()->create(['site_id' => $betaSite->id, 'title' => 'Notes', 'slug' => 'notes']);

        Note::factory()->create(['site_id' => $alphaSite->id, 'title' => 'Alpha Note']);
        Note::factory()->create(['site_id' => $betaSite->id, 'title' => 'Beta Note']);

        $this->get('http://alpha.test/notes')
            ->assertOk()
            ->assertSee('Alpha Note')
            ->assertDontSee('Beta Note');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
    }

    public function test_posts_atom_feed_returns_ok(): void
    {
        $response = $this->get('/feed/posts/atom');

        $response->assertStatus(200);
    }

    public function test_posts_rss_feed_returns_ok(): void
    {
        $response = $this->get('/feed/posts/rss');

        $response->assertStatus(200);
    }

    public function test_posts_json_feed_returns_ok(): void
    {
        $response = $this->get('/feed/posts/json');

        $response->assertStatus(200);
    }

    public function test_notes_atom_feed_returns_ok(): void
    {
        $response = $this->get('/feed/notes/atom');

        $response->assertStatus(200);
    }

    public function test_notes_rss_feed_returns_ok(): void
    {
        $response = $this->get('/feed/notes/rss');

        $response->assertStatus(200);
    }

    public function test_notes_json_feed_returns_ok(): void
    {
        $response = $this->get('/feed/notes/json');

        $response->assertStatus(200);
    }

    public function test_posts_feed_contains_published_posts(): void
    {
        $post = Post::factory()->published()->create(['title' => 'Visible Post']);

        $response = $this->get('/feed/posts/atom');

        $response->assertSee('Visible Post');
    }

    public function test_posts_feed_excludes_unpublished_posts(): void
    {
        $post = Post::factory()->unpublished()->create(['title' => 'Hidden Post']);

        $response = $this->get('/feed/posts/atom');

        $response->assertDontSee('Hidden Post');
    }

    public function test_notes_feed_contains_notes(): void
    {
        $note = Note::factory()->create(['title' => 'Visible Note']);

        $response = $this->get('/feed/notes/atom');

        $response->assertSee('Visible Note');
    }
}

<?php

namespace Tests\Unit\Models;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RendersBodyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
    }

    public function test_render_converts_markdown_to_html(): void
    {
        $post = Post::factory()->create(['body' => '## Hello World']);

        $html = $post->render();

        $this->assertStringContainsString('<h2', $html);
        $this->assertStringContainsString('Hello World', $html);
    }

    public function test_render_handles_empty_body(): void
    {
        $post = Post::factory()->create(['body' => '']);

        $html = $post->render();

        $this->assertIsString($html);
    }
}

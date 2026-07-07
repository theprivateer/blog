<?php

namespace Tests\Feature\Mcp;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Mcp\BasecmsMcpServer;
use Privateer\Basecms\Mcp\Support\McpAccess;
use Privateer\Basecms\Mcp\Tools\CreateContentTool;
use Privateer\Basecms\Mcp\Tools\DeleteContentTool;
use Privateer\Basecms\Mcp\Tools\ListContentTool;
use Privateer\Basecms\Mcp\Tools\ReadContentTool;
use Privateer\Basecms\Mcp\Tools\UpdateContentTool;
use Privateer\Basecms\Models\Metadata;
use Privateer\Basecms\Models\Post;
use Tests\TestCase;

class ContentToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Flat-file backup is enabled via .env locally; fake these events so writes/deletes
        // in this suite don't hit the filesystem or require a homepage page for the sitemap.
        Event::fake([PostSaved::class, PostDeleted::class]);
    }

    public function test_list_content_tool_lists_registered_type(): void
    {
        Post::factory()->count(3)->create();

        $response = BasecmsMcpServer::tool(ListContentTool::class, ['type' => 'posts']);

        $response->assertOk();
        $response->assertStructuredContent(fn ($json) => $json
            ->has('items', 3)
            ->where('pagination.total', 3)
            ->etc());
    }

    public function test_list_content_tool_supports_search(): void
    {
        Post::factory()->create(['title' => 'Findable Title']);
        Post::factory()->create(['title' => 'Something Else']);

        $response = BasecmsMcpServer::tool(ListContentTool::class, [
            'type' => 'posts',
            'search' => 'Findable',
        ]);

        $response->assertOk()->assertSee('Findable Title')->assertDontSee('Something Else');
    }

    public function test_list_content_tool_errors_for_unknown_type(): void
    {
        $response = BasecmsMcpServer::tool(ListContentTool::class, ['type' => 'videos']);

        $response->assertHasErrors();
    }

    public function test_list_content_tool_denies_without_ability(): void
    {
        // Grants a read ability for a different type so the tool stays registered
        // (shouldRegister() only hides it when no read ability at all is present),
        // letting the per-type check inside handle() be exercised.
        app()->instance(McpAccess::class, new McpAccess(['pages:read']));

        Post::factory()->create();

        $response = BasecmsMcpServer::tool(ListContentTool::class, ['type' => 'posts']);

        $response->assertHasErrors(['posts:read']);
    }

    public function test_read_content_tool_finds_record_by_slug(): void
    {
        $post = Post::factory()->create(['title' => 'Readable Post']);

        $response = BasecmsMcpServer::tool(ReadContentTool::class, [
            'type' => 'posts',
            'id_or_slug' => $post->slug,
        ]);

        $response->assertOk()->assertSee('Readable Post');
    }

    public function test_read_content_tool_finds_record_by_id(): void
    {
        $post = Post::factory()->create();

        $response = BasecmsMcpServer::tool(ReadContentTool::class, [
            'type' => 'posts',
            'id_or_slug' => (string) $post->id,
        ]);

        $response->assertOk();
    }

    public function test_read_content_tool_returns_error_when_not_found(): void
    {
        $response = BasecmsMcpServer::tool(ReadContentTool::class, [
            'type' => 'posts',
            'id_or_slug' => 'does-not-exist',
        ]);

        $response->assertHasErrors();
    }

    public function test_read_content_tool_includes_rendered_body(): void
    {
        $post = Post::factory()->create(['body' => 'Hello **world**.']);

        $response = BasecmsMcpServer::tool(ReadContentTool::class, [
            'type' => 'posts',
            'id_or_slug' => $post->slug,
        ]);

        $response->assertOk()->assertSee('<strong>world</strong>');
    }

    public function test_read_content_tool_includes_metadata_when_supported(): void
    {
        $post = Post::factory()->create();
        Metadata::factory()->create([
            'parent_type' => Post::class,
            'parent_id' => $post->id,
            'title' => 'SEO Title',
        ]);

        $response = BasecmsMcpServer::tool(ReadContentTool::class, [
            'type' => 'posts',
            'id_or_slug' => $post->slug,
        ]);

        $response->assertOk()->assertSee('SEO Title');
    }

    public function test_create_content_tool_creates_record_with_whitelisted_fields(): void
    {
        $response = BasecmsMcpServer::tool(CreateContentTool::class, [
            'type' => 'posts',
            'fields' => ['title' => 'Created Post', 'body' => 'Body text'],
        ]);

        $response->assertOk()->assertSee('Created Post');
        $this->assertDatabaseHas('posts', ['title' => 'Created Post']);
    }

    public function test_create_content_tool_ignores_non_writable_fields(): void
    {
        $response = BasecmsMcpServer::tool(CreateContentTool::class, [
            'type' => 'posts',
            'fields' => ['title' => 'Created Post', 'site_id' => 999999],
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('posts', ['site_id' => 999999]);
    }

    public function test_create_content_tool_errors_when_no_writable_fields_given(): void
    {
        $response = BasecmsMcpServer::tool(CreateContentTool::class, [
            'type' => 'posts',
            'fields' => ['unknown_field' => 'value'],
        ]);

        $response->assertHasErrors();
    }

    public function test_create_content_tool_denies_without_ability(): void
    {
        app()->instance(McpAccess::class, new McpAccess(['pages:write']));

        $response = BasecmsMcpServer::tool(CreateContentTool::class, [
            'type' => 'posts',
            'fields' => ['title' => 'Nope'],
        ]);

        $response->assertHasErrors(['posts:write']);
        $this->assertDatabaseMissing('posts', ['title' => 'Nope']);
    }

    public function test_create_content_tool_dispatches_post_saved_event(): void
    {
        BasecmsMcpServer::tool(CreateContentTool::class, [
            'type' => 'posts',
            'fields' => ['title' => 'Event Post', 'body' => 'Body'],
        ])->assertOk();

        Event::assertDispatched(PostSaved::class, fn (PostSaved $event): bool => $event->record->title === 'Event Post');
    }

    public function test_update_content_tool_updates_existing_record(): void
    {
        $post = Post::factory()->create(['title' => 'Old Title']);

        $response = BasecmsMcpServer::tool(UpdateContentTool::class, [
            'type' => 'posts',
            'id_or_slug' => $post->slug,
            'fields' => ['title' => 'New Title'],
        ]);

        $response->assertOk();
        $this->assertSame('New Title', $post->fresh()->title);
    }

    public function test_update_content_tool_errors_when_record_not_found(): void
    {
        $response = BasecmsMcpServer::tool(UpdateContentTool::class, [
            'type' => 'posts',
            'id_or_slug' => 'missing',
            'fields' => ['title' => 'New Title'],
        ]);

        $response->assertHasErrors();
    }

    public function test_delete_content_tool_deletes_record(): void
    {
        $post = Post::factory()->create();

        $response = BasecmsMcpServer::tool(DeleteContentTool::class, [
            'type' => 'posts',
            'id_or_slug' => $post->slug,
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_delete_content_tool_dispatches_post_deleted_event(): void
    {
        $post = Post::factory()->create();

        BasecmsMcpServer::tool(DeleteContentTool::class, [
            'type' => 'posts',
            'id_or_slug' => $post->slug,
        ])->assertOk();

        Event::assertDispatched(PostDeleted::class, fn (PostDeleted $event): bool => $event->record->id === $post->id);
    }

    public function test_delete_content_tool_denies_without_ability(): void
    {
        app()->instance(McpAccess::class, new McpAccess(['pages:delete']));

        $post = Post::factory()->create();

        $response = BasecmsMcpServer::tool(DeleteContentTool::class, [
            'type' => 'posts',
            'id_or_slug' => $post->slug,
        ]);

        $response->assertHasErrors(['posts:delete']);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_content_tools_work_for_the_app_owned_note_type(): void
    {
        $createResponse = BasecmsMcpServer::tool(CreateContentTool::class, [
            'type' => 'notes',
            'fields' => ['title' => 'A note', 'body' => 'Note body'],
        ]);

        $createResponse->assertOk();
        $note = Note::query()->where('title', 'A note')->firstOrFail();

        $readResponse = BasecmsMcpServer::tool(ReadContentTool::class, [
            'type' => 'notes',
            'id_or_slug' => $note->slug,
        ]);
        $readResponse->assertOk()->assertSee('A note');

        $deleteResponse = BasecmsMcpServer::tool(DeleteContentTool::class, [
            'type' => 'notes',
            'id_or_slug' => $note->slug,
        ]);
        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }
}

<?php

namespace Tests\Feature\Mcp;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Privateer\Basecms\Mcp\Support\ContentTypeRegistry;
use Privateer\Basecms\Models\Post;
use Tests\TestCase;

class ContentTypeRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_keys_includes_package_and_app_registered_types(): void
    {
        $registry = app(ContentTypeRegistry::class);

        $this->assertEqualsCanonicalizing(
            ['posts', 'pages', 'categories', 'notes'],
            $registry->keys(),
        );
    }

    public function test_has_returns_true_for_registered_type_and_false_otherwise(): void
    {
        $registry = app(ContentTypeRegistry::class);

        $this->assertTrue($registry->has('posts'));
        $this->assertFalse($registry->has('videos'));
    }

    public function test_model_for_resolves_app_owned_note_model(): void
    {
        $registry = app(ContentTypeRegistry::class);

        $this->assertSame(Post::class, $registry->modelFor('posts'));
        $this->assertSame(Note::class, $registry->modelFor('notes'));
    }

    public function test_get_or_fail_throws_for_unknown_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(ContentTypeRegistry::class)->getOrFail('videos');
    }

    public function test_writable_fields_for_excludes_site_id(): void
    {
        $registry = app(ContentTypeRegistry::class);

        $fields = $registry->writableFieldsFor('posts');

        $this->assertNotContains('site_id', $fields);
        $this->assertContains('title', $fields);
        $this->assertContains('body', $fields);
    }

    public function test_supports_metadata_for_is_true_for_posts_and_false_for_notes(): void
    {
        $registry = app(ContentTypeRegistry::class);

        $this->assertTrue($registry->supportsMetadataFor('posts'));
        $this->assertFalse($registry->supportsMetadataFor('notes'));
    }

    public function test_abilities_for_returns_read_write_delete(): void
    {
        $registry = app(ContentTypeRegistry::class);

        $this->assertSame(
            ['posts:read', 'posts:write', 'posts:delete'],
            $registry->abilitiesFor('posts'),
        );
    }

    public function test_all_abilities_includes_analytics_read(): void
    {
        $registry = app(ContentTypeRegistry::class);

        $this->assertContains('analytics:read', $registry->allAbilities());
        $this->assertContains('notes:write', $registry->allAbilities());
    }
}

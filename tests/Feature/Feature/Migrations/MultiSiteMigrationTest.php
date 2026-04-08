<?php

namespace Tests\Feature\Feature\Migrations;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Domain;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Models\Site;
use Tests\TestCase;

class MultiSiteMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
    }

    public function test_sites_and_domains_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('sites'));
        $this->assertTrue(Schema::hasTable('domains'));
    }

    public function test_posts_pages_and_categories_allow_duplicate_slugs_across_sites(): void
    {
        $alphaSite = Site::factory()->create();
        $betaSite = Site::factory()->create();

        Post::factory()->create(['site_id' => $alphaSite->id, 'slug' => 'shared-slug']);
        Post::factory()->create(['site_id' => $betaSite->id, 'slug' => 'shared-slug']);

        Page::factory()->create(['site_id' => $alphaSite->id, 'slug' => 'shared-page']);
        Page::factory()->create(['site_id' => $betaSite->id, 'slug' => 'shared-page']);

        Category::factory()->create(['site_id' => $alphaSite->id, 'slug' => 'shared-category']);
        Category::factory()->create(['site_id' => $betaSite->id, 'slug' => 'shared-category']);

        $this->assertDatabaseCount('posts', 2);
        $this->assertDatabaseCount('pages', 2);
        $this->assertDatabaseCount('categories', 2);
    }

    public function test_domains_must_be_globally_unique(): void
    {
        $domain = 'shared.test';

        Domain::factory()->create(['domain' => $domain]);

        $this->expectException(QueryException::class);

        Domain::factory()->create(['domain' => $domain]);
    }
}

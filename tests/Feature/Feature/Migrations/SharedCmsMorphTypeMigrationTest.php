<?php

namespace Tests\Feature\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Tests\TestCase;

class SharedCmsMorphTypeMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_migrates_legacy_app_model_morph_types_to_package_models(): void
    {
        $post = Post::unguarded(fn (): Post => Post::createQuietly([
            'title' => 'Legacy Post',
            'slug' => 'legacy-post',
            'intro' => 'Intro',
            'body' => 'Body',
            'published_at' => now()->subDay(),
        ]));

        $page = Page::unguarded(fn (): Page => Page::createQuietly([
            'title' => 'Legacy Page',
            'slug' => 'legacy-page',
            'body' => 'Body',
        ]));

        DB::table('metadata')->insert([
            'title' => 'Legacy Meta',
            'description' => 'Legacy Desc',
            'parent_type' => 'App\\Models\\Post',
            'parent_id' => $post->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('assets')->insert([
            'disk' => 's3',
            'path' => 'attachments/legacy.png',
            'directory' => 'attachments',
            'filename' => 'legacy.png',
            'mime_type' => 'image/png',
            'size' => 1024,
            'visibility' => 'public',
            'url' => 'https://files.example.test/attachments/legacy.png',
            'field' => 'body',
            'uploaded_by' => null,
            'attachable_type' => 'App\\Models\\Page',
            'attachable_id' => $page->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_03_24_194422_migrate_shared_cms_morph_types_to_package_models.php');
        $migration->up();

        $this->assertDatabaseHas('metadata', [
            'parent_type' => Post::class,
            'parent_id' => $post->id,
        ]);

        $this->assertDatabaseHas('assets', [
            'attachable_type' => Page::class,
            'attachable_id' => $page->id,
        ]);
    }

    public function test_down_restores_package_model_morph_types_to_legacy_app_models(): void
    {
        $post = Post::unguarded(fn (): Post => Post::createQuietly([
            'title' => 'Package Post',
            'slug' => 'package-post',
            'intro' => 'Intro',
            'body' => 'Body',
            'published_at' => now()->subDay(),
        ]));

        $page = Page::unguarded(fn (): Page => Page::createQuietly([
            'title' => 'Package Page',
            'slug' => 'package-page',
            'body' => 'Body',
        ]));

        DB::table('metadata')->insert([
            'title' => 'Package Meta',
            'description' => 'Package Desc',
            'parent_type' => Post::class,
            'parent_id' => $post->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('assets')->insert([
            'disk' => 's3',
            'path' => 'attachments/package.png',
            'directory' => 'attachments',
            'filename' => 'package.png',
            'mime_type' => 'image/png',
            'size' => 1024,
            'visibility' => 'public',
            'url' => 'https://files.example.test/attachments/package.png',
            'field' => 'body',
            'uploaded_by' => null,
            'attachable_type' => Page::class,
            'attachable_id' => $page->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_03_24_194422_migrate_shared_cms_morph_types_to_package_models.php');
        $migration->down();

        $this->assertDatabaseHas('metadata', [
            'parent_type' => 'App\\Models\\Post',
            'parent_id' => $post->id,
        ]);

        $this->assertDatabaseHas('assets', [
            'attachable_type' => 'App\\Models\\Page',
            'attachable_id' => $page->id,
        ]);
    }
}

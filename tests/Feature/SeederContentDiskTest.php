<?php

namespace Tests\Feature;

use App\Models\Note;
use Database\Seeders\CategorySeeder;
use Database\Seeders\NoteSeeder;
use Database\Seeders\PageSeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Models\Site;
use Tests\TestCase;

class SeederContentDiskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);

        Storage::fake('content');
        Storage::fake('users');
        Storage::fake('categories');
        Storage::fake('pages');
        Storage::fake('posts');
        Storage::fake('notes');
    }

    public function test_user_seeder_reads_from_the_content_disk_users_directory(): void
    {
        Storage::disk('content')->put('users/jane.md', <<<'MD'
---
id: 42
name: Jane Doe
email: jane@example.com
password: $2y$12$abcdefghijklmnopqrstuv
email_verified_at: 2026-04-09 10:00:00
created_at: 2026-04-09 10:00:00
updated_at: 2026-04-09 10:00:00
---

MD);

        $this->seed(UserSeeder::class);

        $this->assertDatabaseHas('users', [
            'id' => 42,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_content_seeders_read_site_scoped_content_from_the_content_disk(): void
    {
        Storage::disk('content')->put('alpha/categories/laravel.md', <<<'MD'
---
id: 100
title: Laravel
created_at: 2026-04-09 10:00:00
updated_at: 2026-04-09 10:00:00
metadata:
  title: Laravel SEO
  description: Laravel category description
---
Posts about Laravel.
MD);

        Storage::disk('content')->put('alpha/pages/home.md', <<<'MD'
---
title: Home
created_at: 2026-04-09 10:00:00
updated_at: 2026-04-09 10:00:00
---
Homepage content.
MD);

        Storage::disk('content')->put('alpha/posts/2026-04-09T10:00:00+00:00.launch-post.md', <<<'MD'
---
title: Launch Post
intro: Launch intro
category_id: 100
created_at: 2026-04-09 10:00:00
updated_at: 2026-04-09 10:00:00
metadata:
  title: Launch SEO
  description: Launch description
---
Launch post body.
MD);

        Storage::disk('content')->put('beta/notes/2026-04-09T10:00:00+00:00.quick-note.md', <<<'MD'
---
title: Quick Note
link: https://example.com/note
created_at: 2026-04-09 10:00:00
updated_at: 2026-04-09 10:00:00
---
Quick note body.
MD);

        $this->seed([
            CategorySeeder::class,
            PageSeeder::class,
            PostSeeder::class,
            NoteSeeder::class,
        ]);

        $alphaSite = Site::query()->where('key', 'alpha')->first();
        $betaSite = Site::query()->where('key', 'beta')->first();

        $this->assertNotNull($alphaSite);
        $this->assertNotNull($betaSite);

        $this->assertDatabaseHas('categories', [
            'site_id' => $alphaSite->id,
            'title' => 'Laravel',
            'slug' => 'laravel',
            'filename' => 'alpha/categories/laravel.md',
        ]);

        $this->assertDatabaseHas('pages', [
            'site_id' => $alphaSite->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'filename' => 'alpha/pages/home.md',
        ]);

        $this->assertDatabaseHas('posts', [
            'site_id' => $alphaSite->id,
            'title' => 'Launch Post',
            'slug' => 'launch-post',
            'category_id' => 100,
            'filename' => 'alpha/posts/2026-04-09T10:00:00+00:00.launch-post.md',
        ]);

        $this->assertDatabaseHas('notes', [
            'site_id' => $betaSite->id,
            'title' => 'Quick Note',
            'slug' => 'quick-note',
            'filename' => 'beta/notes/2026-04-09T10:00:00+00:00.quick-note.md',
        ]);

        $this->assertSame('Laravel category description', Category::query()->firstOrFail()->metadata?->description);
        $this->assertSame('Launch description', Post::query()->firstOrFail()->metadata?->description);
        $this->assertSame('https://example.com/note', Note::query()->firstOrFail()->link);
    }

    public function test_seeders_do_not_require_the_legacy_type_specific_disks(): void
    {
        Storage::disk('content')->put('default/pages/home.md', <<<'MD'
---
title: Home
created_at: 2026-04-09 10:00:00
updated_at: 2026-04-09 10:00:00
---
Homepage content.
MD);

        $this->assertSame([], Storage::disk('pages')->allFiles());

        $this->seed(PageSeeder::class);

        $this->assertDatabaseHas('pages', [
            'title' => 'Home',
            'filename' => 'default/pages/home.md',
        ]);
    }
}

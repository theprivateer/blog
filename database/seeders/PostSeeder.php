<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Privateer\Basecms\Models\Metadata;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Services\SiteManager;
use Privateer\Basecms\Support\Files;
use Webuni\FrontMatter\FrontMatterChain;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frontMatter = FrontMatterChain::create();
        $disk = Storage::disk('content');

        $files = collect($disk->allFiles())
            ->filter(fn (string $filename): bool => $this->isForType($filename, 'posts'))
            ->values()
            ->all();

        foreach ($files as $filename) {
            if (in_array(basename($filename), Files::SKIPPABLE)) {
                continue;
            }

            $document = $frontMatter->parse(
                $disk->get($filename)
            );

            $data = $document->getData();
            $parts = explode('.', basename($filename));
            $site = $this->siteForFilename($filename);

            $post = Post::createQuietly([
                'site_id' => $site->id,
                'title' => $data['title'],
                'slug' => $parts[1],
                'body' => $document->getContent(),
                'intro' => $data['intro'] ?? null,
                'published_at' => Carbon::parse($parts[0]),
                'category_id' => $data['category_id'] ?? null,
                'filename' => $filename,
                'created_at' => $data['created_at'] ?? Carbon::parse($parts[0]),
                'updated_at' => $data['updated_at'] ?? Carbon::parse($parts[0]),
            ]);

            $post->metadata()->save(Metadata::make($data['metadata'] ?? []));
        }
    }

    protected function siteForFilename(string $filename): Site
    {
        $siteKey = explode('/', ltrim($filename, '/'))[0] ?? 'default';

        return Site::query()->firstOrCreate(
            ['key' => $siteKey],
            ['name' => app(SiteManager::class)->makeSiteNameFromKey($siteKey)],
        );
    }

    protected function isForType(string $filename, string $type): bool
    {
        $segments = explode('/', ltrim($filename, '/'));

        return ($segments[1] ?? null) === $type;
    }
}

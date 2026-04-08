<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Privateer\Basecms\Models\Metadata;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Services\SiteManager;
use Webuni\FrontMatter\FrontMatterChain;
use Privateer\Basecms\Support\Files;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frontMatter = FrontMatterChain::create();

        $files = collect(Storage::disk('pages')->allFiles())
            ->filter(fn (string $filename): bool => $this->isForType($filename, 'pages'))
            ->values()
            ->all();

        foreach ($files as $filename) {
            if (in_array($filename, Files::SKIPPABLE)) {
                continue;
            }

            $document = $frontMatter->parse(
                Storage::disk('pages')->get($filename)
            );

            $data = $document->getData();
            $parts = explode('.', basename($filename));
            $site = $this->siteForFilename($filename);

            $page = Page::createQuietly([
                'site_id' => $site->id,
                'title' => $data['title'],
                'slug' => $parts[0],
                'body' => $document->getContent(),
                'is_homepage' => ($parts[0] === 'home') ? true : false,
                'draft' => $data['draft'] ?? false,
                'template' => $data['template'] ?? null,
                'filename' => $filename,
                'created_at' => $data['created_at'] ?? now(),
                'updated_at' => $data['updated_at'] ?? now(),
            ]);

            $page->metadata()->save(Metadata::make($data['metadata'] ?? []));
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

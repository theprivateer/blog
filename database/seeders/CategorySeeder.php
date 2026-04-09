<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Metadata;
use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Services\SiteManager;
use Privateer\Basecms\Support\Files;
use Webuni\FrontMatter\FrontMatterChain;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frontMatter = FrontMatterChain::create();
        $disk = Storage::disk('content');

        $files = collect($disk->allFiles())
            ->filter(fn (string $filename): bool => $this->isForType($filename, 'categories'))
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

            $category = Category::createQuietly([
                'id' => $data['id'],
                'site_id' => $site->id,
                'title' => $data['title'],
                'slug' => $parts[0],
                'body' => $document->getContent(),
                'filename' => $filename,
                'created_at' => $data['created_at'] ?? now(),
                'updated_at' => $data['updated_at'] ?? now(),
            ]);

            $category->metadata()->save(Metadata::make($data['metadata'] ?? []));
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

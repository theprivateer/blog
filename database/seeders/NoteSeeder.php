<?php

namespace Database\Seeders;

use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Services\SiteManager;
use Webuni\FrontMatter\FrontMatterChain;
use Privateer\Basecms\Support\Files;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frontMatter = FrontMatterChain::create();

        $files = collect(Storage::disk('notes')->allFiles())
            ->filter(fn (string $filename): bool => $this->isForType($filename, 'notes'))
            ->values()
            ->all();

        foreach ($files as $filename) {
            if (in_array($filename, Files::SKIPPABLE)) {
                continue;
            }

            $document = $frontMatter->parse(
                Storage::disk('notes')->get($filename)
            );

            $data = $document->getData();
            $parts = explode('.', basename($filename));
            $site = $this->siteForFilename($filename);

            Note::createQuietly([
                'site_id' => $site->id,
                'title' => $data['title'] ?? null,
                'slug' => $parts[1],
                'link' => $data['link'] ?? null,
                'body' => $document->getContent(),
                'created_at' => $data['created_at'] ?? Carbon::parse($parts[0]),
                'updated_at' => $data['updated_at'] ?? Carbon::parse($parts[0]),
                'filename' => $filename,
            ]);
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

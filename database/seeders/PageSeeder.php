<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frontMatter = \Webuni\FrontMatter\FrontMatterChain::create();

        $files = Storage::disk('pages')->files();

        foreach ($files as $filename) {
            if ($filename === '.gitkeep') {
                continue;
            }

            $document = $frontMatter->parse(
                Storage::disk('pages')->get($filename)
            );

            $data = $document->getData();
            $parts = explode('.', $filename);

            Page::createQuietly([
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
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Metadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frontMatter = \Webuni\FrontMatter\FrontMatterChain::create();

        $files = Storage::disk('categories')->files();

        foreach ($files as $filename) {
            if ($filename === '.gitkeep') {
                continue;
            }

            $document = $frontMatter->parse(
                Storage::disk('categories')->get($filename)
            );

            $data = $document->getData();
            $parts = explode('.', $filename);

            $category = Category::createQuietly([
                'id' => $data['id'],
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
}

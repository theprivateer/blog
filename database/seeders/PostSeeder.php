<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Privateer\Basecms\Models\Metadata;
use Privateer\Basecms\Models\Post;
use Webuni\FrontMatter\FrontMatterChain;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frontMatter = FrontMatterChain::create();

        $files = Storage::disk('posts')->files();

        foreach ($files as $filename) {
            if ($filename === '.gitkeep') {
                continue;
            }

            $document = $frontMatter->parse(
                Storage::disk('posts')->get($filename)
            );

            $data = $document->getData();
            $parts = explode('.', $filename);

            $post = Post::createQuietly([
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
}

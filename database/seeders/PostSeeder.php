<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frontMatter = \Webuni\FrontMatter\FrontMatterChain::create();

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

            Post::createQuietly([
                'title' => $data['title'],
                'slug' => $parts[1],
                'body' => $document->getContent(),
                'published_at' => Carbon::parse($parts[0]),
                'filename' => $filename,
                'created_at' => $data['created_at'] ?? Carbon::parse($parts[0]),
                'updated_at' => $data['updated_at'] ?? Carbon::parse($parts[0]),
            ]);
        }
    }
}

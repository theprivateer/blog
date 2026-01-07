<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Note;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frontMatter = \Webuni\FrontMatter\FrontMatterChain::create();

        $files = Storage::disk('notes')->files();

        foreach ($files as $filename) {
            if ($filename === '.gitkeep') {
                continue;
            }

            $document = $frontMatter->parse(
                Storage::disk('notes')->get($filename)
            );

            $data = $document->getData();
            $parts = explode('.', $filename);

            Note::createQuietly([
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
}

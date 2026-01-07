<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Moment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class MomentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frontMatter = \Webuni\FrontMatter\FrontMatterChain::create();

        $files = Storage::disk('moments')->files();

        foreach ($files as $filename) {
            if ($filename === '.gitkeep') {
                continue;
            }

            $document = $frontMatter->parse(
                Storage::disk('moments')->get($filename)
            );

            $data = $document->getData();

            $parts = explode('.', $filename);

            Moment::createQuietly([
                'body' => $document->getContent(),
                'created_at' => $data['created_at'] ?? Carbon::parse($parts[0]),
                'updated_at' => $data['updated_at'] ?? Carbon::parse($parts[0]),
                'filename' => $filename,
            ]);
        }
    }
}

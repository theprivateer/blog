<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Privateer\Basecms\Support\Files;
use Webuni\FrontMatter\FrontMatterChain;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frontMatter = FrontMatterChain::create();
        $disk = Storage::disk('content');

        $files = $disk->files('users');

        foreach ($files as $filename) {
            if (in_array(basename($filename), Files::SKIPPABLE)) {
                continue;
            }

            $document = $frontMatter->parse(
                $disk->get($filename)
            );

            $data = $document->getData();

            if (! User::where('email', $data['email'])->first()) {
                User::createQuietly([
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'email_verified_at' => $data['email_verified_at'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at'],
                ]);
            }
        }
    }
}

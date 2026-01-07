<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frontMatter = \Webuni\FrontMatter\FrontMatterChain::create();

        $files = Storage::disk('users')->files();

        foreach ($files as $filename) {
            if ($filename === '.gitkeep') {
                continue;
            }

            $document = $frontMatter->parse(
                Storage::disk('users')->get($filename)
            );

            $data = $document->getData();

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

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Privateer\Basecms\Models\Site;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        Site::query()->firstOrCreate(
            ['key' => 'default'],
            ['name' => 'Default Site'],
        );
    }
}

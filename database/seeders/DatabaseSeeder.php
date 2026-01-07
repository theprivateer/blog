<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => env('SEED_USER_NAME', 'Test User'),
            'email' => env('SEED_USER_EMAIL', 'test@example.com'),
            'password' => env('SEED_USER_PASSWORD', 'password'),
        ]);

        $this->call([
            PageSeeder::class,
            PostSeeder::class,
            NoteSeeder::class,
            MomentSeeder::class,
        ]);
    }
}

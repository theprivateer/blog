<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Note;
use App\Models\Page;
use App\Models\Post;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class ReSeedContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:re-seed-content';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncates content tables and re-seeds from Markdown files';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        foreach ([Category::class, Page::class, Post::class, Note::class] as $model) {
            $model::query()->truncate();
        }

        Model::unguard();
        resolve(DatabaseSeeder::class)->run();
    }
}

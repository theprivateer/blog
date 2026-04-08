<?php

namespace App\Console\Commands;

use App\Models\Note;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Domain;
use Privateer\Basecms\Models\Metadata;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Models\Site;

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
        Schema::disableForeignKeyConstraints();

        foreach ([Metadata::class, Note::class, Post::class, Page::class, Category::class, Domain::class, Site::class] as $model) {
            $model::query()->truncate();
        }

        Schema::enableForeignKeyConstraints();

        Model::unguard();
        resolve(DatabaseSeeder::class)->run();
    }
}

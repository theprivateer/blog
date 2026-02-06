<?php

namespace App\Console\Commands;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
    public function handle()
    {
        foreach ([
            // 'users',
            'categories',
            'pages',
            'posts',
            'notes',
            'moments',
        ] as $table) {
            DB::table($table)->truncate();
        }

        Model::unguard();
        resolve(DatabaseSeeder::class)->run();
    }
}

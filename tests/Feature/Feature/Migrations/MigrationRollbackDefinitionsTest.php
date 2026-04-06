<?php

namespace Tests\Feature\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationRollbackDefinitionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_column_migration_down_removes_the_column(): void
    {
        $this->assertTrue(Schema::hasColumn('posts', 'category_id'));

        $migration = require database_path('migrations/2026_02_06_153208_add_category_column_to_posts_table.php');
        $migration->down();

        $this->assertFalse(Schema::hasColumn('posts', 'category_id'));
    }

    public function test_drop_moments_migration_down_recreates_the_table(): void
    {
        $this->assertFalse(Schema::hasTable('moments'));

        $migration = require database_path('migrations/2026_03_23_220428_drop_moments_table.php');
        $migration->down();

        $this->assertTrue(Schema::hasTable('moments'));
        $this->assertTrue(Schema::hasColumns('moments', [
            'id',
            'body',
            'filename',
            'created_at',
            'updated_at',
        ]));
    }
}

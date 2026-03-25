<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->migrateMorphTypes([
            'App\\Models\\Post' => 'Privateer\\Basecms\\Models\\Post',
            'App\\Models\\Page' => 'Privateer\\Basecms\\Models\\Page',
            'App\\Models\\Category' => 'Privateer\\Basecms\\Models\\Category',
            'App\\Models\\Metadata' => 'Privateer\\Basecms\\Models\\Metadata',
            'App\\Models\\Asset' => 'Privateer\\Basecms\\Models\\Asset',
            'App\\Models\\Visit' => 'Privateer\\Basecms\\Models\\Visit',
        ]);
    }

    /**
     * @param  array<string, string>  $morphTypes
     */
    protected function migrateMorphTypes(array $morphTypes): void
    {
        foreach ($morphTypes as $from => $to) {
            DB::table('metadata')
                ->where('parent_type', $from)
                ->update(['parent_type' => $to]);

            DB::table('assets')
                ->where('attachable_type', $from)
                ->update(['attachable_type' => $to]);
        }
    }
};

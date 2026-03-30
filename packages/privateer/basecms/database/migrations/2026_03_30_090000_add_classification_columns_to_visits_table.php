<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->string('visitor_type')->nullable()->after('user_agent');
            $table->string('visitor_label')->nullable()->after('visitor_type');
            $table->string('classification_source')->nullable()->after('visitor_label');

            $table->index('visitor_type');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex(['visitor_type']);
            $table->dropColumn([
                'visitor_type',
                'visitor_label',
                'classification_source',
            ]);
        });
    }
};

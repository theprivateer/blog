<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->string('title');
            $table->longText('body')->nullable();
            $table->boolean('use_builder')->default(false);
            $table->json('blocks')->nullable();
            $table->boolean('is_homepage')->default(false);
            $table->boolean('draft')->default(false);
            $table->string('template')->nullable();
            $table->string('filename')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};

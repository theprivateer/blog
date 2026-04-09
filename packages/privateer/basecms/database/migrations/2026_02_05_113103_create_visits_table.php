<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('method');
            $table->string('ip_address');
            $table->string('session_id');
            $table->string('user_agent');
            $table->unsignedSmallInteger('response_status');
            $table->string('visitor_type')->nullable();
            $table->string('visitor_label')->nullable();
            $table->string('classification_source')->nullable();
            $table->timestamps();

            $table->index('visitor_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};

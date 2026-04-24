<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
        
                $table->foreignId('lesson_id')
          ->constrained()
          ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type'); // pdf, doc, video, etc.
            $table->integer('file_size')->nullable(); // in bytes
            $table->string('thumbnail_url')->nullable();
            $table->integer('download_count')->default(0);
            $table->boolean('is_downloadable')->default(true);
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
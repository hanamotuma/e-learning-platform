<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonsTable extends Migration
{
    public function up(): void
    {
         Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('video_url')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_free')->default(false);
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->integer('order_position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
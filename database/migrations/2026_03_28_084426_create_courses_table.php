<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
   public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('what_you_will_learn')->nullable();
            $table->text('requirements')->nullable();
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();

            $table->decimal('price', 10, 2)->default(0);
            $table->foreignId('category_id')->constrained();
            $table->foreignId('instructor_id')->constrained('users');
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->integer('duration_weeks')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('courses');

    }
};
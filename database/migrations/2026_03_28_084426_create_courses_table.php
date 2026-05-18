<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('requirements')->nullable();
            $table->text('what_you_will_learn')->nullable();
            $table->text('instructor_bio')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('instructor_id');

            $table->foreign('instructor_id')->references('user_id')->on('users');
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2);
            $table->float('rating')->default(0);
            $table->integer('reviews')->default(0);
            $table->integer('students')->default(0);
            $table->integer('hours');
            $table->string('image')->nullable();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('badge')->nullable();
            $table->string('level');
            $table->date('date');
            $table->boolean('is_published')->default(false);
            $table->string('difficulty_level')->nullable();
            $table->timestamps();
            
        });
    }

   public function down()
    {
        Schema::dropIfExists('courses');
    }
};
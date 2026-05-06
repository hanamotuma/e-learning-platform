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
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('instructor_id');
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2);
            $table->float('rating')->default(0);
            $table->integer('reviews')->default(0);
            $table->integer('students')->default(0);
            $table->integer('hours');
            $table->string('image');
            $table->string('category_id');
            $table->string('badge')->nullable();
            $table->string('level');
            $table->date('date');
            $table->string('status')->default('active'); // active, inactive, archived
            $table->string('difficulty_level')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
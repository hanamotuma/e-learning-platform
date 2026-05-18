<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
    if (!Schema::hasColumn('courses', 'students_count')) {
        $table->integer('students_count')->default(0);
    }
    if (!Schema::hasColumn('courses', 'reviews_count')) {
        $table->integer('reviews_count')->default(0);
    }
    if (!Schema::hasColumn('courses', 'duration_hours')) {
        $table->integer('duration_hours')->default(40);
    }
    if (!Schema::hasColumn('courses', 'badge')) {
        $table->string('badge')->default('Featured');
    }
});
    }
};

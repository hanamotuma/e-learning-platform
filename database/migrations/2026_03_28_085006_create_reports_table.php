<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Report generator
            $table->enum('report_type', ['admin', 'user', 'course', 'payment', 'progress', 'system']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('parameters')->nullable(); // Report parameters
            $table->json('data')->nullable(); // Report data
            $table->string('file_path')->nullable(); // Generated file path
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
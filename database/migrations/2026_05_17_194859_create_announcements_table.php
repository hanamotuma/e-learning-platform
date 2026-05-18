<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['general', 'important', 'update', 'maintenance'])->default('general');
            $table->enum('target_audience', ['all', 'students', 'instructors', 'both'])->default('all');
            $table->boolean('send_email')->default(false);
            $table->boolean('send_notification')->default(true);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'published', 'expired'])->default('draft');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcements');
    }
};
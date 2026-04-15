<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_library', function (Blueprint $table) {
            $table->id('resource_id');
            $table->string('resource_type'); // course_material, assignment, etc.
            $table->morphs('resourceable'); // Polymorphic relationship
            $table->string('title');
            $table->string('file_path');
            $table->string('file_type'); // PDF, PPT, VIDEO, etc.
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_library');
    }
};
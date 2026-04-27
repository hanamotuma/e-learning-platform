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
        Schema::create('questions', function (Blueprint $table) {
            // Use 'id' for standard Laravel conventions
        $table->id(); 
        $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
        $table->text('question_text');
        
        // Match the model's expected field names
        $table->string('question_type')->default('multiple_choice'); // enum or string
        $table->json('options')->nullable(); // For MCQ choices
        $table->json('correct_answer')->nullable(); // Stores the correct key/index
        $table->integer('points')->default(1);
        $table->text('explanation')->nullable();
        
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};

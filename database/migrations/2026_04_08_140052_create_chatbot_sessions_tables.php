<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('chatbot_sessions', function (Blueprint $table) {
        $table->id();
        $table->string('session_id');
        $table->text('conversation_history')->nullable();
        $table->enum('status', ['active', 'ended'])->default('active');
        
        // This is the line that was causing the error:
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        
        $table->timestamps();
    });
}
    public function down(): void
    {
        Schema::dropIfExists('chatbot_sessions');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First create the users table
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id'); // Primary key
            $table->string('username')->unique();
            $table->string('full_name'); 
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true); 
            $table->integer('learning_streak')->default(0);
            $table->integer('total_hours_learned')->default(0);
            $table->integer('certificates_earned')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->string('role');
            $table->string('phone')->nullable()->after('full_name');
            $table->string('bio')->nullable()->after('phone');
            $table->string('instructor_profiles');
            $table->rememberToken();
            $table->timestamps();
        });

        // Then create the course_user (enrollments) table separately
        Schema::create('course_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->timestamp('enrolled_at');
            $table->string('payment_status')->default('pending');
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->unique(['user_id', 'course_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_user');
        Schema::dropIfExists('users');
        Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('role');
        $table->dropColumn('phone', 'bio');
        
    });
    }
};
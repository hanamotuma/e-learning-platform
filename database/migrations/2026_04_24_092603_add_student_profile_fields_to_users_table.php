<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->string('interests')->nullable();
            $table->string('education')->nullable();
            $table->string('profile_picture_url')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'address', 'city', 'state', 'zip_code', 'country', 
                'phone', 'bio', 'interests', 'education', 'profile_picture_url'
            ]);
        });
    }
};
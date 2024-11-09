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
        Schema::create('users', function (Blueprint $table) {
            $table->id('_id');
            $table->text('name')->nullable();
            $table->tinyText('email')->nullable();
            $table->tinyText('phoneno')->nullable();
            $table->text('social_id')->nullable();
            $table->boolean('social_login')->default(1)->comment('0:Not Social Login , 1:Social Login')->nullable();
            $table->boolean('disable')->default(0)->comment('0:Not Disable ,1:Disable')->nullable();
            $table->text('profile_pic')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

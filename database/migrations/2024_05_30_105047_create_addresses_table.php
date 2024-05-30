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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('house_no',100)->nullable();
            $table->string('area',255)->nullable();
            $table->string('options_to_reach',255)->nullable();
            $table->string('latitude',50)->nullable();
            $table->string('longtitude',50)->nullable();
            $table->string('save_as', 30)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('_id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};

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
        Schema::create('yoga_poses', function (Blueprint $table) {
            $table->id();
            $table->tinyText('name')->nullable();
            $table->text('short_decription')->nullable();
            $table->text('long_decription')->nullable();
            $table->text('image')->nullable();
            $table->tinyText('pose_type')->nullable();
            $table->tinyText('sanskrit_meaning')->nullable();
            $table->text('benefits')->nullable();
            $table->text('targets')->nullable();
            $table->text('guidance')->nullable();
            $table->text('things_keep_in_mind')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('pose_categories');
            $table->boolean('draft')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yoga_poses');
    }
};

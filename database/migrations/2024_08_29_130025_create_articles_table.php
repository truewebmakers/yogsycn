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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->text('title')->nullable();
            $table->text('short_description')->nullable();
            $table->text('long_description')->nullable();
            $table->text('image')->nullable();
            $table->tinyText('author_name')->nullable();
            $table->text('author_image')->nullable();
            $table->text('author_details')->nullable();
            $table->boolean('draft')->default(0)->nullable();
            $table->boolean('is_latest')->default(0)->nullable();
            $table->boolean('is_expert_approved')->default(0)->nullable();
            $table->text('related_poses')->nullable();
            $table->text('slug')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();

            $table->foreign('category_id')->references('id')->on('artical_categories');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};

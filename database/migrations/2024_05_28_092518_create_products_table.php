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
        Schema::create('products', function (Blueprint $table) {
            $table->id('_id');
            $table->string('name', 255)->nullable();
            $table->string('description',1000)->nullable();
            $table->boolean('veg')->default(1)->comment('1:Veg 0:Non Veg');
            $table->string('price')->nullable();
            $table->string('image',3000)->nullable();
            $table->boolean('best_seller')->default(0)->comment('');
            $table->boolean('disable')->default(0)->comment('0:not best seller ,1:best seller');
            $table->unsignedBigInteger('product_category_id');
            $table->foreign('product_category_id')->references('_id')->on('product_categories');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

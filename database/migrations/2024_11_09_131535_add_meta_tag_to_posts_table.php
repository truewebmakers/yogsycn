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
        Schema::table('yoga_poses', function (Blueprint $table) {
            $table->text('meta_tag')->nullable();  // Add this line to store HTML content
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yoga_poses', function (Blueprint $table) {
            //
            $table->dropColumn('meta_tag');
        });
    }
};

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
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phoneno',15)->nullable();
            $table->string('dob',30)->nullable();
            $table->text('aniversary_date',30)->nullable();
            $table->tinyText('gender')->nullable()->comment('F:Female,M:Male,O:Other');
            $table->boolean('disable')->default(0)->comment('0:Not Disable ,1:Disable');
            // $table->string('password');
            // $table->rememberToken();
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

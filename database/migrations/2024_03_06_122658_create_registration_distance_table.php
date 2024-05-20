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
        Schema::create('registration_distance', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('task_distance_id')->unsigned();
            $table->decimal('distance', 8, 2)->nullable(); 
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('task_distance_id')->references('id')->on('task_distance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_distance');
    }
};

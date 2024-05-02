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
        Schema::create('registration_hourly', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('task_hourly_id')->unsigned();
            $table->decimal('minutes_worked', 10, 4)->nullable(); 
            $table->decimal('earnings', 10, 4); 
            $table->timestamps();

            // Add a foreign key constraint
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('task_hourly_id')->references('id')->on('task_hourly');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_hourly');
    }
};

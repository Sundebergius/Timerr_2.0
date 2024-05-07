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
        Schema::create('task_hourly', function (Blueprint $table) {
            $table->id();
            //$table->bigInteger('task_id')->unsigned()->nullable();
            $table->bigInteger('client_id')->unsigned()->nullable();
            $table->string('title');
            $table->decimal('rate_per_hour')->nullable();
            $table->decimal('rate_per_minute', 15, 10)->nullable();
            $table->timestamps();

            // Add a foreign key constraint
            //$table->foreign('task_id')->references('id')->on('tasks');
            $table->foreign('client_id')->references('id')->on('clients');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_hourly');
    }
};

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
        Schema::create('task_distances', function (Blueprint $table) {
            $table->id();
            //$table->bigInteger('task_id')->unsigned()->nullable();
            $table->bigInteger('client_id')->unsigned()->nullable();
            $table->string('title');
            $table->decimal('distance', 8, 2)->nullable()->default(0);
            $table->decimal('price_per_km', 8, 2);
            $table->timestamps();

            //$table->foreign('task_id')->references('id')->on('tasks');
            $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_distance');
    }
};

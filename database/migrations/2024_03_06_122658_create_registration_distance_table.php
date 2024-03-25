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
            //$table->bigInteger('task_id')->unsigned();
            $table->string('title');
            //$table->string('description')->nullable();
            $table->decimal('distance')->nullable(); // distance driven in kilometers
            $table->decimal('price_per_km')->nullable(); // price per kilometer driven
            $table->timestamps();
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

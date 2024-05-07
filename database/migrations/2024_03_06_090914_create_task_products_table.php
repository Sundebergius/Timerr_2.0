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
        Schema::create('task_products', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->bigInteger('client_id')->unsigned()->nullable();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('title'); 
            $table->integer('quantity');
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_products');
    }
};

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
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('product_id');
            $table->string('type'); // 'product' or 'service'
            $table->integer('quantity')->default(0); // For both products and services
            $table->json('attributes')->nullable(); // To store service-specific attributes like size, etc.
            $table->timestamps();
    
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
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

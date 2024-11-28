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
        Schema::create('task_product_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_product_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity_used')->default(0); // Quantity of material used
            $table->decimal('total_cost', 8, 2)->default(0.00); // Optional: Total cost for this material usage
            $table->timestamps();
    
            $table->foreign('task_product_id')->references('id')->on('task_products')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade'); // Assuming materials are stored in products
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_product_materials');
    }
};

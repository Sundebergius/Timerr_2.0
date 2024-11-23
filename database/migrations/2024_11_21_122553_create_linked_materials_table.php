<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('linked_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id'); // Linking to the product
            $table->unsignedBigInteger('parent_material_1_id'); // First parent material
            $table->unsignedBigInteger('parent_material_2_id'); // Second parent material
            $table->json('child_material_relationships'); // Stores child relationships and usage
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('parent_material_1_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('parent_material_2_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('linked_materials');
    }
};

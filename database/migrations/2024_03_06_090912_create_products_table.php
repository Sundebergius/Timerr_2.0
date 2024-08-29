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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('title');
            $table->string('category')->nullable();
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 8, 2);
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('quantity_sold')->default(0);
            $table->boolean('active')->default(true);
            $table->json('attributes')->nullable();
            $table->timestamps();

            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

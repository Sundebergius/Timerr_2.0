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
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('title');
            $table->enum('type', ['product', 'service'])->default('product'); // Added type field
            $table->string('category')->nullable();
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 8, 2)->nullable(); // Price can be null for services
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('quantity_sold')->default(0);
            $table->boolean('active')->default(true);
            $table->json('attributes')->nullable(); // Stores attributes for services
            $table->timestamps();
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

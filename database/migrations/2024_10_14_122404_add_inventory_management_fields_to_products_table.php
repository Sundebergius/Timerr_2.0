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
        Schema::table('products', function (Blueprint $table) {
            // New fields for enhanced inventory management
            $table->string('unit_type')->nullable()->after('quantity_sold'); // e.g., grams, pieces
            $table->decimal('usage_per_unit', 8, 2)->nullable()->after('unit_type'); // Quantity used per parent product unit
            $table->boolean('is_material')->default(false)->after('usage_per_unit'); // Distinguish materials from sellable products
            $table->decimal('minimum_stock_alert')->nullable()->after('is_material'); // Trigger alerts for low stock
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('unit_type');
            $table->dropColumn('usage_per_unit');
            $table->dropColumn('is_material');
            $table->dropColumn('minimum_stock_alert');
        });
    }
};

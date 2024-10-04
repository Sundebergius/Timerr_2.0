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
        Schema::table('task_products', function (Blueprint $table) {
            $table->decimal('total_price', 10, 2)->after('attributes')->nullable(); // Add the total_price column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_products', function (Blueprint $table) {
            $table->dropColumn('total_price'); // Drop the column if rolled back
        });
    }
};

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
        Schema::table('projects', function (Blueprint $table) {
            // Drop the current client_id foreign key if it exists
            $table->dropForeign(['client_id']);
    
            // Re-add the foreign key with onDelete('set null')
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
    
            // Re-add the original foreign key constraint if needed (e.g., onDelete cascade or restrict)
            $table->foreign('client_id')->references('id')->on('clients');
        });
    }
};

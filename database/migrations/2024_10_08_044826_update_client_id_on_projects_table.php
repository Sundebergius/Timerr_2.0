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
            // Check and drop the existing foreign key if it exists
            if (DB::select(DB::raw("SHOW KEYS FROM projects WHERE Key_name = 'projects_client_id_foreign'"))) {
                $table->dropForeign(['client_id']);
            }

            // Modify the client_id foreign key to include onDelete('set null')
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['client_id']);

            // Restore the original foreign key constraint if needed
            $table->foreign('client_id')->references('id')->on('clients');
        });
    }
};
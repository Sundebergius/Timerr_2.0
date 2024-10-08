<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the foreign key exists
        $foreignKeyExists = DB::select("SHOW KEYS FROM projects WHERE Key_name = 'projects_client_id_foreign'");

        Schema::table('projects', function (Blueprint $table) use ($foreignKeyExists) {
            // Drop the foreign key if it exists
            if ($foreignKeyExists) {
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

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
        $foreignKeyExists = DB::select("SHOW KEYS FROM tasks WHERE Key_name = 'tasks_client_id_foreign'");

        Schema::table('tasks', function (Blueprint $table) use ($foreignKeyExists) {
            if ($foreignKeyExists) {
                // Drop the existing foreign key constraint on client_id
                $table->dropForeign(['client_id']);
            }

            // Re-add foreign key constraint with onDelete('set null')
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the modified foreign key constraint
            $table->dropForeign(['client_id']);

            // Re-add the original foreign key constraint (without onDelete('set null'))
            $table->foreign('client_id')->references('id')->on('clients');
        });
    }
};

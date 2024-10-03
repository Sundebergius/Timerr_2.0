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
        Schema::table('users', function (Blueprint $table) {
            $table->text('google_refresh_token')->nullable()->after('google_token');
            $table->string('google_calendar_id')->nullable()->after('google_token'); // Add calendar ID

        });
    }
    
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_refresh_token');
            $table->dropColumn('google_calendar_id');

        });
    }
};

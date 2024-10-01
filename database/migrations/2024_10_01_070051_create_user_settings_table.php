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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('show_email')->default(true);
            $table->boolean('show_address')->default(true);
            $table->boolean('show_phone')->default(true);
            $table->boolean('show_cvr')->default(false); // New field for CVR visibility
            $table->boolean('show_city')->default(false); // New field for city visibility
            $table->boolean('show_zip_code')->default(false); // New field for zip code visibility
            $table->boolean('show_country')->default(false); // New field for country visibility
            $table->boolean('show_notes')->default(false);
            $table->boolean('show_contact_persons')->default(false); // Show/hide contact persons
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};

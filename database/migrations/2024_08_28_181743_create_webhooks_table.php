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
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Link to user
            $table->string('name'); // Webhook name
            $table->string('url'); // Webhook URL
            $table->string('event'); // Event type
            $table->boolean('active')->default(false); // Active status
            $table->timestamps();

            // Unique constraint for the combination of user_id and url
            $table->unique(['user_id', 'url', 'event']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};

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
        Schema::create('registration_projects', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('task_project_id')->unsigned();
            $table->string('title');
            $table->string('type'); // New column to specify the type of registration
            $table->text('description')->nullable(); // Description of the registration
            $table->datetime('date')->nullable(); // Date of the registration
            $table->decimal('amount')->nullable(); // Amount (could be hours, expenses, etc.)
            $table->string('currency')->default('DKK');
            $table->string('location')->nullable(); // Location (if applicable)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('task_project_id')->references('id')->on('task_projects');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_project');
    }
};

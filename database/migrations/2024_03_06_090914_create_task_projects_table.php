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
        Schema::create('task_projects', function (Blueprint $table) {
            $table->id();
            //$table->bigInteger('user_id')->unsigned();
            //$table->bigInteger('task_id')->unsigned();
            $table->string('title');
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->decimal('price')->nullable();
            $table->string('currency')->default('DKK');
            $table->string('project_location')->nullable();
            $table->timestamps();

            //$table->foreign('user_id')->references('id')->on('users');
            //$table->foreign('task_id')->references('id')->on('tasks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_projects');
    }
};

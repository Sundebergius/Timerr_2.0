<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('registrations', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('user_id')->unsigned();
        $table->bigInteger('task_id')->unsigned();

        $table->string('type');
        $table->string('name');
        $table->string('description')->nullable();

        $table->datetime('start_date')->nullable();
        $table->datetime('end_date')->nullable();

        $table->decimal('hours')->nullable();
        $table->decimal('price')->nullable();

        $table->string('project_location')->nullable();
        $table->string('product_name')->nullable();

        $table->decimal('product_quantity')->nullable();
        $table->decimal('distance')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};

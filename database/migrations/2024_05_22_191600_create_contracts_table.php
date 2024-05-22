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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->text('service_description');
            $table->date('start_date');
            $table->date('end_date')->nullable();  // Nullable in case there is no defined end date
            $table->decimal('total_amount', 10, 2);  // Assuming the currency has two decimal places
            $table->string('currency');
            $table->date('due_date');
            $table->text('payment_terms');
            $table->string('status')->default('pending');
            $table->boolean('is_signed')->default(false);
            $table->text('additional_terms')->nullable();  // Nullable in case there are no additional terms
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

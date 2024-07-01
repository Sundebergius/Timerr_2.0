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
        Schema::create('invoices', function (Blueprint $table) {
            // Primary key
            $table->id();
    
            // Foreign keys linking to other entities
            $table->bigInteger('user_id')->unsigned(); // The user who created the invoice
            $table->bigInteger('project_id')->unsigned()->nullable(); // Associated project
            $table->bigInteger('client_id')->unsigned()->nullable(); // Client being invoiced
    
            // Invoice specific information
            $table->string('title')->nullable(); // Title or description of the invoice
            $table->string('status')->default('draft'); // Current status of the invoice (e.g., draft, sent, paid)
            $table->date('issue_date')->nullable(); // The date when the invoice was issued
            $table->date('due_date')->nullable(); // The date by which the invoice should be paid
            $table->string('currency', 3)->default('DKK'); // Currency for the amounts in the invoice
    
            // Financial details
            $table->decimal('subtotal', 10, 2)->nullable(); // Total before taxes and discounts
            $table->decimal('discount', 5, 2)->default(0.00); // Discount applied to the invoice
            $table->decimal('vat', 5, 2)->default(25.00)->nullable(); // Value-added tax amount
            $table->decimal('total', 10, 2)->nullable(); // Final amount due including taxes and after discounts
    
            // Payment details
            $table->string('payment_terms')->nullable(); // Payment terms (e.g., Net 30)
            $table->string('payment_method')->nullable(); // Method of payment (e.g., bank transfer, online payment)
            $table->string('transaction_id')->nullable(); // ID from the payment gateway for the transaction
    
            // Additional information
            $table->string('file_path')->nullable(); // Path to the invoice file (if any)
            $table->date('last_reminder_sent')->nullable(); // The last date a payment reminder was sent
    
            // Timestamps
            $table->timestamps();
    
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

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
        Schema::create('sales_transactions_process', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('so_number')->unique(); // Sales Order (SO) Number
            $table->unsignedBigInteger('customer_id'); // Foreign key to customers table
            $table->unsignedBigInteger('sales_staff_id'); // Foreign key to users table (staff)
            $table->enum('payment_method', ['cash', 'credit']); // Payment method (cash or credit)
            $table->decimal('total_amount', 10, 2); // Total amount of the transaction
            $table->decimal('amount_tendered', 10, 2)->nullable(); // Cash amount (nullable for credit payments)
            $table->decimal('change_amount', 10, 2)->nullable(); // Change (nullable for credit payments)
            $table->string('charge_to')->nullable(); // Charge to (nullable for cash payments)
            $table->integer('total_items'); // Total number of items in the transaction
            $table->enum('remarks', ['Paid', 'Not Paid'])->default('Not Paid'); // Remarks
            $table->timestamps(); // Created at and updated at timestamps

            // Foreign key constraints
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('sales_staff_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_transactions_process');
    }
};

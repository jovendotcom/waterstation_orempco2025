<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique(); // Unique PO Number
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade'); // Customer Reference
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade'); // Staff who handled the transaction
            $table->enum('payment_method', ['cash', 'credit']); // Payment method
            $table->decimal('total_amount', 10, 2); // Total transaction amount
            $table->decimal('amount_tendered', 10, 2)->nullable(); // Cash payment only
            $table->decimal('change_amount', 10, 2)->nullable(); // Cash payment only
            $table->string('credit_payment_method')->nullable(); // Credit method (e.g., Salary Deduction)
            $table->integer('total_items'); // Total number of items
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_transactions');
    }
};


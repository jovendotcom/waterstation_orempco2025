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
        Schema::create('sales_transactions_items', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('sales_transaction_id'); // Foreign key to sales_transactions table
            $table->unsignedBigInteger('product_id'); // Foreign key to products_inventory table
            $table->string('product_name'); // Product name
            $table->integer('quantity'); // Quantity of the product
            $table->decimal('price', 10, 2); // Price per unit
            $table->decimal('subtotal', 10, 2); // Subtotal (quantity * price)
            $table->json('materials_used')->nullable(); // JSON to store materials used
            $table->timestamps(); // Created at and updated at timestamps

            // Foreign key constraints
            $table->foreign('sales_transaction_id')->references('id')->on('sales_transactions_process')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products_inventory')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_transactions_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sales_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_transaction_id')->constrained('sales_transactions')->onDelete('cascade'); // Link to sales transaction
            $table->foreignId('product_id')->constrained('products_for_sale')->onDelete('cascade'); // Link to product
            $table->string('product_name'); // Store product name (in case product gets deleted)
            $table->integer('quantity'); // Quantity purchased
            $table->decimal('price', 10, 2); // Price per unit
            $table->decimal('subtotal', 10, 2); // Quantity * Price
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_items');
    }
};


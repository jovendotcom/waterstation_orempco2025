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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_image')->nullable(); // Path to the product image
            $table->string('product_name');
            $table->string('category');
            $table->string('variant')->nullable();
            $table->string('unit');
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->integer('low_stock_limit');
            $table->enum('status', ['Available', 'Low Stock', 'Out of Stock'])->default('Available');
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

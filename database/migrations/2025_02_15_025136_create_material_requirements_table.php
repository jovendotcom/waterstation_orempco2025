<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('material_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id'); // Match type with products_for_sale.id
            $table->unsignedBigInteger('material_id'); // Match type with stocks_counts.id
            $table->integer('quantity_needed')->default(1);
            $table->timestamps();

            // Correct foreign key reference
            $table->foreign('product_id')->references('id')->on('products_for_sale')->onDelete('cascade');
            $table->foreign('material_id')->references('id')->on('stocks_counts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('material_requirements');
    }
};

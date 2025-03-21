<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('material_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                  ->constrained('products_inventory')
                  ->onDelete('cascade');

            $table->foreignId('material_id')
                  ->constrained('materials_inventory')
                  ->onDelete('cascade');

            $table->decimal('quantity_used', 10, 2);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('material_product');
    }
};

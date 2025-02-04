<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('products_for_sale', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->decimal('price', 10, 2);
            $table->integer('quantity')->nullable();
            $table->string('product_image')->nullable();
            $table->json('items_needed')->nullable(); // Stores multiple items or none
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('products_for_sale');
    }
};

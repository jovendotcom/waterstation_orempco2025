<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('products_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->decimal('price', 10, 2);
            $table->unsignedBigInteger('subcategory_id'); 
            $table->string('product_image')->nullable();
            $table->integer('product_quantity')->nullable();
            $table->timestamps();

            $table->foreign('subcategory_id')
                  ->references('id')
                  ->on('subcategories')
                  ->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('products_inventory');
    }
};
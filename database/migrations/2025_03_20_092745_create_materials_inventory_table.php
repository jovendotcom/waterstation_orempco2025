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
        Schema::create('materials_inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id'); // Foreign key
            $table->string('material_name');
            $table->string('unit');
            $table->decimal('cost_per_unit', 10, 2);
            $table->integer('total_stocks'); // New field
            $table->timestamps();

            // Define the foreign key constraint
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials_inventory');
    }
};

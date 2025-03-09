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
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('name'); // Subcategory name
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // Foreign key to categories
            $table->timestamps(); // Created at and updated at timestamps
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('subcategories');
    }
};

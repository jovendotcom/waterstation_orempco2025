<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('products_for_sale', function (Blueprint $table) {
            // Add new columns
            $table->unsignedBigInteger('category_id')->nullable()->after('id');
            $table->unsignedBigInteger('subcategory_id')->nullable()->after('category_id');
            $table->string('unit_of_measurement')->nullable()->after('subcategory_id');
            $table->string('size_options')->nullable()->after('unit_of_measurement'); // For milktea and coffee
            $table->string('add_ons')->nullable()->after('size_options'); // For milktea

            // Add foreign keys
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('subcategory_id')->references('id')->on('subcategories')->onDelete('set null');
        });
    }

    public function down() {
        Schema::table('products_for_sale', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['category_id']);
            $table->dropForeign(['subcategory_id']);

            // Drop columns
            $table->dropColumn([
                'category_id',
                'subcategory_id',
                'unit_of_measurement',
                'size_options',
                'add_ons',
            ]);
        });
    }
};
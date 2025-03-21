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
        Schema::table('products_inventory', function (Blueprint $table) {
            $table->string('size_options')->nullable()->after('product_quantity');
        });
    }
    
    public function down()
    {
        Schema::table('products_inventory', function (Blueprint $table) {
            $table->dropColumn('size_options');
        });
    }
};

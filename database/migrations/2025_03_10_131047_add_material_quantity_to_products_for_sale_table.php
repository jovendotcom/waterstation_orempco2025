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
        Schema::table('products_for_sale', function (Blueprint $table) {
            $table->json('material_quantities')->nullable()->after('items_needed');
        });
    }
    
    public function down()
    {
        Schema::table('products_for_sale', function (Blueprint $table) {
            $table->dropColumn('material_quantities');
        });
    }
};

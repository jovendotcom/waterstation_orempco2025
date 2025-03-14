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
        Schema::table('products_for_sale', function (Blueprint $table) {
            // Add the new column as LONGTEXT
            $table->longText('material_quantity_unit_of_measurement')->nullable()->after('material_quantities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products_for_sale', function (Blueprint $table) {
            // Drop the column if the migration is rolled back
            $table->dropColumn('material_quantity_unit_of_measurement');
        });
    }
};
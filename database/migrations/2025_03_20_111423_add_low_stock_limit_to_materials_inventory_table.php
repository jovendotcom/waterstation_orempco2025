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
        Schema::table('materials_inventory', function (Blueprint $table) {
            $table->integer('low_stock_limit')->default(0)->after('total_stocks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials_inventory', function (Blueprint $table) {
            $table->dropColumn('low_stock_limit');
        });
    }
};

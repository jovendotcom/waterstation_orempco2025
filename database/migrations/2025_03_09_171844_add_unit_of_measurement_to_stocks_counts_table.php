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
        Schema::table('stocks_counts', function (Blueprint $table) {
            $table->string('unit_of_measurement')->after('quantity'); // Add this
        });
    }
    
    public function down()
    {
        Schema::table('stocks_counts', function (Blueprint $table) {
            $table->dropColumn('unit_of_measurement'); // Add this
        });
    }
};

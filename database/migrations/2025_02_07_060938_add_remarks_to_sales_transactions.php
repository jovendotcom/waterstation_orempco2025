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
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->enum('remarks', ['Paid', 'Not Paid'])->default('Not Paid')->after('total_items');
        });
    }
    
    public function down()
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
    
};

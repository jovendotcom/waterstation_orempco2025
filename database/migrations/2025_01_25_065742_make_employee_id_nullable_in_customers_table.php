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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->change(); // Make employee_id nullable
        });
    }
    
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('employee_id')->nullable(false)->change(); // Revert back if needed
        });
    }
    
};

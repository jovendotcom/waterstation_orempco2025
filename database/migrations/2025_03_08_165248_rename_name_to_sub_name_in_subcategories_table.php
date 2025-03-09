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
        Schema::table('subcategories', function (Blueprint $table) {
            // Use CHANGE syntax for MariaDB
            $table->string('sub_name')->after('id'); // Add the new column
        });

        // Copy data from the old column to the new column
        DB::table('subcategories')->update(['sub_name' => DB::raw('name')]);

        // Drop the old column
        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('subcategories', function (Blueprint $table) {
            // Recreate the old column
            $table->string('name')->after('id');
        });

        // Copy data back to the old column
        DB::table('subcategories')->update(['name' => DB::raw('sub_name')]);

        // Drop the new column
        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropColumn('sub_name');
        });
    }
};
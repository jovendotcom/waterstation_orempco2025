<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateTypeEnumInCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE customers MODIFY COLUMN type ENUM('Department', 'Employee', 'Outside') AFTER employee_id");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to original enum without 'Outside'
        DB::statement("ALTER TABLE customers MODIFY COLUMN type ENUM('Department', 'Employee') AFTER employee_id");
    }
}

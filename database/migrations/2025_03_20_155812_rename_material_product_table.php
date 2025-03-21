<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('material_product', 'material_inventory_product_inventory');
    }

    public function down(): void
    {
        Schema::rename('material_inventory_product_inventory', 'material_product');
    }
};

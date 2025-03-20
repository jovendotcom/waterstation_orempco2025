<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialInventory extends Model
{
    use HasFactory;

    protected $table = 'materials_inventory';

    protected $fillable = [
        'category_id',
        'material_name',
        'unit',
        'cost_per_unit',
        'total_stocks',
        'low_stock_limit',
    ];

    /**
     * Get the category that owns the material.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products() {
        return $this->belongsToMany(ProductInventory::class)->withPivot('quantity_used');
    }
}

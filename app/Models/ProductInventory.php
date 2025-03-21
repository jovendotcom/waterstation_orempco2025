<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'products_inventory';

    // Define the primary key
    protected $primaryKey = 'id';

    // Define the fields that are mass assignable
    protected $fillable = [
        'product_name',
        'price',
        'subcategory_id',
        'product_image',
        'product_quantity',
        'size_options',
    ];

    // Define the fields that should be hidden from JSON output
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // Define the relationship with the Subcategory model
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }

    public function materials() {
        return $this->belongsToMany(MaterialInventory::class, 'material_inventory_product_inventory', 'product_id', 'material_id')
                    ->withPivot('quantity_used')->withTimestamps();
    }    

    public function getMaterialCostAttribute() {
        $totalCost = 0;
        foreach ($this->materials as $material) {
            $totalCost += $material->cost_per_unit * $material->pivot->quantity_used;
        }
        return $totalCost;
    }

    public function getProfitAttribute() {
        return $this->price - $this->material_cost;
    }
}
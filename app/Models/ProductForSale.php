<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductForSale extends Model
{
    use HasFactory;

    protected $table = 'products_for_sale';

    protected $fillable = [
        'product_name',
        'price',
        'quantity', 
        'product_image', 
        'category_id',
        'subcategory_id',
        'size_options',
        'material_quantities',
        'material_quantity_unit_of_measurement',
        'add_ons',
        'unit_of_measurement', 
        'items_needed' // JSON array of items needed
    ];
    
    protected $casts = [
        'items_needed' => 'array', // Ensures JSON field is treated as an array
    ];

    // Relationship: A product belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Relationship: A product belongs to a subcategory
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }
}
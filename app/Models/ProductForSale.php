<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductForSale extends Model {
    use HasFactory;
    
    protected $table = 'products_for_sale';
    protected $fillable = ['product_name', 'price', 'quantity', 'product_image', 'items_needed'];
    
    protected $casts = [
        'items_needed' => 'array', // Ensures JSON field is treated as an array
    ];
}

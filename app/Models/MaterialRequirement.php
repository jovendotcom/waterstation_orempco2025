<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialRequirement extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'material_id', 'quantity_needed'];

    public function product()
    {
        return $this->belongsTo(ProductForSale::class, 'product_id');
    }

    public function material()
    {
        return $this->belongsTo(StocksCount::class, 'material_id');
    }
}

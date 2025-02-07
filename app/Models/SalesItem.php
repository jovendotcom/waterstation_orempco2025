<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_transaction_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'subtotal',
    ];

    // Relationship with Sales Transaction
    public function salesTransaction()
    {
        return $this->belongsTo(SalesTransaction::class);
    }

    // Relationship with Product
    public function product()
    {
        return $this->belongsTo(ProductForSale::class);
    }
}


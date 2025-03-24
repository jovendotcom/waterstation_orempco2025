<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesTransactionItem extends Model
{
    use HasFactory;

    protected $table = 'sales_transactions_items'; // Table name

    protected $fillable = [
        'sales_transaction_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'subtotal',
        'materials_used',
    ];

    protected $casts = [
        'materials_used' => 'array', // Cast JSON field to array
    ];

    /**
     * Get the sales transaction associated with the item.
     */
    public function salesTransaction()
    {
        return $this->belongsTo(SalesTransactionProcess::class, 'sales_transaction_id');
    }

    /**
     * Get the product associated with the item.
     */
    public function product()
    {
        return $this->belongsTo(ProductInventory::class, 'product_id');
    }
}

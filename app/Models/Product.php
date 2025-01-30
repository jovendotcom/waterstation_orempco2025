<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_name',
        'category',
        'variant',
        'unit',
        'price',
        'quantity',
        'low_stock_limit',
        'status',
        'product_image',
    ];

    /**
     * Automatically determine product status based on quantity and stock limit.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($product) {
            if ($product->quantity <= $product->low_stock_limit) {
                $product->status = 'Low Stock';
            } else {
                $product->status = 'Available';
            }
        });
    }
}

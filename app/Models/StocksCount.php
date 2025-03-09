<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StocksCount extends Model
{
    use HasFactory;

    protected $table = 'stocks_counts';

    protected $fillable = [
        'item_name',
        'quantity',
        'unit_of_measurement',
        'price',
        'remarks',
        'category_id', // Add this
    ];

    // Relationship: A stock item belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
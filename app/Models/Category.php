<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Fields that can be mass-assigned
    protected $fillable = ['name'];

    // Relationship: A Category has many Subcategories
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    // Relationship: A Category has many Products
    public function products()
    {
        return $this->hasMany(ProductForSale::class, 'category_id');
    }

    // Relationship: A Category has many Stock Counts
    public function stockCounts()
    {
        return $this->hasMany(StocksCount::class);
    }

    // In App\Models\Category.php

    public function materials()
    {
        return $this->hasMany(MaterialInventory::class);
    }

}

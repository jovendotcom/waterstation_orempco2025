<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    use HasFactory;

    // Fields that can be mass-assigned
    protected $fillable = ['sub_name', 'category_id'];

    // Relationship: A Subcategory belongs to a Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relationship: A Subcategory has many Products
    public function products()
    {
        return $this->hasMany(ProductForSale::class, 'subcategory_id');
    }
}

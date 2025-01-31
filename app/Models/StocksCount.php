<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StocksCount extends Model
{
    use HasFactory;

    protected $table = 'stocks_counts'; // Ensure this matches the database table name

    protected $fillable = [
        'item_name',
        'quantity',
        'remarks',
    ];
}


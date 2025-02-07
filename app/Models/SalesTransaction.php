<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'customer_id',
        'staff_id',
        'payment_method',
        'total_amount',
        'amount_tendered',
        'change_amount',
        'credit_payment_method',
        'total_items',
    ];

    // Relationship with Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relationship with Staff (User)
    public function staff()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Sales Items
    public function items()
    {
        return $this->hasMany(SalesItem::class);
    }
}


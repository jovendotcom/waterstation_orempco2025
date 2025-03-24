<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesTransactionProcess extends Model
{
    use HasFactory;

    protected $table = 'sales_transactions_process'; // Table name

    protected $fillable = [
        'so_number',
        'customer_id',
        'sales_staff_id',
        'payment_method',
        'total_amount',
        'amount_tendered',
        'change_amount',
        'charge_to',
        'total_items',
        'remarks',
    ];

    /**
     * Get the customer associated with the transaction.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the sales staff who handled the transaction.
     */
    public function salesStaff()
    {
        return $this->belongsTo(User::class, 'sales_staff_id');
    }
}

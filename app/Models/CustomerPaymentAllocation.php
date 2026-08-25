<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPaymentAllocation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function payment()
    {
        return $this->belongsTo(CustomerPayment::class, 'customer_payment_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}

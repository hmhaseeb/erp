<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns()
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function paymentAllocations()
    {
        return $this->hasMany(CustomerPaymentAllocation::class);
    }
}

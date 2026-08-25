<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function defaultCashAccount()
    {
        return $this->belongsTo(Account::class, 'default_cash_account_id');
    }

    public function defaultBankAccount()
    {
        return $this->belongsTo(Account::class, 'default_bank_account_id');
    }
}

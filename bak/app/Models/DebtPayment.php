<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtPayment extends Model
{
    protected $fillable = ['debt_id','account_id','amount','transacted_at','note'];
    protected $casts = ['transacted_at' => 'datetime'];

    public function debt() {
        return $this->belongsTo(Debt::class);
    }
    public function account() {
        return $this->belongsTo(Account::class);
    }
}

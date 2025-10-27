<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $fillable = [
        'account_id','creditor_name','principal_amount','interest_rate',
        'start_date','due_date','status','note'
    ];

    public function account() {
        return $this->belongsTo(Account::class);
    }

    public function payments() {
        return $this->hasMany(DebtPayment::class);
    }

    public function getPaidAmountAttribute(): string {
        return number_format($this->payments()->sum('amount'), 2, '.', '');
    }

    public function getRemainingAttribute(): string {
        $rem = (float)$this->principal_amount - (float)$this->payments()->sum('amount');
        return number_format(max($rem, 0), 2, '.', '');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Debt extends Model
{
    protected $fillable = [
        'account_id',
        'creditor_name',
        'principal_amount',
        'interest_rate',
        'monthly_payment',
        'start_date',
        'due_date',
        'note',
        'status',
    ];

    // Pastikan angka dibaca sebagai float (bukan string)
    protected $casts = [
        'principal_amount' => 'float',
        'interest_rate'    => 'float',
        'monthly_payment'  => 'float',
    ];

    // Opsional: supaya muncul saat toArray()/JSON (tidak wajib untuk Blade)
    protected $appends = [
        'interest_amount',
        'total_due',
        'paid_amount',
        'remaining_due',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\DebtPayment::class);
    }

    /** Bunga rupiah: persen flat dari pokok */
    public function getInterestAmountAttribute(): float
    {
        $rate = (float) ($this->interest_rate ?? 0);
        $principal = (float) ($this->principal_amount ?? 0);
        return round($principal * $rate / 100, 2);
    }

    /** Total tagihan = pokok + bunga */
    public function getTotalDueAttribute(): float
    {
        return round((float) ($this->principal_amount ?? 0) + $this->interest_amount, 2);
    }

    /** Total sudah dibayar (jumlah payments) */
    public function getPaidAmountAttribute(): float
    {
        // Query langsung supaya akurat meski relasi belum di-load
        return (float) $this->payments()->sum('amount');
    }

    /** Sisa = total_due - paid_amount, minimum 0 */
    public function getRemainingDueAttribute(): float
    {
        $left = $this->total_due - $this->paid_amount;
        return $left > 0 ? round($left, 2) : 0.0;
    }
}

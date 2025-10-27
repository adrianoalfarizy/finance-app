<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    protected $fillable = ['account_id','name','target_amount'];

    public function account() {
        return $this->belongsTo(Account::class);
    }

    public function entries() {
        return $this->hasMany(SavingEntry::class);
    }

    public function getCurrentAmountAttribute(): string {
        $in = $this->entries()->where('type','deposit')->sum('amount');
        $out = $this->entries()->where('type','withdraw')->sum('amount');
        return number_format($in - $out, 2, '.', '');
    }
}

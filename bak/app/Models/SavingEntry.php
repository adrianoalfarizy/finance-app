<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingEntry extends Model
{
    protected $fillable = ['saving_id','type','amount','transacted_at','note'];
    protected $casts = ['transacted_at' => 'datetime'];

    public function savingGoal()
{
    return $this->belongsTo(Saving::class);
}
    
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'account_id','user_id','type','group_id','category_id','amount','transacted_at','description'
    ];

    protected $casts = [
        'transacted_at' => 'datetime',
    ];

    public function account(): BelongsTo {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}

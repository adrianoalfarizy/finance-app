<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\{Account, Transaction, Debt};
use App\Policies\{AccountPolicy, TransactionPolicy, DebtPolicy};

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Account::class => AccountPolicy::class,
    ];
    // protected $policies = [
    //     Account::class => AccountPolicy::class,
    //     Transaction::class => TransactionPolicy::class,
    //     Debt::class => DebtPolicy::class,
    // ];

    public function boot(): void
    { /* Laravel 11 auto-discovery ok */
    }
}


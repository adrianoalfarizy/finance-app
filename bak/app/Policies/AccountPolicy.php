<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function view(User $user, Account $account): bool {
        return $account->users()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Account $account): bool {
        return $account->users()->where('user_id', $user->id)
            ->whereIn('role', ['owner','editor'])->exists();
    }

    public function share(User $user, Account $account): bool {
        return $account->users()->where('user_id', $user->id)
            ->where('role', 'owner')->exists();
    }
}

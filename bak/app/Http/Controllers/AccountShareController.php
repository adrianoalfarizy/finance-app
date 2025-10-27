<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;

class AccountShareController extends Controller
{
    public function edit(Account $account)
    {
        $this->authorize('share', $account);

        $shared = $account->users()->withPivot('role')->get();
        return view('accounts.share', compact('account','shared'));
    }

    public function update(Request $request, Account $account)
    {
        $this->authorize('share', $account);

        $data = $request->validate([
            'email'=>'required|email',
            'role'=>'required|in:editor,viewer'
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();
        $account->users()->syncWithoutDetaching([$user->id => ['role'=>$data['role']]]);

        return back()->with('success','Akses diberikan.');
    }

    public function revoke(Request $request, Account $account, User $user)
    {
        $this->authorize('share', $account);
        $account->users()->detach($user->id);
        return back()->with('success','Akses dicabut.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::whereHas('users', fn($q) => $q->where('user_id', Auth::id()))
            ->orderBy('name')->get();

        $totalBalance = $accounts->sum(fn($account) => $account->balance);

        return view('accounts.index', compact('accounts', 'totalBalance'));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:cash,bank,ewallet,saving,debt', // <— tambahkan
            'notes' => 'nullable|string'
        ]);

        $account = Account::create($data);
        $account->users()->attach(Auth::id(), ['role' => 'owner']);

        return redirect()->route('accounts.index')->with('success', 'Akun dibuat.');
    }

    public function edit(Account $account)
    {
        $this->authorize('update', $account);
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, Account $account)
    {
        $this->authorize('update', $account);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:cash,bank,ewallet,saving,debt', // <— tambahkan
            'notes' => 'nullable|string'
        ]);

        $account->update($data);

        return redirect()->route('accounts.index')->with('success', 'Akun diperbarui.');
    }

    public function destroy(Account $account)
    {
        $this->authorize('update', $account);
        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'Akun dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Saving;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavingController extends Controller
{
    public function index(Request $request)
    {
        $accounts = Account::whereHas('users', fn($q)=>$q->where('user_id', Auth::id()))
            ->orderBy('name')->get();

        $accountId = $request->get('account_id', optional($accounts->first())->id);
        $active = $accountId ? $accounts->firstWhere('id', $accountId) : null;

        $savings = collect();
        if ($active) {
            $savings = $active->savings()->with('entries')->orderBy('name')->get();
        }

        return view('savings.index', compact('accounts','active','savings'));
    }

    public function create()
    {
        $accounts = Account::whereHas('users', fn($q)=>$q->where('user_id', Auth::id()))
            ->orderBy('name')->get();
        return view('savings.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_id'=>'required|exists:accounts,id',
            'name'=>'required|string|max:120',
            'target_amount'=>'nullable|numeric|min:0'
        ]);

        $acc = Account::findOrFail($data['account_id']);
        $this->authorize('update', $acc);

        Saving::create([
            'account_id'=>$acc->id,
            'name'=>$data['name'],
            'target_amount'=>$data['target_amount'] ?? 0,
        ]);

        return redirect()->route('savings.index', ['account_id'=>$acc->id])->with('success','Tabungan dibuat.');
    }
}
    
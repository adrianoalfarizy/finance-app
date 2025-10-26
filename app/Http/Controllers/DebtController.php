<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $accounts = Account::whereHas('users', fn($q)=>$q->where('user_id', Auth::id()))
            ->orderBy('name')->get();
        $accountId = $request->get('account_id', optional($accounts->first())->id);
        $active = $accountId ? $accounts->firstWhere('id', $accountId) : null;

        $debts = collect();
        if ($active) {
            $debts = $active->debts()->with('payments')->latest()->get();
        }

        return view('debts.index', compact('accounts','active','debts'));
    }

    public function create()
    {
        $accounts = Account::whereHas('users', fn($q)=>$q->where('user_id', Auth::id()))
            ->orderBy('name')->get();
        return view('debts.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_id'=>'required|exists:accounts,id',
            'creditor_name'=>'required|string|max:120',
            'principal_amount'=>'required|numeric|min:0.01',
            'interest_rate'=>'nullable|numeric|min:0',
            'start_date'=>'nullable|date',
            'due_date'=>'nullable|date|after_or_equal:start_date',
            'note'=>'nullable|string'
        ]);

        $acc = Account::findOrFail($data['account_id']);
        $this->authorize('update', $acc);

        Debt::create($data + ['status'=>'ongoing']);
        return redirect()->route('debts.index',['account_id'=>$acc->id])->with('success','Hutang dicatat.');
    }
}

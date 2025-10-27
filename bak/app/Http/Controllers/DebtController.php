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
        // Dropdown filter atas: hanya akun bertipe debt
        $accounts = \App\Models\Account::whereHas('users', fn($q) => $q->where('user_id', \Auth::id()))
            ->where('type', 'debt')->orderBy('name')->get();

        $accountId = $request->get('account_id', optional($accounts->first())->id);
        $active = $accountId ? $accounts->firstWhere('id', $accountId) : null;

        $debts = $active ? $active->debts()->with('payments')->latest()->get() : collect();

        // Akun untuk membayar hutang: NON-debt → saldo akan berkurang
        $spendableAccounts = \App\Models\Account::whereHas('users', fn($q) => $q->where('user_id', \Auth::id()))
            ->whereIn('type', ['cash', 'bank', 'ewallet', 'saving']) // <— TIDAK termasuk 'debt'
            ->orderBy('name')->get();

        return view('debts.index', compact('accounts', 'active', 'debts', 'spendableAccounts'));
    }




    public function create()
    {
        $accounts = Account::whereHas('users', fn($q) => $q->where('user_id', Auth::id()))
            ->where('type', 'debt')
            ->orderBy('name')->get();
        return view('debts.create', compact('accounts'));
    }


    public function store(Request $request)
    {
        $request->merge([
            'amount' => $this->normalizeCurrency($request->input('amount')),
        ]); 
        $data = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'creditor_name' => 'required|string|max:120',
            'principal_amount' => 'required|numeric|min:0.01',
            'interest_rate' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'note' => 'nullable|string'
        ]);

        $acc = Account::findOrFail($data['account_id']);
        $this->authorize('update', $acc);

        Debt::create($data + ['status' => 'ongoing']);
        return redirect()->route('debts.index', ['account_id' => $acc->id])->with('success', 'Hutang dicatat.');
    }

    private function normalizeCurrency(?string $v): ?string
    {
        if ($v === null)
            return null;
        $v = trim($v);
        // buang semua char kecuali digit, koma, titik, minus
        $v = preg_replace('/[^\d,.\-]/', '', $v);

        if (strpos($v, ',') !== false && strpos($v, '.') !== false) {
            // punya koma & titik -> titik dianggap ribuan, koma = desimal
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        } elseif (strpos($v, ',') !== false) {
            // hanya koma -> koma = desimal
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        } else {
            // hanya titik -> buang pemisah ribu (titik) kecuali terakhir
            $parts = explode('.', $v);
            if (count($parts) > 2) {
                $last = array_pop($parts);
                $v = implode('', $parts) . '.' . $last;
            }
        }
        return $v;
    }

}

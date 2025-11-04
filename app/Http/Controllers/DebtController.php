<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        // Dropdown filter atas: hanya akun bertipe debt
        $accounts = \App\Models\Account::whereHas('users', fn($q) => $q->where('user_id', \Auth::id()))
            ->where('type', 'debt')->orderBy('name')->get();

        $accountId = $request->get('account_id', optional($accounts->first())->id);
        $active = $accountId ? $accounts->firstWhere('id', $accountId) : null;

        $debts = $active ? $active->debts()->where('status', '!=', 'paid')->with('payments')->latest()->get() : collect();
        $summary = [
            'count' => $debts->count(),
            'remaining' => $debts->sum(fn($debt) => $debt->remaining_due),
            'monthly' => $debts->where('repayment_type', 'installment')->sum(fn($debt) => $debt->monthly_payment),
        ];

        // Akun untuk membayar hutang: NON-debt → saldo akan berkurang
        $spendableAccounts = \App\Models\Account::whereHas('users', fn($q) => $q->where('user_id', \Auth::id()))
            ->whereIn('type', ['cash', 'bank', 'ewallet', 'saving']) // <— TIDAK termasuk 'debt'
            ->orderBy('name')->get();

        return view('debts.index', compact('accounts', 'active', 'debts', 'spendableAccounts', 'summary'));
    }

    public function history(Request $request)
    {
        $accounts = \App\Models\Account::whereHas('users', fn($q) => $q->where('user_id', \Auth::id()))
            ->where('type', 'debt')->orderBy('name')->get();

        $accountId = $request->get('account_id', optional($accounts->first())->id);
        $active = $accountId ? $accounts->firstWhere('id', $accountId) : null;

        $debts = $active ? $active->debts()->where('status', 'paid')->with('payments')->latest()->get() : collect();

        $summary = [
            'count' => $debts->count(),
            'total_paid' => $debts->sum(fn($debt) => $debt->paid_amount),
        ];

        return view('debts.history', compact('accounts', 'active', 'debts', 'summary'));
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
        // Normalisasi angka (kalau user ketik 1.000.000)
        $request->merge([
            'principal_amount' => $this->normalizeCurrency($request->input('principal_amount')),
            'interest_rate' => $this->normalizeCurrency($request->input('interest_rate')), // jika user ketik "10" atau "10,5"
            'monthly_payment' => $this->normalizeCurrency($request->input('monthly_payment')),
        ]);

        $data = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'creditor_name' => 'required|string|max:120',
            'principal_amount' => 'required|numeric|min:0.01',
            'interest_rate' => 'nullable|numeric|min:0', // persen (flat) – misal 10 = 10%
            'repayment_type' => ['required', Rule::in(['one_time', 'installment'])],
            'monthly_payment' => ['nullable', 'numeric', 'min:0', Rule::requiredIf(fn () => $request->input('repayment_type') === 'installment')],
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'note' => 'nullable|string'
        ]);

        $data['monthly_payment'] = $data['repayment_type'] === 'installment'
            ? ($data['monthly_payment'] ?? 0)
            : 0;

        $acc = Account::findOrFail($data['account_id']);
        $this->authorize('update', $acc);

        Debt::create($data + ['status' => 'ongoing']);

        return redirect()
            ->route('debts.index', ['account_id' => $acc->id])
            ->with('success', 'Hutang dicatat.');
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

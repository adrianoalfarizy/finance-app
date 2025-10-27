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
        // Hanya akun bertipe 'saving' untuk daftar/dropdown
        $accounts = Account::whereHas('users', fn($q) => $q->where('user_id', Auth::id()))
            ->where('type', 'saving')
            ->orderBy('name')->get();

        $accountId = $request->get('account_id', optional($accounts->first())->id);
        $active = $accountId ? $accounts->firstWhere('id', $accountId) : null;

        $savings = collect();
        if ($active) {
            $savings = $active->savings()->with('entries')->orderBy('name')->get();
        }

        // Akun sumber/tujuan uang (untuk setor/tarik): cash/bank/ewallet/saving
        $spendableAccounts = Account::whereHas('users', fn($q) => $q->where('user_id', Auth::id()))
            ->whereIn('type', ['cash', 'bank', 'ewallet', 'saving'])
            ->orderBy('name')->get();

        return view('savings.index', compact('accounts', 'active', 'savings', 'spendableAccounts'));
    }


    public function create()
    {
        $accounts = Account::whereHas('users', fn($q) => $q->where('user_id', Auth::id()))
            ->where('type', 'saving')
            ->orderBy('name')->get();
        return view('savings.create', compact('accounts'));
    }


    public function store(Request $request)
    {
        $request->merge([
            'amount' => $this->normalizeCurrency($request->input('amount')),
        ]);
        $data = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'name' => 'required|string|max:120',
            'target_amount' => 'nullable|numeric|min:0'
        ]);

        $acc = Account::findOrFail($data['account_id']);
        $this->authorize('update', $acc);

        Saving::create([
            'account_id' => $acc->id,
            'name' => $data['name'],
            'target_amount' => $data['target_amount'] ?? 0,
        ]);

        return redirect()->route('savings.index', ['account_id' => $acc->id])->with('success', 'Tabungan dibuat.');
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

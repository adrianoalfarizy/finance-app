<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\SavingEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;


class SavingEntryController extends Controller
{

    public function store(Request $request, Saving $saving)
    {
        $this->authorize('update', $saving->account);

        $request->merge([
            'amount' => $this->normalizeCurrency($request->input('amount')),
        ]);

        $data = $request->validate([
            'type' => 'required|in:deposit,withdraw',
            'amount' => 'required|numeric|min:0.01',
            'transacted_at' => 'required|date',
            'note' => 'nullable|string|max:255',
            'transfer_account_id' => 'required|exists:accounts,id' // akun lawan transaksi
        ]);

        $other = Account::findOrFail($data['transfer_account_id']);

        // Pastikan user punya hak pada akun lawan
        $this->authorize('update', $other);

        // 1) Simpan entry (riwayat tabungan)
        \App\Models\SavingEntry::create(array_merge($data, ['saving_id' => $saving->id]));

        // 2) Buat transaksi berpasangan (seperti transfer)
        $gid = (string) Str::uuid();
        $savingAccount = $saving->account; // akun tipe 'saving' pemilik tabungan

        if ($data['type'] === 'deposit') {
            // other -> expense, savingAccount -> income
            Transaction::create([
                'account_id' => $other->id,
                'user_id' => Auth::id(),
                'type' => 'expense',
                'group_id' => $gid,
                'category_id' => null,
                'amount' => $data['amount'],
                'transacted_at' => $data['transacted_at'],
                'description' => 'Setor ke tabungan: ' . $saving->name,
            ]);

            Transaction::create([
                'account_id' => $savingAccount->id,
                'user_id' => Auth::id(),
                'type' => 'income',
                'group_id' => $gid,
                'category_id' => null,
                'amount' => $data['amount'],
                'transacted_at' => $data['transacted_at'],
                'description' => 'Setor dari akun: ' . $other->name . ' (' . $saving->name . ')',
            ]);
        } else {
            // withdraw: savingAccount -> expense, other -> income
            Transaction::create([
                'account_id' => $savingAccount->id,
                'user_id' => Auth::id(),
                'type' => 'expense',
                'group_id' => $gid,
                'category_id' => null,
                'amount' => $data['amount'],
                'transacted_at' => $data['transacted_at'],
                'description' => 'Tarik ke akun: ' . $other->name . ' (' . $saving->name . ')',
            ]);

            Transaction::create([
                'account_id' => $other->id,
                'user_id' => Auth::id(),
                'type' => 'income',
                'group_id' => $gid,
                'category_id' => null,
                'amount' => $data['amount'],
                'transacted_at' => $data['transacted_at'],
                'description' => 'Tarik dari tabungan: ' . $saving->name,
            ]);
        }

        return back()->with('success', 'Entri tabungan & pergerakan saldo tercatat.');
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

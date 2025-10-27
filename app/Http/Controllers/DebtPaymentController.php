<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebtPaymentController extends Controller
{
    public function store(Request $request, Debt $debt)
    {
        $request->merge([
            'amount' => $this->normalizeCurrency($request->input('amount')),
        ]);
        $data = $request->validate([
            'pay_account_id' => 'required|exists:accounts,id', // akun yang DIPAKAI membayar
            'amount' => 'required|numeric|min:0.01',
            'transacted_at' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        $payAccount = Account::findOrFail($data['pay_account_id']);
        $this->authorize('update', $payAccount);

        // Pastikan akun pembayaran BUKAN tipe debt
        if ($payAccount->type === 'debt') {
            return back()->withErrors('Akun pembayaran tidak boleh bertipe Hutang.');
        }

        // 1) Catat riwayat pembayaran hutang
        $payment = DebtPayment::create([
            'debt_id' => $debt->id,
            'account_id' => $payAccount->id, // simpan referensi akun yang dipakai membayar
            'amount' => $data['amount'],
            'transacted_at' => $data['transacted_at'],
            'note' => $data['note'] ?? null,
        ]);

        // 2) Buat transaksi EXPENSE pada akun pembayaran → saldo akun berkurang
        Transaction::create([
            'account_id' => $payAccount->id,
            'user_id' => Auth::id(),          // atau auth()->id()
            'type' => 'expense',
            'group_id' => null,
            'category_id' => null,                // boleh diisi kategori "Pembayaran Hutang" jika ada
            'amount' => $data['amount'],
            'transacted_at' => $data['transacted_at'],
            'description' => 'Pembayaran hutang: ' . $debt->creditor_name,
        ]);

        // 3) Opsi: tandai lunas jika total pembayaran >= pokok
// ... setelah membuat Transaction expense

        // Tandai lunas jika total pembayaran >= total tagihan (pokok + bunga)
// Tandai lunas jika total pembayaran >= total tagihan (pokok + bunga)
        $debt->refresh(); // ambil nilai terbaru
        $paid = $debt->payments()->sum('amount');
        if ($paid >= (float) $debt->total_due && $debt->status !== 'paid') {
            $debt->update(['status' => 'paid']);
        }



        return back()->with('success', 'Pembayaran dicatat. Saldo akun pembayaran berkurang.');
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

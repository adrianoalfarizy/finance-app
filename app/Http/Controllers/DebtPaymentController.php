<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Debt;
use App\Services\DebtPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class DebtPaymentController extends Controller
{
    public function __construct(private DebtPaymentService $service)
    {
    }

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

        try {
            $this->service->record(
                $debt,
                $payAccount,
                (float) $data['amount'],
                $data['transacted_at'],
                $data['note'] ?? null,
                Auth::id()
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors($e->getMessage());
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

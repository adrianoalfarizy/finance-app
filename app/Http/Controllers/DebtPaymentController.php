<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Debt;
use App\Models\DebtPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebtPaymentController extends Controller
{
    public function store(Request $request, Debt $debt)
    {
        $data = $request->validate([
            'account_id'=>'required|exists:accounts,id', // bisa pilih "dana keluar dari akun siapa"
            'amount'=>'required|numeric|min:0.01',
            'transacted_at'=>'required|date',
            'note'=>'nullable|string|max:255',
        ]);

        $payAccount = Account::findOrFail($data['account_id']);
        $this->authorize('update', $payAccount);

        DebtPayment::create([
            'debt_id'=>$debt->id,
            'account_id'=>$payAccount->id,
            'amount'=>$data['amount'],
            'transacted_at'=>$data['transacted_at'],
            'note'=>$data['note'] ?? null
        ]);

        // Opsional: ubah status jika lunas
        $paid = $debt->payments()->sum('amount');
        if ($paid >= (float)$debt->principal_amount) {
            $debt->update(['status'=>'paid']);
        }

        return back()->with('success','Pembayaran dicatat.');
    }
}

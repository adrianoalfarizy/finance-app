<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\SavingEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        // daftar akun yg bisa diakses
        $accounts = Account::whereHas('users', fn($q)=>$q->where('user_id',$user->id))
            ->orderBy('name')->get();

        // pilih akun aktif (dropdown "akun milik siapa")
        $accountId = $request->get('account_id', optional($accounts->first())->id);
        $active = $accountId ? $accounts->firstWhere('id', $accountId) : null;

        $stats = null;
        if ($active) {
            $income = $active->transactions()->where('type','income')->sum('amount');
            $expense = $active->transactions()->where('type','expense')->sum('amount');

            $savingIds = $active->savings()->pluck('id');
            $savingsTotal = 0.0;
            if ($savingIds->isNotEmpty()) {
                $deposit = SavingEntry::whereIn('saving_id', $savingIds)->where('type', 'deposit')->sum('amount');
                $withdraw = SavingEntry::whereIn('saving_id', $savingIds)->where('type', 'withdraw')->sum('amount');
                $savingsTotal = (float) $deposit - (float) $withdraw;
            }

            $debtsTotal = $active->debts()
                ->where('status', '!=', 'paid')
                ->get()
                ->sum(fn ($debt) => $debt->remaining_due);

            $stats = [
                'balance' => (float)$income - (float)$expense,
                'income'  => (float)$income,
                'expense' => (float)$expense,
                'savings_total' => $savingsTotal,
                'debts_total' => (float) $debtsTotal,
                'recent'  => $active->transactions()->latest('transacted_at')->limit(10)->get(),
            ];
        }

        return view('dashboard', compact('accounts','active','stats'));
    }
}

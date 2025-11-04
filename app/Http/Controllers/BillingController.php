<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Debt;
use App\Services\DebtPaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class BillingController extends Controller
{
    public function __construct(private DebtPaymentService $service)
    {
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $payAccounts = Account::whereHas('users', fn($q) => $q->where('user_id', $user->id))
            ->whereIn('type', ['cash', 'bank', 'ewallet', 'saving'])
            ->orderBy('name')
            ->get();

        $debts = Debt::whereHas('account.users', fn($q) => $q->where('user_id', $user->id))
            ->where('status', '!=', 'paid')
            ->where('monthly_payment', '>', 0)
            ->with('account')
            ->withSum(['payments as paid_this_month' => function ($q) use ($start, $end) {
                $q->whereBetween('transacted_at', [$start, $end]);
            }], 'amount')
            ->get()
            ->filter(fn($debt) => $debt->remaining_due > 0)
            ->map(function ($debt) {
                $target = $debt->monthly_payment > 0 ? $debt->monthly_payment : $debt->remaining_due;
                $paid = (float) ($debt->paid_this_month ?? 0);
                $debt->due_amount = min($target, $debt->remaining_due);
                $debt->paid_this_month_amount = $paid;
                $debt->still_due_this_month = max($debt->due_amount - $paid, 0);
                return $debt;
            })
            ->filter(fn($debt) => $debt->still_due_this_month > 0)
            ->values();

        return view('bills.index', [
            'debts' => $debts,
            'payAccounts' => $payAccounts,
            'monthLabel' => $start->translatedFormat('F Y'),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        /** @var Collection<int, Account> $payAccounts */
        $payAccounts = Account::whereHas('users', fn($q) => $q->where('user_id', $user->id))
            ->whereIn('type', ['cash', 'bank', 'ewallet', 'saving'])
            ->get()
            ->keyBy('id');

        $selected = collect($request->input('debts', []))
            ->filter(fn($row) => !empty($row['pay']));

        if ($selected->isEmpty()) {
            return back()->withErrors('Tidak ada tagihan yang dipilih.')->withInput();
        }

        $errors = [];
        $processed = 0;

        foreach ($selected as $debtId => $row) {
            $debt = Debt::whereHas('account.users', fn($q) => $q->where('user_id', $user->id))
                ->where('id', $debtId)
                ->where('status', '!=', 'paid')
                ->first();

            if (!$debt || $debt->monthly_payment <= 0 || $debt->remaining_due <= 0) {
                continue;
            }

            $payAccountId = (int) ($row['pay_account_id'] ?? 0);
            $payAccount = $payAccounts->get($payAccountId);
            if (!$payAccount) {
                $errors[] = 'Akun pembayaran belum dipilih atau tidak valid untuk hutang ' . $debt->creditor_name . '.';
                continue;
            }

            $this->authorize('update', $payAccount);

            $paidThisMonth = $debt->payments()
                ->whereBetween('transacted_at', [$start, $end])
                ->sum('amount');

            $target = $debt->monthly_payment > 0 ? $debt->monthly_payment : $debt->remaining_due;
            $amount = min(max($target - $paidThisMonth, 0), $debt->remaining_due);

            if ($amount <= 0) {
                continue;
            }

            try {
                $this->service->record(
                    $debt,
                    $payAccount,
                    (float) $amount,
                    Carbon::now(),
                    'Pembayaran tagihan ' . $start->translatedFormat('F Y'),
                    $user->id
                );
                $processed++;
            } catch (InvalidArgumentException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        if ($processed === 0) {
            return back()->withErrors('Tidak ada tagihan yang dapat diproses.')->withInput();
        }

        return redirect()->route('bills.index')
            ->with('success', "{$processed} tagihan terbayar.");
    }
}

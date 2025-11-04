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
            ->with('account')
            ->withSum(['payments as paid_this_month' => function ($q) use ($start, $end) {
                $q->whereBetween('transacted_at', [$start, $end]);
            }], 'amount')
            ->get()
            ->filter(fn($debt) => $debt->remaining_due > 0)
            ->map(function ($debt) {
                $paidThisMonth = (float) ($debt->paid_this_month ?? 0);

                if ($debt->repayment_type === 'installment') {
                    $target = $debt->monthly_payment > 0 ? $debt->monthly_payment : $debt->remaining_due;
                    $dueAmount = min($target, $debt->remaining_due);
                    $stillDue = max($dueAmount - $paidThisMonth, 0);
                } else {
                    $dueAmount = min((float) $debt->principal_amount, $debt->remaining_due);
                    $stillDue = max(min($dueAmount, $debt->remaining_due) - $paidThisMonth, 0);
                }

                $debt->due_amount = $dueAmount;
                $debt->paid_this_month_amount = $paidThisMonth;
                $debt->still_due_this_month = $stillDue;

                return $debt;
            })
            ->filter(fn($debt) => $debt->still_due_this_month > 0)
            ->values();

        $totalDue = $debts->sum(fn($debt) => $debt->still_due_this_month);

        return view('bills.index', [
            'debts' => $debts,
            'payAccounts' => $payAccounts,
            'monthLabel' => $start->translatedFormat('F Y'),
            'totalDue' => $totalDue,
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

            if (!$debt || $debt->remaining_due <= 0) {
                continue;
            }

            $payAccountId = (int) ($row['pay_account_id'] ?? 0);
            $payAccount = $payAccounts->get($payAccountId);
            if (!$payAccount) {
                $errors[] = 'Akun pembayaran belum dipilih atau tidak valid untuk hutang ' . $debt->creditor_name . '.';
                continue;
            }

            $this->authorize('update', $payAccount);

            if ($debt->repayment_type === 'installment') {
                $paidThisMonth = $debt->payments()
                    ->whereBetween('transacted_at', [$start, $end])
                    ->sum('amount');

                $target = $debt->monthly_payment > 0 ? $debt->monthly_payment : $debt->remaining_due;
                $amount = min(max($target - $paidThisMonth, 0), $debt->remaining_due);

                if ($amount <= 0) {
                    continue;
                }
            } else {
                $totalPaid = $debt->payments()->sum('amount');
                $remainingPrincipal = max((float) $debt->principal_amount - (float) $totalPaid, 0.0);
                $amount = min($debt->remaining_due, $remainingPrincipal ?: $debt->remaining_due);

                if ($amount <= 0) {
                    continue;
                }
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

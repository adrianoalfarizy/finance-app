<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Transaction;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DebtPaymentService
{
    /**
     * Record a debt payment and the matching expense transaction.
     */
    public function record(
        Debt $debt,
        Account $payAccount,
        float $amount,
        CarbonInterface|string $transactedAt,
        ?string $note,
        int $userId
    ): DebtPayment {
        if ($payAccount->type === 'debt') {
            throw new InvalidArgumentException('Akun pembayaran tidak boleh bertipe Hutang.');
        }

        $when = $transactedAt instanceof CarbonInterface ? $transactedAt : Carbon::parse($transactedAt);

        return DB::transaction(function () use ($debt, $payAccount, $amount, $when, $note, $userId) {
            $payment = DebtPayment::create([
                'debt_id' => $debt->id,
                'account_id' => $payAccount->id,
                'amount' => $amount,
                'transacted_at' => $when,
                'note' => $note,
            ]);

            Transaction::create([
                'account_id' => $payAccount->id,
                'user_id' => $userId,
                'type' => 'expense',
                'group_id' => null,
                'category_id' => null,
                'amount' => $amount,
                'transacted_at' => $when,
                'description' => 'Pembayaran hutang: ' . $debt->creditor_name,
            ]);

            $debt->refresh();
            $paid = $debt->payments()->sum('amount');
            if ($paid >= (float) $debt->total_due && $debt->status !== 'paid') {
                $debt->update(['status' => 'paid']);
            }

            return $payment;
        });
    }
}

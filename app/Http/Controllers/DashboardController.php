<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
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

            $categoryBreakdown = $active->transactions()
                ->whereNotNull('category_id')
                ->selectRaw('type, category_id, SUM(amount) as total')
                ->groupBy('type', 'category_id')
                ->get();

            $categoryIds = $categoryBreakdown->pluck('category_id')->unique();
            $categoryModels = Category::whereIn('id', $categoryIds)
                ->orderBy('kind')
                ->orderBy('name')
                ->get()
                ->keyBy('id');

            $grouped = [
                'income' => [],
                'expense' => [],
            ];

            foreach ($categoryBreakdown as $row) {
                $category = $categoryModels->get($row->category_id);
                if (!$category) {
                    continue;
                }
                $grouped[$row->type][] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'total' => (float) $row->total,
                    'type' => $row->type,
                ];
            }

            foreach ($grouped as $type => $list) {
                usort($list, fn($a, $b) => $b['total'] <=> $a['total']);
                $grouped[$type] = $list;
            }

            $filterOptions = [];
            if (!empty($grouped['income'])) {
                $filterOptions['income'] = 'Semua pemasukan';
                foreach ($grouped['income'] as $entry) {
                    $filterOptions['category_' . $entry['id']] = '[IN] ' . $entry['name'];
                }
            }
            if (!empty($grouped['expense'])) {
                $filterOptions['expense'] = 'Semua pengeluaran';
                foreach ($grouped['expense'] as $entry) {
                    $filterOptions['category_' . $entry['id']] = '[OUT] ' . $entry['name'];
                }
            }

            $filter = $request->get('category_filter');
            if (!$filter || !array_key_exists($filter, $filterOptions)) {
                $filter = array_key_first($filterOptions ?? []) ?? null;
            }

            $summaryTitle = null;
            $summaryList = [];
            if ($filter === 'income') {
                $summaryTitle = 'Semua kategori pemasukan';
                $summaryList = $grouped['income'];
            } elseif ($filter === 'expense') {
                $summaryTitle = 'Semua kategori pengeluaran';
                $summaryList = $grouped['expense'];
            } elseif ($filter && str_starts_with($filter, 'category_')) {
                $categoryId = (int) str_replace('category_', '', $filter);
                $entry = collect($grouped['income'])->merge($grouped['expense'])
                    ->first(fn($item) => $item['id'] === $categoryId);
                if ($entry) {
                    $summaryTitle = $filterOptions[$filter] ?? $entry['name'];
                    $summaryList = [$entry];
                }
            }

            $stats = [
                'balance' => (float)$income - (float)$expense,
                'income'  => (float)$income,
                'expense' => (float)$expense,
                'savings_total' => $savingsTotal,
                'debts_total' => (float) $debtsTotal,
                'category_options' => $filterOptions,
                'category_filter' => $filter,
                'category_summary' => $summaryList,
                'category_summary_title' => $summaryTitle,
                'recent'  => $active->transactions()->latest('transacted_at')->limit(10)->get(),
            ];
        }

        return view('dashboard', compact('accounts','active','stats'));
    }
}

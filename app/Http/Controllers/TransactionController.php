<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class TransactionController extends Controller
{
    public function index(Request $request)
{
    $accounts = Account::whereHas('users', fn($q)=>$q->where('user_id', Auth::id()))
        ->orderBy('name')->get();

    $accountId = $request->get('account_id', optional($accounts->first())->id);
    $active = $accountId ? $accounts->firstWhere('id', $accountId) : null;

    if ($active) {
        $transactions = $active->transactions()
            ->latest('transacted_at')
            ->paginate(20)
            ->withQueryString(); // <- ini Paginator (bukan Collection)
    } else {
        // buat paginator kosong agar view selalu bisa memanggil ->links()
        $transactions = new LengthAwarePaginator(
            [], // items
            0,  // total
            20, // perPage
            Paginator::resolveCurrentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    return view('transactions.index', compact('accounts','active','transactions'));
}

    public function create()
    {
        $accounts = Account::whereHas('users', fn($q)=>$q->where('user_id', Auth::id()))
            ->orderBy('name')->get();
        $income = Category::where('kind','income')->orderBy('name')->get();
        $expense = Category::where('kind','expense')->orderBy('name')->get();
        return view('transactions.create', compact('accounts','income','expense'));
    }

    public function store(Request $request)
    {
        // Mode: income / expense / transfer
        $mode = $request->get('mode');

        if ($mode === 'transfer') {
            $data = $request->validate([
                'source_account_id'=>'required|exists:accounts,id',
                'destination_account_id'=>'required|different:source_account_id|exists:accounts,id',
                'amount'=>'required|numeric|min:0.01',
                'transacted_at'=>'required|date',
                'description'=>'nullable|string|max:255'
            ]);

            // Cek hak akses
            $source = Account::findOrFail($data['source_account_id']);
            $dest   = Account::findOrFail($data['destination_account_id']);
            $this->authorize('update', $source);
            $this->authorize('view', $dest);

            $gid = (string) Str::uuid();

            // 1) expense di sumber
            Transaction::create([
                'account_id'=>$source->id,
                'user_id'=>Auth::id(),
                'type'=>'expense',
                'group_id'=>$gid,
                'category_id'=>null,
                'amount'=>$data['amount'],
                'transacted_at'=>$data['transacted_at'],
                'description'=>$data['description'] ?? 'Transfer ke '.$dest->name,
            ]);

            // 2) income di tujuan
            Transaction::create([
                'account_id'=>$dest->id,
                'user_id'=>Auth::id(),
                'type'=>'income',
                'group_id'=>$gid,
                'category_id'=>null,
                'amount'=>$data['amount'],
                'transacted_at'=>$data['transacted_at'],
                'description'=>$data['description'] ?? 'Transfer dari '.$source->name,
            ]);

            return redirect()->route('transactions.index', ['account_id'=>$source->id])
                ->with('success','Transfer berhasil.');
        }

        // income/expense biasa
        $data = $request->validate([
            'account_id'=>'required|exists:accounts,id',
            'type'=>'required|in:income,expense',
            'category_id'=>'nullable|exists:categories,id',
            'amount'=>'required|numeric|min:0.01',
            'transacted_at'=>'required|date',
            'description'=>'nullable|string|max:255'
        ]);

        $account = Account::findOrFail($data['account_id']);
        $this->authorize('update', $account);

        // Jika jenis income/expense, category_id harus sesuai kind (opsional: validasi lanjutan)
        Transaction::create([
            'account_id'=>$account->id,
            'user_id'=>Auth::id(), // <- mencegah error "user_id doesn't have a default value"
            'type'=>$data['type'],
            'group_id'=>null,
            'category_id'=>$data['category_id'] ?? null,
            'amount'=>$data['amount'],
            'transacted_at'=>$data['transacted_at'],
            'description'=>$data['description'] ?? null,
        ]);

        return redirect()->route('transactions.index', ['account_id'=>$account->id])
            ->with('success','Transaksi tersimpan.');
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('update', $transaction->account);

        // Jika bagian dari transfer, hapus pasangan satu group_id
        if ($transaction->group_id) {
            Transaction::where('group_id', $transaction->group_id)->delete();
        } else {
            $transaction->delete();
        }

        return back()->with('success','Transaksi dihapus.');
    }
}

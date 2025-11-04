<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Transaksi</h2></x-slot>

    <div class="p-4 space-y-3">
        <form class="flex gap-2" method="get" action="{{ route('transactions.index') }}">
            <select name="account_id" class="w-full border rounded p-2">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" @selected(optional($active)->id==$acc->id)>{{ $acc->name }}</option>
                @endforeach
            </select>
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Pilih</button>
        </form>

        <a href="{{ route('transactions.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded block text-center">+ Tambah</a>

        <div class="glass-panel-light rounded-xl shadow divide-y divide-white/10 text-white">
            @forelse($transactions as $t)
                <div class="p-3 flex justify-between items-center">
                    <div>
                        <div class="text-sm text-white">{{ $t->description ?? '-' }}</div>
                        <div class="text-xs text-white/60">{{ $t->transacted_at->format('d M Y H:i') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold {{ $t->type==='income'?'text-green-600':'text-red-600' }}">
                            {{ $t->type==='income' ? '+' : '-' }}Rp {{ number_format($t->amount,0,',','.') }}
                        </div>
                        <form method="post" action="{{ route('transactions.destroy',$t) }}" onsubmit="return confirm('Hapus transaksi?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-600">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-3 text-sm text-white/70">Belum ada transaksi.</div>
            @endforelse
        </div>

        <div>{{ $transactions->links() }}</div>

        @php
            $summaryItems = [
                ['label' => 'Total pemasukan', 'value' => 'Rp ' . number_format($summary['income'], 0, ',', '.'), 'accent' => 'text-green-600'],
                ['label' => 'Total pengeluaran', 'value' => 'Rp ' . number_format($summary['expense'], 0, ',', '.'), 'accent' => 'text-red-600'],
                ['label' => 'Selisih', 'value' => 'Rp ' . number_format($summary['balance'], 0, ',', '.'), 'accent' => ''],
            ];
        @endphp
        <div class="summary-footer-placeholder h-40 sm:h-32"></div>
        @include('partials.summary-footer', ['items' => $summaryItems])
    </div>
</x-app-layout>

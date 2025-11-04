<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg">Dashboard</h2>
    </x-slot>

    <div class="p-4 space-y-4">
        <form class="flex gap-2" method="get" action="{{ route('dashboard') }}">
            <select name="account_id" class="w-full border rounded p-2">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" @selected(optional($active)->id == $acc->id)>{{ $acc->name }} ({{ strtoupper($acc->type) }})</option>
                @endforeach
            </select>
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Pilih</button>
        </form>

        @if($active)
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-center text-white">
            <div class="glass-panel-light rounded-xl p-4 shadow">
                <div class="text-xs text-white/70">Saldo</div>
                <div class="text-xl font-bold">Rp {{ number_format($stats['balance'],0,',','.') }}</div>
            </div>
            <div class="glass-panel-light rounded-xl p-4 shadow">
                <div class="text-xs text-white/70">Pemasukan</div>
                <div class="text-lg font-semibold text-green-600">Rp {{ number_format($stats['income'],0,',','.') }}</div>
            </div>
            <div class="glass-panel-light rounded-xl p-4 shadow">
                <div class="text-xs text-white/70">Pengeluaran</div>
                <div class="text-lg font-semibold text-red-600">Rp {{ number_format($stats['expense'],0,',','.') }}</div>
            </div>
            <div class="glass-panel-light rounded-xl p-4 shadow">
                <div class="text-xs text-white/70">Total Tabungan</div>
                <div class="text-lg font-semibold text-blue-600">Rp {{ number_format($stats['savings_total'],0,',','.') }}</div>
            </div>
            <div class="glass-panel-light rounded-xl p-4 shadow">
                <div class="text-xs text-white/70">Sisa Hutang</div>
                <div class="text-lg font-semibold text-amber-600">Rp {{ number_format($stats['debts_total'],0,',','.') }}</div>
            </div>
        </div>

        @php
            $hasCategorySummary = !empty($stats['category_options']);
        @endphp

        @if($hasCategorySummary)
        <div class="glass-panel-light rounded-xl p-4 shadow space-y-3 text-white">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <div class="font-semibold">Ringkasan Kategori</div>
                    @if($stats['category_summary_title'])
                        <div class="text-xs text-white/60">{{ $stats['category_summary_title'] }}</div>
                    @endif
                </div>
                <form method="get" action="{{ route('dashboard') }}" class="flex gap-2">
                    <input type="hidden" name="account_id" value="{{ $active->id }}">
                    <select name="category_filter" class="border border-white/20 rounded bg-white/10 text-sm text-white p-2 focus:outline-none focus:border-white/40">
                        @foreach($stats['category_options'] as $value => $label)
                            <option value="{{ $value }}" @selected($stats['category_filter'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="px-3 py-2 bg-white/15 text-white rounded border border-white/20">Terapkan</button>
                </form>
            </div>
            <ul class="space-y-1 text-sm">
                @forelse($stats['category_summary'] as $row)
                    <li class="flex justify-between">
                        <span class="truncate">{{ $row['name'] }}</span>
                        <span class="font-semibold {{ $row['type'] === 'income' ? 'text-green-600' : 'text-red-600' }}">Rp {{ number_format($row['total'],0,',','.') }}</span>
                    </li>
                @empty
                    <li class="text-white/60">Belum ada data untuk pilihan ini.</li>
                @endforelse
            </ul>
        </div>
        @endif

        <div class="glass-panel-light rounded-xl p-4 shadow text-white">
            <div class="font-semibold mb-2">Transaksi Terbaru</div>
            <ul class="divide-y">
                @forelse($stats['recent'] as $t)
                    <li class="py-2 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-white">{{ $t->description ?? '-' }}</div>
                            <div class="text-xs text-white/60">{{ $t->transacted_at->format('d M Y H:i') }}</div>
                        </div>
                        <div class="text-sm font-semibold {{ $t->type==='income'?'text-green-600':'text-red-600' }}">
                            {{ $t->type==='income' ? '+' : '-' }}Rp {{ number_format($t->amount,0,',','.') }}
                        </div>
                    </li>
                @empty
                    <li class="py-2 text-sm text-white/70">Belum ada transaksi.</li>
                @endforelse
            </ul>
        </div>

        @php
            $summaryItems = [
                ['label' => 'Total pemasukan', 'value' => 'Rp ' . number_format($stats['income'], 0, ',', '.'), 'accent' => 'text-green-600'],
                ['label' => 'Total pengeluaran', 'value' => 'Rp ' . number_format($stats['expense'], 0, ',', '.'), 'accent' => 'text-red-600'],
                ['label' => 'Total tabungan', 'value' => 'Rp ' . number_format($stats['savings_total'], 0, ',', '.'), 'accent' => 'text-blue-600'],
                ['label' => 'Sisa hutang', 'value' => 'Rp ' . number_format($stats['debts_total'], 0, ',', '.'), 'accent' => 'text-amber-600'],
            ];
        @endphp
        <div class="summary-footer-placeholder h-40 sm:h-32"></div>
        @include('partials.summary-footer', ['items' => $summaryItems])
        @else
        <div class="text-sm text-white/80">Anda belum memiliki akses ke akun manapun.</div>
        @endif
    </div>

    {{-- Bottom Nav (mobile) --}}
    <div class="fixed bottom-0 inset-x-0 glass-panel-light shadow-sm">
        <div class="grid grid-cols-6 text-center text-xs">
            <a href="{{ route('dashboard') }}" class="p-2 {{ request()->routeIs('dashboard') ? 'text-blue-600' : '' }}">Dashboard</a>
            <a href="{{ route('accounts.index') }}" class="p-2 {{ request()->routeIs('accounts.*') ? 'text-blue-600' : '' }}">Akun</a>
            <a href="{{ route('transactions.index') }}" class="p-2 {{ request()->routeIs('transactions.*') ? 'text-blue-600' : '' }}">Transaksi</a>
            <a href="{{ route('savings.index') }}" class="p-2 {{ request()->routeIs('savings.*') ? 'text-blue-600' : '' }}">Tabungan</a>
            <a href="{{ route('bills.index') }}" class="p-2 {{ request()->routeIs('bills.*') ? 'text-blue-600' : '' }}">Tagihan</a>
            <a href="{{ route('debts.index') }}" class="p-2 {{ request()->routeIs('debts.*') ? 'text-blue-600' : '' }}">Hutang</a>

        </div>
    </div>
</x-app-layout>

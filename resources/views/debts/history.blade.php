<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg">Riwayat Hutang Lunas</h2>
    </x-slot>

    <div class="p-4 space-y-4 pb-16">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <form class="grid grid-cols-12 gap-2 sm:flex sm:gap-2" method="get" action="{{ route('debts.history') }}">
                <select name="account_id" class="col-span-9 sm:w-64 border rounded-lg p-2 h-10">
                    @forelse($accounts as $acc)
                        <option value="{{ $acc->id }}" @selected(optional($active)->id == $acc->id)>{{ $acc->name }}</option>
                    @empty
                        <option disabled>Belum ada akun bertipe Hutang</option>
                    @endforelse
                </select>
                <button class="col-span-3 px-3 h-10 bg-blue-600 text-white rounded-lg">Pilih</button>
            </form>
            <a href="{{ route('debts.index', ['account_id' => optional($active)->id]) }}" class="px-3 py-2 bg-white/15 text-white rounded-lg border border-white/20 text-sm">&larr; Kembali ke hutang berjalan</a>
        </div>

        @forelse($debts as $d)
            <div class="glass-panel-light rounded-2xl shadow p-4 space-y-3 text-white">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ $d->creditor_name }}</div>
                        <div class="text-xs text-green-300">Lunas • {{ optional($d->payments->sortByDesc('transacted_at')->first())->transacted_at?->format('d M Y') }}</div>
                    </div>
                    <div class="text-right text-sm shrink-0 space-y-1">
                        <div>Pokok: Rp {{ number_format($d->principal_amount, 0, ',', '.') }}</div>
                        <div>Bunga: Rp {{ number_format($d->interest_amount, 0, ',', '.') }}</div>
                        <div>Total dibayar: <span class="font-semibold">Rp {{ number_format($d->paid_amount, 0, ',', '.') }}</span></div>
                    </div>
                </div>

                <div class="text-sm text-white/70">
                    <div>Mulai: {{ $d->start_date ? \Illuminate\Support\Carbon::parse($d->start_date)->translatedFormat('d M Y') : '-' }}</div>
                    <div>Jatuh tempo: {{ $d->due_date ? \Illuminate\Support\Carbon::parse($d->due_date)->translatedFormat('d M Y') : '-' }}</div>
                </div>
            </div>
        @empty
            <div class="glass-panel-light rounded-xl shadow p-4 text-sm text-white/70">Belum ada hutang yang ditandai lunas.</div>
        @endforelse

        @php
            $summaryItems = [
                ['label' => 'Total hutang lunas', 'value' => (string) $summary['count'], 'accent' => ''],
                ['label' => 'Total pembayaran', 'value' => 'Rp ' . number_format($summary['total_paid'], 0, ',', '.'), 'accent' => 'text-green-600'],
            ];
        @endphp
        <div class="summary-footer-placeholder h-36 sm:h-28"></div>
        @include('partials.summary-footer', ['items' => $summaryItems])
    </div>
</x-app-layout>

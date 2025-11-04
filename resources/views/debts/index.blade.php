<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg">Hutang</h2>
    </x-slot>

    <div class="p-4 space-y-4 pb-16">
        {{-- Filter akun (mobile-first) --}}
        <form class="grid grid-cols-12 gap-2" method="get" action="{{ route('debts.index') }}">
            <select name="account_id" class="col-span-9 sm:col-span-10 border rounded-lg p-2 h-10 w-full">
                @forelse($accounts as $acc) {{-- sudah debt-only dari controller --}}
                    <option value="{{ $acc->id }}" @selected(optional($active)->id == $acc->id)>{{ $acc->name }}</option>
                @empty
                    <option disabled>Belum ada akun bertipe Hutang</option>
                @endforelse
            </select>
            <button class="col-span-3 sm:col-span-2 px-3 h-10 bg-blue-600 text-white rounded-lg w-full">Pilih</button>
        </form>

        {{-- Tampilkan CTA jika belum ada akun debt --}}
        @if($accounts->isEmpty())
            <div class="text-sm text-white/80">
                Anda belum memiliki akun bertipe <strong>Hutang</strong>.
                <a href="{{ route('accounts.create', ['type' => 'debt']) }}" class="text-blue-600 underline">+ Buat Akun
                    Hutang</a>
            </div>
        @endif


        <a href="{{ route('debts.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded-lg block text-center">+
            Catat Hutang</a>

        <a href="{{ route('debts.history', ['account_id' => optional($active)->id]) }}" class="px-3 py-2 bg-white/15 text-white rounded-lg block text-center border border-white/20 text-sm">Lihat hutang yang sudah lunas &rarr;</a>

        @if($debts->isEmpty())
            <div class="glass-panel-light rounded-xl shadow p-4 text-sm text-white/70">Tidak ada hutang berjalan.</div>
        @endif

        @foreach($debts as $d)
            <div class="glass-panel-light rounded-2xl shadow p-4 space-y-3 overflow-hidden text-white">
                {{-- Header kartu --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ $d->creditor_name }}</div>
                        <div class="text-xs text-white/60">
                            Status: {{ strtoupper($d->status) }} •
                            @if($d->repayment_type === 'installment')
                                Angsuran
                            @else
                                Sekali bayar
                            @endif
                        </div>
                    </div>
                    <div class="text-right text-sm shrink-0 space-y-1">
                        <div>Pokok: Rp {{ number_format($d->principal_amount, 0, ',', '.') }}</div>
                        <div>Bunga ({{ rtrim(rtrim(number_format($d->interest_rate ?? 0, 2, ',', '.'), '0'), ',') }}%):
                            Rp {{ number_format($d->interest_amount, 0, ',', '.') }}</div>
                        <div>Total: <span class="font-semibold text-blue-200">Rp {{ number_format($d->total_due, 0, ',', '.') }}</span></div>
                        @if($d->monthly_payment > 0)
                            <div>Angsuran bulanan: Rp {{ number_format($d->monthly_payment, 0, ',', '.') }}</div>
                        @endif
                        <div>Terbayar: Rp {{ number_format($d->paid_amount, 0, ',', '.') }}</div>
                        <div>Sisa: <span class="font-semibold">Rp {{ number_format($d->remaining_due, 0, ',', '.') }}</span>
                        </div>
                    </div>


                </div>

                {{-- Form bayar angsuran (grid responsif, anti-overflow) --}}
                <form method="post" action="{{ route('debts.payments.store', $d) }}"
                    class="grid grid-cols-1 md:grid-cols-5 gap-2">
                    @csrf

                    {{-- Akun yang digunakan membayar hutang (non-debt) --}}
                    <select name="pay_account_id" class="border rounded-lg p-2 h-10 w-full md:col-span-2"
                        aria-label="Akun pembayaran">
                        @foreach($spendableAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                        @endforeach
                    </select>


                    {{-- Jumlah --}}
                    <input name="amount" type="text" step="0.01" min="0"
                        class="border rounded-lg p-2 h-10 w-full md:col-span-1 js-currency" placeholder="Jumlah angsuran"
                        aria-label="Jumlah angsuran" inputmode="numeric" data-decimals="0">

                    {{-- Tanggal & jam --}}
                    <input name="transacted_at" type="datetime-local"
                        class="border rounded-lg p-2 h-10 w-full md:col-span-2" value="{{ now()->format('Y-m-d\TH:i') }}"
                        aria-label="Tanggal transaksi">

                    {{-- Catatan (baris kedua, lebar) --}}
                    <input name="note" class="border rounded-lg p-2 h-10 w-full md:col-span-4" placeholder="Catatan"
                        aria-label="Catatan">

                    {{-- Tombol submit (penuh di mobile, sempit di md) --}}
                    <button class="px-3 h-10 bg-blue-600 text-white rounded-lg w-full md:col-span-1">
                        Catat Pembayaran
                    </button>
                </form>
            </div>
        @endforeach

        @php
            $summaryItems = [
                ['label' => 'Total hutang tercatat', 'value' => (string) $summary['count'], 'accent' => ''],
                ['label' => 'Total angsuran bulanan', 'value' => 'Rp ' . number_format($summary['monthly'], 0, ',', '.'), 'accent' => ''],
                ['label' => 'Total sisa hutang', 'value' => 'Rp ' . number_format($summary['remaining'], 0, ',', '.'), 'accent' => 'text-amber-600'],
            ];
        @endphp
        <div class="summary-footer-placeholder h-40 sm:h-32"></div>
        @include('partials.summary-footer', ['items' => $summaryItems])
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg">Tabungan</h2>
    </x-slot>

    <div class="p-4 space-y-4 pb-16">
        {{-- Filter akun (mobile-first, sama seperti Hutang) --}}
        <form class="grid grid-cols-12 gap-2" method="get" action="{{ route('savings.index') }}">
            <select name="account_id" class="col-span-9 sm:col-span-10 border border-white/20 rounded-lg p-2 h-10 w-full bg-white/10 text-white">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" @selected(optional($active)->id == $acc->id)>{{ $acc->name }}</option>
                @endforeach
            </select>
            <button class="col-span-3 sm:col-span-2 px-3 h-10 bg-blue-600 text-white rounded-lg w-full">Pilih</button>
        </form>

        <a href="{{ route('savings.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded-lg block text-center">+
            Buat Tabungan</a>

        @foreach($savings as $s)
            <div class="glass-panel-light rounded-2xl shadow p-4 space-y-3 overflow-hidden text-white">
                {{-- Header kartu (selaras dengan Hutang) --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ $s->name }}</div>
                        <div class="text-xs text-white/60">Target: Rp {{ number_format($s->target_amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-right text-sm shrink-0">
                        <div class="font-semibold text-blue-200">Terkumpul: Rp {{ number_format($s->current_amount, 0, ',', '.') }}</div>
                    </div>
                </div>

                {{-- Form setor/tarik (grid responsif, anti-overflow; pola sama dengan Hutang) --}}
                <form method="post" action="{{ route('savings.entries.store', $s) }}"
                    class="grid grid-cols-1 md:grid-cols-5 gap-2">
                    @csrf

                    {{-- Jenis transaksi --}}
                    <select name="type" class="border border-white/20 rounded-lg p-2 h-10 w-full md:col-span-1 bg-white/5 text-white"
                        aria-label="Jenis transaksi">
                        <option value="deposit">Setor</option>
                        <option value="withdraw">Tarik</option>
                    </select>

                    {{-- Akun sumber/tujuan uang --}}
                    <select name="transfer_account_id" class="border border-white/20 rounded-lg p-2 h-10 w-full md:col-span-2 bg-white/5 text-white"
                        aria-label="Akun sumber/tujuan">
                        @foreach($spendableAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                        @endforeach
                    </select>

                    {{-- Jumlah --}}
                    <input name="amount" type="text" inputmode="numeric" step="0.01" min="0"
                        class="border border-white/20 rounded-lg p-2 h-10 w-full md:col-span-1 bg-white/5 text-white js-currency" placeholder="Jumlah" aria-label="Jumlah" data-decimals="0">

                    {{-- Tanggal & jam --}}
                    <input name="transacted_at" type="datetime-local"
                        class="border border-white/20 rounded-lg p-2 h-10 w-full md:col-span-1 bg-white/5 text-white" value="{{ now()->format('Y-m-d\TH:i') }}"
                        aria-label="Tanggal transaksi">

                    {{-- Catatan (baris kedua penuh) --}}
                    <input name="note" class="border border-white/20 rounded-lg p-2 h-10 w-full md:col-span-4 bg-white/5 text-white placeholder-white/60" placeholder="Catatan"
                        aria-label="Catatan">

                    {{-- Tombol submit --}}
                    <button class="px-3 h-10 bg-blue-600 text-white rounded-lg w-full md:col-span-1">
                        Tambah
                    </button>
                </form>

            </div>
        @endforeach

        @php
            $summaryItems = [
                ['label' => 'Total target', 'value' => 'Rp ' . number_format($totalTarget, 0, ',', '.'), 'accent' => ''],
                ['label' => 'Total terkumpul', 'value' => 'Rp ' . number_format($totalCurrent, 0, ',', '.'), 'accent' => 'text-blue-600'],
            ];
        @endphp
        <div class="summary-footer-placeholder h-36 sm:h-28"></div>
        @include('partials.summary-footer', ['items' => $summaryItems])
    </div>
</x-app-layout>

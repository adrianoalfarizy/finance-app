<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg">Tagihan Bulanan</h2>
    </x-slot>

    <div class="p-4 space-y-4 pb-16">
        <div class="text-sm text-gray-600">
            Tagihan hutang yang jatuh tempo bulan {{ $monthLabel }}. Pilih tagihan yang sudah dibayar, lalu simpan.
        </div>

        @if (session('success'))
            <div class="bg-green-50 text-green-700 border border-green-200 rounded p-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 text-red-700 border border-red-200 rounded p-3 text-sm">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($debts->isEmpty())
            <div class="bg-white rounded-xl shadow p-4 text-sm text-gray-500">
                Tidak ada tagihan hutang yang perlu dibayar bulan ini.
            </div>
        @else
            <form method="post" action="{{ route('bills.store') }}" class="space-y-3">
                @csrf
                <div class="bg-white rounded-2xl shadow divide-y">
                    @foreach ($debts as $debt)
                        <div class="p-4 space-y-3">
                            <div class="flex items-start gap-3">
                                <label class="mt-1">
                                    <input type="checkbox" name="debts[{{ $debt->id }}][pay]" value="1" class="h-4 w-4">
                                </label>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between gap-3">
                                        <div>
                                            <div class="font-semibold truncate">{{ $debt->creditor_name }}</div>
                                            <div class="text-xs text-gray-500">Akun: {{ $debt->account->name }}</div>
                                        </div>
                                        <div class="text-right text-sm shrink-0">
                                            <div>Tagihan: <span class="font-semibold">Rp {{ number_format($debt->due_amount, 0, ',', '.') }}</span></div>
                                            <div>Sudah dibayar bulan ini: Rp {{ number_format($debt->paid_this_month_amount, 0, ',', '.') }}</div>
                                            <div>Sisa bulan ini: <span class="font-semibold text-red-600">Rp {{ number_format($debt->still_due_this_month, 0, ',', '.') }}</span></div>
                                            <div>Sisa total hutang: Rp {{ number_format($debt->remaining_due, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                    @if ($debt->due_date)
                                        <div class="text-xs text-gray-500 mt-1">Jatuh tempo: {{ \Illuminate\Support\Carbon::parse($debt->due_date)->translatedFormat('d F Y') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 md:pl-7">
                                <select name="debts[{{ $debt->id }}][pay_account_id]" class="border rounded-lg p-2 h-10 w-full">
                                    <option value="">Pilih sumber dana</option>
                                    @foreach ($payAccounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" value="Rp {{ number_format($debt->still_due_this_month, 0, ',', '.') }}" class="border rounded-lg p-2 h-10 bg-gray-100 text-gray-600" disabled>
                                <div class="hidden md:block"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button class="px-3 py-2 bg-blue-600 text-white rounded-lg w-full md:w-auto">Simpan Pembayaran</button>
            </form>
        @endif

        @php
            $summaryItems = [
                ['label' => 'Total tagihan bulan ini', 'value' => 'Rp ' . number_format($totalDue, 0, ',', '.'), 'accent' => 'text-red-600'],
            ];
        @endphp
        <div class="summary-footer-placeholder h-36 sm:h-28"></div>
        @include('partials.summary-footer', ['items' => $summaryItems])
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Hutang</h2></x-slot>

    <div class="p-4 space-y-4">
        <form class="flex gap-2" method="get" action="{{ route('debts.index') }}">
            <select name="account_id" class="w-full border rounded p-2">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" @selected(optional($active)->id==$acc->id)>{{ $acc->name }}</option>
                @endforeach
            </select>
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Pilih</button>
        </form>

        <a href="{{ route('debts.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded block text-center">+ Catat Hutang</a>

        @foreach($debts as $d)
        <div class="bg-white rounded-xl shadow p-3 space-y-2">
            <div class="flex justify-between">
                <div>
                    <div class="font-semibold">{{ $d->creditor_name }}</div>
                    <div class="text-xs text-gray-500">Status: {{ strtoupper($d->status) }}</div>
                </div>
                <div class="text-right text-sm">
                    <div>Pokok: Rp {{ number_format($d->principal_amount,0,',','.') }}</div>
                    <div>Terbayar: Rp {{ number_format($d->paid_amount,0,',','.') }}</div>
                    <div>Sisa: <span class="font-semibold">Rp {{ number_format($d->remaining,0,',','.') }}</span></div>
                </div>
            </div>

            <form method="post" action="{{ route('debts.payments.store',$d) }}" class="flex gap-2">
                @csrf
                <select name="account_id" class="border rounded p-2">
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
                <input name="amount" class="border rounded p-2 w-full" placeholder="Jumlah angsuran">
                <input name="transacted_at" type="datetime-local" class="border rounded p-2" value="{{ now()->format('Y-m-d\TH:i') }}">
                <input name="note" class="border rounded p-2 w-full" placeholder="Catatan">
                <button class="px-3 py-2 bg-blue-600 text-white rounded">Catat Pembayaran</button>
            </form>
        </div>
        @endforeach
    </div>
</x-app-layout>

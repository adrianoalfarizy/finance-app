<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Catat Hutang</h2></x-slot>
    <form method="post" action="{{ route('debts.store') }}" class="p-4 space-y-3" x-data="{ type: '{{ old('repayment_type', 'installment') }}' }">
        @csrf
        <label class="block">
            <span class="text-xs">Akun (terkait hutang)</span>
            <select name="account_id" class="w-full border rounded p-2">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                @endforeach
            </select>
        </label>
        <input name="creditor_name" class="w-full border rounded p-2" placeholder="Kreditur (nama pihak)" value="{{ old('creditor_name') }}">
        <input name="principal_amount" class="w-full border rounded p-2 js-currency" placeholder="Pokok hutang" type="text" inputmode="numeric" data-decimals="0" value="{{ old('principal_amount') }}">
        <input name="interest_rate" class="w-full border rounded p-2" placeholder="Bunga % (opsional)" value="{{ old('interest_rate') }}">
        <label class="block">
            <span class="text-xs">Jenis pelunasan</span>
            <select name="repayment_type" class="w-full border rounded p-2" x-model="type">
                <option value="one_time" @selected(old('repayment_type', 'installment') === 'one_time')>Sekali bayar</option>
                <option value="installment" @selected(old('repayment_type', 'installment') === 'installment')>Beberapa kali angsuran</option>
            </select>
        </label>
        <div x-cloak x-show="type === 'installment'">
            <input name="monthly_payment" class="w-full border rounded p-2 js-currency"
                   placeholder="Jumlah angsuran bulanan"
                   type="text" inputmode="numeric" data-decimals="0" value="{{ old('monthly_payment') }}">
        </div>
        <input name="start_date" type="date" class="w-full border rounded p-2" value="{{ old('start_date') }}">
        <input name="due_date" type="date" class="w-full border rounded p-2" value="{{ old('due_date') }}">
        <textarea name="note" class="w-full border rounded p-2" placeholder="Catatan (opsional)">{{ old('note') }}</textarea>
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Simpan</button>
    </form>
</x-app-layout>

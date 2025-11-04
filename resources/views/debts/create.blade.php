<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Catat Hutang</h2></x-slot>
    <form method="post" action="{{ route('debts.store') }}" class="p-4 space-y-3">
        @csrf
        <label class="block">
            <span class="text-xs">Akun (terkait hutang)</span>
            <select name="account_id" class="w-full border rounded p-2">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                @endforeach
            </select>
        </label>
        <input name="creditor_name" class="w-full border rounded p-2" placeholder="Kreditur (nama pihak)">
        <input name="principal_amount" class="w-full border rounded p-2 js-currency" placeholder="Pokok hutang" type="text" inputmode="numeric" data-decimals="0">
        <input name="interest_rate" class="w-full border rounded p-2" placeholder="Bunga % (opsional)">
        <input name="monthly_payment" class="w-full border rounded p-2 js-currency" placeholder="Angsuran bulanan (opsional)" type="text" inputmode="numeric" data-decimals="0">
        <input name="start_date" type="date" class="w-full border rounded p-2">
        <input name="due_date" type="date" class="w-full border rounded p-2">
        <textarea name="note" class="w-full border rounded p-2" placeholder="Catatan (opsional)"></textarea>
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Simpan</button>
    </form>
</x-app-layout>

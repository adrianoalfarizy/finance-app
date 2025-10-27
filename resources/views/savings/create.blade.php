<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Buat Tabungan</h2></x-slot>
    <form method="post" action="{{ route('savings.store') }}" class="p-4 space-y-3">
        @csrf
        <label class="block">
            <span class="text-xs">Akun (milik siapa)</span>
            <select name="account_id" class="w-full border rounded p-2">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                @endforeach
            </select>
        </label>
        <input name="name" class="w-full border rounded p-2" placeholder="Nama tabungan">
        <input name="target_amount" class="w-full border rounded p-2 js-currency" placeholder="Target (opsional)" type="text" inputmode="numeric" data-decimals="0">
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Simpan</button>
    </form>
</x-app-layout>

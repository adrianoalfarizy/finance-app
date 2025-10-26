<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Tambah Akun</h2></x-slot>
    <form class="p-4 space-y-3" method="post" action="{{ route('accounts.store') }}">
        @csrf
        <input name="name" class="w-full border rounded p-2" placeholder="Nama akun (mis: BCA)">
        <select name="type" class="w-full border rounded p-2">
            <option value="cash">Cash</option>
            <option value="bank">Bank</option>
            <option value="ewallet">E-Wallet</option>
        </select>
        <textarea name="notes" class="w-full border rounded p-2" placeholder="Catatan (opsional)"></textarea>
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Simpan</button>
    </form>
</x-app-layout>

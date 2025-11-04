<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Tambah Kategori</h2></x-slot>

    <form method="post" action="{{ route('categories.store') }}" class="p-4 space-y-3">
        @csrf
        <label class="block">
            <span class="text-xs">Nama kategori</span>
            <input name="name" class="w-full border rounded p-2" value="{{ old('name') }}" placeholder="Misal: Gaji, Makan">
        </label>
        <label class="block">
            <span class="text-xs">Jenis</span>
            <select name="kind" class="w-full border rounded p-2">
                <option value="income" @selected(old('kind') === 'income')>Pemasukan</option>
                <option value="expense" @selected(old('kind') === 'expense')>Pengeluaran</option>
            </select>
        </label>
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Simpan</button>
    </form>
</x-app-layout>

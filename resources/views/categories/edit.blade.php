<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Edit Kategori</h2></x-slot>

    <form method="post" action="{{ route('categories.update', $category) }}" class="p-4 space-y-3">
        @csrf @method('PUT')
        <label class="block">
            <span class="text-xs">Nama kategori</span>
            <input name="name" class="w-full border rounded p-2" value="{{ old('name', $category->name) }}">
        </label>
        <label class="block">
            <span class="text-xs">Jenis</span>
            <select name="kind" class="w-full border rounded p-2">
                <option value="income" @selected(old('kind', $category->kind) === 'income')>Pemasukan</option>
                <option value="expense" @selected(old('kind', $category->kind) === 'expense')>Pengeluaran</option>
            </select>
        </label>
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Update</button>
    </form>
</x-app-layout>

<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Kategori</h2></x-slot>

    <div class="p-4 space-y-4">
        @if (session('success'))
            <div class="bg-green-50 text-green-700 border border-green-200 rounded p-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route('categories.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded w-full sm:w-auto inline-block">+ Tambah Kategori</a>

        <div class="bg-white rounded-xl shadow divide-y">
            @forelse($categories as $category)
                <div class="p-3 flex items-center justify-between">
                    <div>
                        <div class="font-semibold">{{ $category->name }}</div>
                        <div class="text-xs text-gray-500">{{ strtoupper($category->kind) }}</div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('categories.edit', $category) }}" class="text-xs px-2 py-1 border rounded">Edit</a>
                        <form method="post" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?');">
                            @csrf @method('DELETE')
                            <button class="text-xs px-2 py-1 border rounded text-red-600">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-3 text-sm text-gray-500">Belum ada kategori.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>

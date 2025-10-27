<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Transaksi</h2></x-slot>

    <div class="p-4 space-y-3">
        <form class="flex gap-2" method="get" action="{{ route('transactions.index') }}">
            <select name="account_id" class="w-full border rounded p-2">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" @selected(optional($active)->id==$acc->id)>{{ $acc->name }}</option>
                @endforeach
            </select>
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Pilih</button>
        </form>

        <a href="{{ route('transactions.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded block text-center">+ Tambah</a>

        <div class="bg-white rounded-xl shadow divide-y">
            @forelse($transactions as $t)
                <div class="p-3 flex justify-between items-center">
                    <div>
                        <div class="text-sm">{{ $t->description ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $t->transacted_at->format('d M Y H:i') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold {{ $t->type==='income'?'text-green-600':'text-red-600' }}">
                            {{ $t->type==='income' ? '+' : '-' }}Rp {{ number_format($t->amount,0,',','.') }}
                        </div>
                        <form method="post" action="{{ route('transactions.destroy',$t) }}" onsubmit="return confirm('Hapus transaksi?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-600">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-3 text-sm text-gray-500">Belum ada transaksi.</div>
            @endforelse
        </div>

        <div>{{ $transactions->links() }}</div>
    </div>
</x-app-layout>

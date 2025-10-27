<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Tambah Transaksi</h2></x-slot>

    <div x-data="{ mode: 'expense' }" class="p-4 space-y-4">
        <div class="bg-white rounded-xl p-2 shadow flex gap-2 text-sm">
            <button type="button" class="px-3 py-1 border rounded" :class="mode==='income' ? 'bg-blue-600 text-white' : ''" @click="mode='income'">Pemasukan</button>
            <button type="button" class="px-3 py-1 border rounded" :class="mode==='expense' ? 'bg-blue-600 text-white' : ''" @click="mode='expense'">Pengeluaran</button>
            <button type="button" class="px-3 py-1 border rounded" :class="mode==='transfer' ? 'bg-blue-600 text-white' : ''" @click="mode='transfer'">Transfer</button>
        </div>

        {{-- Income/Expense --}}
        <form x-show="mode!=='transfer'" class="space-y-3" method="post" action="{{ route('transactions.store') }}">
            @csrf
            <input type="hidden" name="type" :value="mode">
            <input type="hidden" name="mode" :value="mode">
            <label class="block">
                <span class="text-xs">Akun (dana milik siapa)</span>
                <select name="account_id" class="w-full border rounded p-2">
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-xs">Kategori</span>
                <select name="category_id" class="w-full border rounded p-2">
                    <option value="">— Pilih —</option>
                    <optgroup label="Pemasukan">
                        @foreach($income as $c)
                            <option value="{{ $c->id }}">[IN] {{ $c->name }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Pengeluaran">
                        @foreach($expense as $c)
                            <option value="{{ $c->id }}">[OUT] {{ $c->name }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </label>
            <input name="amount" class="w-full border rounded p-2 js-currency" placeholder="Jumlah (angka)" type="text" inputmode="numeric" data-decimals="0">
            <input name="transacted_at" type="datetime-local" class="w-full border rounded p-2" value="{{ now()->format('Y-m-d\TH:i') }}">
            <input name="description" class="w-full border rounded p-2" placeholder="Keterangan (opsional)">
            <button class="px-3 py-2 bg-blue-600 text-white rounded w-full">Simpan</button>
        </form>

        {{-- Transfer --}}
        <form x-show="mode==='transfer'" class="space-y-3" method="post" action="{{ route('transactions.store') }}">
            @csrf
            <input type="hidden" name="mode" value="transfer">
            <label class="block">
                <span class="text-xs">Dari Akun (dana keluar dari)</span>
                <select name="source_account_id" class="w-full border rounded p-2">
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-xs">Ke Akun (dana masuk ke)</span>
                <select name="destination_account_id" class="w-full border rounded p-2">
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            </label>
            <input name="amount" class="w-full border rounded p-2 js-currency" placeholder="Jumlah (angka)" type="text" inputmode="numeric" data-decimals="0">
            <input name="transacted_at" type="datetime-local" class="w-full border rounded p-2" value="{{ now()->format('Y-m-d\TH:i') }}">
            <input name="description" class="w-full border rounded p-2" placeholder="Keterangan (opsional)">
            <button class="px-3 py-2 bg-blue-600 text-white rounded w-full">Simpan Transfer</button>
        </form>
    </div>
</x-app-layout>
    
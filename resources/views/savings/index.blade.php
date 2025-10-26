<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Tabungan</h2></x-slot>

    <div class="p-4 space-y-4">
        <form class="flex gap-2" method="get" action="{{ route('savings.index') }}">
            <select name="account_id" class="w-full border rounded p-2">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" @selected(optional($active)->id==$acc->id)>{{ $acc->name }}</option>
                @endforeach
            </select>
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Pilih</button>
        </form>

        <a href="{{ route('savings.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded block text-center">+ Buat Tabungan</a>

        @foreach($savings as $s)
        <div class="bg-white rounded-xl shadow p-3 space-y-2">
            <div class="flex justify-between">
                <div>
                    <div class="font-semibold">{{ $s->name }}</div>
                    <div class="text-xs text-gray-500">Target: Rp {{ number_format($s->target_amount,0,',','.') }}</div>
                </div>
                <div class="text-sm font-semibold">Terkumpul: Rp {{ number_format($s->current_amount,0,',','.') }}</div>
            </div>

            <form method="post" action="{{ route('savings.entries.store',$s) }}" class="flex gap-2">
                @csrf
                <select name="type" class="border rounded p-2">
                    <option value="deposit">Setor</option>
                    <option value="withdraw">Tarik</option>
                </select>
                <input name="amount" class="border rounded p-2 w-full" placeholder="Jumlah">
                <input name="transacted_at" type="datetime-local" class="border rounded p-2" value="{{ now()->format('Y-m-d\TH:i') }}">
                <input name="note" class="border rounded p-2 w-full" placeholder="Catatan">
                <button class="px-3 py-2 bg-blue-600 text-white rounded">Tambah</button>
            </form>
        </div>
        @endforeach
    </div>
</x-app-layout>

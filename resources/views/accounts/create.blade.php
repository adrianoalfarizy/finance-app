<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Tambah Akun</h2></x-slot>

    <div class="p-4 space-y-3">
        {{-- Pesan error validasi --}}
        @if ($errors->any())
            <div class="bg-red-50 text-red-700 border border-red-200 rounded-lg p-3 text-sm">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('accounts.store') }}" class="space-y-3">
            @csrf

            <input name="name" class="w-full border rounded p-2"
                   value="{{ old('name') }}"
                   placeholder="Nama akun (mis: BCA)">

            <select name="type" class="w-full border rounded p-2">
                @foreach(['cash'=>'Cash','bank'=>'Bank','ewallet'=>'E-Wallet','saving'=>'Tabungan','debt'=>'Hutang'] as $val=>$label)
                    <option value="{{ $val }}" @selected(old('type')===$val)>{{ $label }}</option>
                @endforeach
            </select>

            <textarea name="notes" class="w-full border rounded p-2" placeholder="Catatan (opsional)">{{ old('notes') }}</textarea>

            <button class="px-3 py-2 bg-blue-600 text-white rounded">Simpan</button>
        </form>
    </div>
</x-app-layout>

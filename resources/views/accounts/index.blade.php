<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Akun</h2></x-slot>
    <div class="p-4 space-y-4">
        @if (session('success'))
            <div class="bg-green-50 text-green-700 border border-green-200 rounded p-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        <a href="{{ route('accounts.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">+ Tambah Akun</a>
        <div class="bg-white rounded-xl shadow divide-y">
            @forelse($accounts as $a)
                <div class="p-3 flex items-center justify-between">
                    <div>
                        <div class="font-semibold">{{ $a->name }}</div>
                        <div class="text-xs text-gray-500">{{ strtoupper($a->type) }} • Saldo: Rp {{ number_format($a->balance,0,',','.') }}</div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('accounts.share.edit',$a) }}" class="text-xs px-2 py-1 border rounded">Share</a>
                        <a href="{{ route('accounts.edit',$a) }}" class="text-xs px-2 py-1 border rounded">Edit</a>
                        <form method="post" action="{{ route('accounts.destroy',$a) }}" onsubmit="return confirm('Hapus akun?');">
                            @csrf @method('DELETE')
                            <button class="text-xs px-2 py-1 border rounded text-red-600">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-3 text-sm text-gray-500">Belum ada akun.</div>
            @endforelse
        </div>
        @php
            $summaryItems = [
                ['label' => 'Total saldo seluruh akun', 'value' => 'Rp ' . number_format($totalBalance, 0, ',', '.'), 'accent' => ''],
            ];
        @endphp
        <div class="summary-footer-placeholder h-36 sm:h-28"></div>
        @include('partials.summary-footer', ['items' => $summaryItems])
    </div>
</x-app-layout>

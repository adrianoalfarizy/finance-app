<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Bagikan Akses: {{ $account->name }}</h2></x-slot>

    <div class="p-4 space-y-4">
        <form method="post" action="{{ route('accounts.share.update',$account) }}" class="bg-white rounded-xl p-3 shadow space-y-2">
            @csrf
            <input type="email" name="email" class="w-full border rounded p-2" placeholder="Email user">
            <select name="role" class="w-full border rounded p-2">
                <option value="editor">Editor (lihat & ubah)</option>
                <option value="viewer">Viewer (lihat saja)</option>
            </select>
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Berikan Akses</button>
        </form>

        <div class="bg-white rounded-xl shadow">
            <div class="p-3 font-semibold">Sudah Dibagikan</div>
            <ul class="divide-y">
                @foreach($shared as $u)
                    <li class="p-3 flex justify-between items-center">
                        <div>
                            <div class="text-sm">{{ $u->name }} <span class="text-xs text-gray-500">({{ $u->email }})</span></div>
                            <div class="text-xs text-gray-500">Role: {{ strtoupper($u->pivot->role) }}</div>
                        </div>
                        <form method="post" action="{{ route('accounts.share.revoke', [$account, $u]) }}" onsubmit="return confirm('Cabut akses?');">
                            @csrf @method('DELETE')
                            <button class="text-xs px-2 py-1 border rounded text-red-600">Cabut</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-app-layout>

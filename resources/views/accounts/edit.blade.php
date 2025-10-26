<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-lg">Edit Akun</h2></x-slot>
    <form class="p-4 space-y-3" method="post" action="{{ route('accounts.update',$account) }}">
        @csrf @method('PUT')
        <input name="name" class="w-full border rounded p-2" value="{{ old('name',$account->name) }}">
        <select name="type" class="w-full border rounded p-2">
            @foreach(['cash','bank','ewallet'] as $t)
                <option value="{{ $t }}" @selected($t===$account->type)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <textarea name="notes" class="w-full border rounded p-2">{{ old('notes',$account->notes) }}</textarea>
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Update</button>
    </form>
</x-app-layout>

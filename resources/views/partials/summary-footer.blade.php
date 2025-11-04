@if (!empty($items))
    <div class="fixed inset-x-0 bottom-16 sm:bottom-4 px-4 pb-4 z-40 pointer-events-none">
        <div class="max-w-4xl mx-auto">
            <div class="glass-panel-light rounded-2xl shadow-lg p-4 space-y-1 text-sm pointer-events-auto text-white/90">
                @foreach ($items as $item)
                    <div class="flex justify-between items-center">
                        <span class="truncate">{{ $item['label'] }}</span>
                        <span class="font-semibold {{ $item['accent'] ?? '' }}">{{ $item['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

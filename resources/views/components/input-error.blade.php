@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'mt-2']) }}>
        @foreach ((array) $messages as $message)
            <div class="flex items-start gap-2 bg-red-50/50 border border-red-200 text-red-700 px-3 py-2 rounded-md shadow-sm text-sm animate-pulse">
                <!-- Icône d'alerte -->
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                
                <!-- Message -->
                <span class="font-medium">{{ $message }}</span>
            </div>
        @endforeach
    </div>
@endif
<div class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full" style="display: {{ $showModal ? 'block' : 'none' }};">
    @if ($showModal) {{-- Ne rend le contenu du modal que s'il est ouvert --}}
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-1/3">
                <h3 class="text-lg font-bold mb-4">{{ $memo->subject ?? 'Détails du Mémo' }}</h3> {{-- Affiche le sujet du mémo --}}
                <p class="mb-6">{{ $memo->content ?? 'Contenu non disponible.' }}</p> {{-- Affiche le contenu du mémo --}}

                <div class="flex justify-end space-x-4">
                    <button wire:click="closeModal" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                        Fermer
                    </button>
                    <button wire:click="closeModal" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                        Sauvegarder
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
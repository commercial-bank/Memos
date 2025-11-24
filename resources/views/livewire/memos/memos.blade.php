<div class="p-6 bg-gray-100 min-h-screen"> 
    <h1 class="text-3xl font-bold mb-4">MEMO RANDUMS</h1>
    <p class="text-gray-600 mb-6">Manage your memo randums.</p>

    <div class="flex space-x-4 mb-6 border-b border-gray-200">
        <button
            class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'incoming' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
            wire:click="selectTab('incoming')"
        >
            Mémos Entrants
            
            <span class="ml-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full">3</span>
        </button>

        <button
            class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'drafted' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
            wire:click="selectTab('drafted')"
        >
            Mémos Brouillons

            <span class="ml-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{$countB}}</span>
        </button>

        <button
            class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'document' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
            wire:click="selectTab('document')"
        >
            Mémos Documents

            <span class="ml-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full">2</span>
        </button>

        <button
            class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'sent' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
            wire:click="selectTab('sent')"
        >
            Mémos Envoyés
            <span class="ml-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full">2</span>
        </button>
    </div>

    <div class="flex justify-end mb-4 space-x-2">
        <button wire:click="openModal"
            class="bg-yellow-400 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded flex items-center"

        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            New Mémo
        </button>
        <button class="border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-2 px-4 rounded">
            Filter
        </button>
    </div>

    {{-- Contenu des onglets --}}
    <div class="mt-8">
        @if ($activeTab === 'incoming')
            <livewire:memos.incoming-memos />
        @elseif ($activeTab === 'drafted')
            <livewire:memos.drafted-memos />
        @elseif ($activeTab === 'document')
            <livewire:memos.docs-memos />
        @elseif ($activeTab === 'sent')
           tytyty
        @endif
    </div>


    <!-- Modal (Tailwind CSS Corrigé) -->
    @if($isOpen)
    <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- L'arrière-plan sombre (Overlay) -->
        <!-- On ajoute 'fixed inset-0' pour qu'il prenne tout l'écran -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <!-- Le conteneur de positionnement -->
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                
                <!-- Le panneau du formulaire (La boite blanche) -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">
                    
                    <form wire:submit.prevent="save">
                        <!-- Corps du modal -->
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 mb-4" id="modal-title">
                                Nouveau Memo
                            </h3>
                            
                            <!-- Champ Object -->
                            <div class="mb-4">
                                <label for="object" class="block text-sm font-medium text-gray-700 mb-1">Object</label>
                                <input type="text" wire:model="object" id="object" class="w-full rounded-md border-yellow-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 py-2 px-3 border text-gray-900">
                                @error('object') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Champ Type_memo -->
                            <div class="mb-4">
                                <label for="type_memo" class="block text-sm font-medium text-gray-700 mb-1">Type De Memo</label>
                                <select  wire:model="type_memo" id="type_memo"  class="w-full rounded-md border-yellow-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 py-2 px-3 border text-gray-900">
                                    <option value="Memo Simple">Memo Simple</option>
                                    <option value="Memo De Projet">Memo De Projet</option>
                                </select>
                                @error('type_memo') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Champ Contenu -->
                            <div class="mb-4">
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Contenu</label>
                                <textarea wire:model="content" id="content" rows="4" class="w-full p-4 min-h-[200px] max-h-[400px] border-yellow-300 overflow-y-auto outline-none prose max-w-none text-gray-800 bg-white"></textarea>
                                @error('content') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <!-- Pied du modal (Boutons) -->
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 sm:ml-3 sm:w-auto">
                                Enregistrer
                            </button>
                            <button type="button" wire:click="closeModal" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                Annuler
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
    @endif
   

</div>
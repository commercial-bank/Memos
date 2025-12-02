<div class="p-6 bg-gray-100 min-h-screen"> 
    <h1 class="text-3xl font-bold mb-4">MEMO RANDUMS</h1>
    <p class="text-gray-600 mb-6">Manage your memo randums.</p>

    <div class="flex space-x-4 mb-6 border-b border-gray-200">
        <button
            class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'incoming' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
            wire:click="selectTab('incoming')"
        >
            Mémos Sortants
            
        
        </button>

         <button
            class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'incoming2' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
            wire:click="selectTab('incoming2')"
        >
            Mémos Entrants
            
        </button>

       @if(auth()->user()->poste == 'Secretaire')
            
        <button
            class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'blockout' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
            wire:click="selectTab('blockout')" 
        >
            <!-- J'ai corrigé le wire:click ci-dessus -->
            Blocs Mémos Sortants
        </button>

        <button
            class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'blockint' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
            wire:click="selectTab('blockint')"
        >
            <!-- J'ai corrigé le wire:click ci-dessus -->
            Blocs Mémos Entrants
        </button>
        
            
        @else

            <button
                class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'drafted' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
                wire:click="selectTab('drafted')"
            >
                Mémos Brouillons

            
            </button>

            <button
                class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'document' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
                wire:click="selectTab('document')"
            >
                Mémos Documents

                
            </button>

            <button
                class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'favorites' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
                wire:click="selectTab('favorites')"
            >
                Favoris
    
            </button>

        @endif

    </div>

    <div class="flex justify-end mb-4 space-x-2">
        <button wire:click="openModal"
            class="bg-[#daaf2c] hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded flex items-center"

        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            New Mémo
        </button>
    </div>

    {{-- Contenu des onglets --}}
    <div class="mt-8">
        @if ($activeTab === 'incoming')
            <livewire:memos.incoming-memos />
        @elseif($activeTab === 'incoming2')   
            <livewire:memos.incoming2-memos />
        @elseif ($activeTab === 'drafted')
            <livewire:memos.drafted-memos />
        @elseif ($activeTab === 'document')
            <livewire:memos.docs-memos />
        @elseif ($activeTab === 'blockout')  
            <livewire:memos.blockout-memos />
        @elseif ($activeTab === 'blockint')  
            <livewire:memos.blockint-memos />
        @elseif ($activeTab === 'favorites')  
            <livewire:favorites.favorite-memos />
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

                            <!-- Champ concernet -->
                            <div class="mb-4">
                                <label for="object" class="block text-sm font-medium text-gray-700 mb-1">Concerne</label>
                                <input type="text" wire:model="concern" id="concern" class="w-full rounded-md border-yellow-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 py-2 px-3 border text-gray-900">
                                @error('concern') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>


                            <!-- Champ Contenu (Éditeur Riche) -->
                                    <div class="mb-4">
                                        <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Contenu</label>
                                        
                                        <div wire:ignore 
                                            class="rounded-md shadow-sm"
                                            x-data="{
                                                content: @entangle('content'),
                                                initQuill() {
                                                    const quill = new Quill(this.$refs.quillEditor, {
                                                        theme: 'snow',
                                                        placeholder: 'Rédigez votre mémo ici...',
                                                        modules: {
                                                            toolbar: [
                                                                // GROUPE 1 : Titres et Polices
                                                                [{ 'header': [1, 2, 3, false] }], // H1, H2, H3, Normal

                                                                // GROUPE 2 : Formatage de base
                                                                ['bold', 'italic', 'underline', 'strike'], // Gras, Italique, Souligné, Barré
                                                                
                                                                // GROUPE 3 : Couleurs
                                                                [{ 'color': [] }, { 'background': [] }], // Couleur du texte & Surlignage

                                                                // GROUPE 4 : Listes et Indentation
                                                                [{ 'list': 'ordered'}, { 'list': 'bullet' }], 
                                                                [{ 'indent': '-1'}, { 'indent': '+1' }], // Diminuer/Augmenter le retrait

                                                                // GROUPE 5 : Alignement
                                                                [{ 'align': [] }], 

                                                                // GROUPE 6 : Insertion (Lien)
                                                                ['link'], // Outil pour insérer un lien hypertexte

                                                                // GROUPE 7 : Nettoyage
                                                                ['clean'] // Effacer le formatage
                                                            ]
                                                        }
                                                    });

                                                    // Charger le contenu initial s'il existe (édition)
                                                    if (this.content) {
                                                        quill.root.innerHTML = this.content;
                                                    }

                                                    // Synchroniser Quill vers Livewire quand on tape
                                                    quill.on('text-change', () => {
                                                        this.content = quill.root.innerHTML;
                                                    });
                                                }
                                            }"
                                            x-init="initQuill()"
                                        >
                                            <!-- Le conteneur visuel de l'éditeur -->
                                            <div class="bg-white border border-gray-300 rounded-md overflow-hidden focus-within:border-yellow-500 focus-within:ring-1 focus-within:ring-yellow-500 transition-all duration-200">
                                                <!-- La zone de saisie Quill -->
                                                <div x-ref="quillEditor" class="min-h-[150px] max-h-[300px] text-gray-800 text-base font-sans"></div>
                                            </div>
                                        </div>

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
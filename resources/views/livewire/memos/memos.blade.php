<div class="min-h-screen bg-gray-50 text-gray-800 font-sans">

    <!-- ========================================================== -->
    <!-- VUE 1 : LISTE DES MÉMOS -->
    <!-- ========================================================== -->
    @if(!$isCreating)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- En-tête -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Mémorandums</h1>
                    <p class="text-gray-500 mt-1">Gérez, suivez et archivez vos communications internes.</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <button wire:click="createMemo"
                        class="group inline-flex items-center justify-center px-6 py-3 text-base font-medium text-white transition-all duration-200 bg-yellow-500 rounded-full shadow-lg hover:bg-yellow-600 hover:shadow-yellow-500/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transform hover:-translate-y-1">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nouveau Mémo
                    </button>
                </div>
            </div>

            <!-- Navigation par Onglets -->
            <div class="border-b border-gray-200 mb-8">
                <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                    @php
                        $tabs = [
                            'incoming' => 'Sortants',
                            'incoming2' => 'Entrants',
                        ];
                        if(auth()->user()->poste == 'Secretaire') {
                            $tabs['blockout'] = 'Blocs Sortants';
                            $tabs['blockint'] = 'Blocs Entrants';
                        } else {
                            $tabs['drafted'] = 'Brouillons';
                            $tabs['document'] = 'Documents';
                            $tabs['favorites'] = 'Favoris';
                        }
                    @endphp

                    @foreach($tabs as $key => $label)
                        <button
                            wire:click="selectTab('{{ $key }}')"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                            {{ $activeTab === $key 
                                ? 'border-yellow-500 text-yellow-600' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </nav>
            </div>

            <!-- Contenu dynamique des listes -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 min-h-[400px] p-6">
                @switch($activeTab)
                    @case('incoming') <livewire:memos.incoming-memos wire:key="tab-incoming"/> @break
                    @case('incoming2') <livewire:memos.incoming2-memos wire:key="tab-incoming2"/> @break
                    @case('drafted') <livewire:memos.drafted-memos wire:key="tab-drafted"/> @break
                    @case('document') <livewire:memos.docs-memos wire:key="tab-document"/> @break
                    @case('blockout') <livewire:memos.blockout-memos wire:key="tab-blockout"/> @break
                    @case('blockint') <livewire:memos.blockint-memos wire:key="tab-blockint"/> @break
                    @case('favorites') <livewire:favorites.favorite-memos wire:key="tab-favorites"/> @break
                @endswitch
            </div>
        </div>

    <!-- ========================================================== -->
    <!-- VUE 2 : ÉDITEUR DE MÉMO (CRÉATION/EDITION) -->
    <!-- ========================================================== -->
    @else
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in-up">
            
            <!-- Barre d'actions -->
            <div class="mb-8 bg-white border border-gray-200 rounded-xl shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-all duration-300 hover:shadow-md">
                
                <!-- Bouton Retour -->
                <button wire:click="cancelCreation" type="button" class="group flex items-center text-gray-500 hover:text-gray-900 transition-colors">
                    <div class="mr-3 h-10 w-10 rounded-full bg-gray-100 group-hover:bg-yellow-100 group-hover:text-yellow-700 flex items-center justify-center transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col items-start">
                        <span class="font-bold text-base">Retour</span>
                        <span class="text-xs text-gray-400 font-normal">Vers la liste des mémos</span>
                    </div>
                </button>
                
                <!-- Groupe Actions -->
                <div class="flex items-center justify-end space-x-3 w-full sm:w-auto border-t sm:border-t-0 border-gray-100 pt-3 sm:pt-0">
                    <button wire:click="cancelCreation" type="button" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200">
                        Annuler
                    </button>
                    
                    <!-- BOUTON ENREGISTRER AVEC LOADING STATE -->
                    <button wire:click="save" wire:loading.attr="disabled" type="button" class="relative inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-lg shadow-md text-white bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        
                        <!-- Contenu visible quand ça ne charge pas -->
                        <span wire:loading.remove wire:target="save" class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Enregistrer le Mémo
                        </span>

                        <!-- Contenu visible PENDANT le chargement -->
                        <span wire:loading wire:target="save" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Traitement...
                        </span>
                    </button>
                </div>
            </div>

            <!-- Message d'erreur global -->
            @if ($errors->any())
                <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Il y a des erreurs dans le formulaire :</h3>
                            <ul class="mt-2 list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="text-sm text-red-700">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- FEUILLE DE PAPIER (FORMULAIRE PRINCIPAL) -->
            <div class="bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden relative">
                
                <!-- Bandeau décoratif -->
                <div class="bg-gradient-to-r from-gray-900 to-gray-800 px-8 py-6 text-white flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold tracking-wider uppercase" style="font-family: 'Times New Roman', serif;">Mémorandum</h2>
                        <p class="text-yellow-500 text-xs font-semibold tracking-widest mt-1">INTERNE / CONFIDENTIEL</p>
                    </div>
                    <div class="text-right opacity-70">
                        <p class="text-sm">Date : {{ now()->format('d/m/Y') }}</p>
                    </div>
                </div>

                <!-- Corps du formulaire -->
                <div class="p-8 md:p-12 space-y-8">
                    
                    <!-- 1. Section Méta-données -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pb-4">
                        <!-- Destinataire principal / Concerne -->
                        <div class="space-y-6">
                            <div class="relative group">
                                <label for="concern" class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Pour (Concerne)</label>
                                <input type="text" wire:model="concern" id="concern" 
                                    class="block w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-gray-900 placeholder-gray-300 focus:border-yellow-500 focus:ring-0 sm:text-lg transition-colors" 
                                    placeholder="Ex: Direction Générale...">
                                @error('concern') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Objet -->
                        <div class="space-y-6">
                            <div class="relative group">
                                <label for="object" class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Objet</label>
                                <input type="text" wire:model="object" id="object" 
                                    class="block w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-gray-900 font-semibold placeholder-gray-300 focus:border-yellow-500 focus:ring-0 sm:text-lg transition-colors" 
                                    placeholder="Sujet principal...">
                                @error('object') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 2. SECTION DESTINATAIRES -->
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Liste de Distribution (Destinataires)
                        </h3>

                        <!-- Formulaire d'ajout -->
                        <div class="flex flex-col md:flex-row gap-4 mb-4">
                            <!-- Select Entité -->
                            <div class="flex-1">
                                <select wire:model="newRecipientEntity" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 text-sm">
                                    <option value="">-- Sélectionner un destinataire (Entité) --</option>
                                    @foreach($entities as $entity)
                                        <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                                    @endforeach
                                </select>
                                @error('newRecipientEntity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Select Action -->
                            <div class="flex-1">
                                <select wire:model="newRecipientAction" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 text-sm">
                                    <option value="">-- Action requise --</option>
                                    @foreach($actionsList as $action)
                                        <option value="{{ $action }}">{{ $action }}</option>
                                    @endforeach
                                </select>
                                @error('newRecipientAction') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Bouton Ajouter -->
                            <button wire:click="addRecipient" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Ajouter
                            </button>
                        </div>

                        <!-- Tableau des destinataires ajoutés -->
                        @if(count($recipients) > 0)
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300 bg-white">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="py-2 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase">Entité / Destinataire</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Action à entreprendre</th>
                                            <th scope="col" class="relative py-2 pl-3 pr-4 sm:pr-6"><span class="sr-only">Retirer</span></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach($recipients as $index => $recipient)
                                            <tr>
                                                <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm font-medium text-gray-900">
                                                    {{ $recipient['entity_name'] }}
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-500">
                                                    <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                                                        {{ $recipient['action'] }}
                                                    </span>
                                                </td>
                                                <td class="relative whitespace-nowrap py-3 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                    <button wire:click="removeRecipient({{ $index }})" class="text-red-600 hover:text-red-900">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 text-sm text-gray-400 italic">
                                Aucun destinataire ajouté pour le moment.
                            </div>
                        @endif
                    </div>

                    <!-- 3. SECTION ÉDITEUR TYPE WORD (Quill) -->
                    <div class="pt-6">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Corps du Document
                        </label>
                        
                        <!-- Conteneur AlpineJS pour l'éditeur -->
                        <div wire:ignore 
                             class="flex flex-col items-center bg-gray-100 rounded-lg p-4 border border-gray-200 shadow-inner"
                             x-data="{
                                content: @entangle('content'),
                                quill: null,
                                initQuill() {
                                    // Initialisation SANS le module table qui plante
                                    this.quill = new Quill(this.$refs.quillEditor, {
                                        theme: 'snow',
                                        placeholder: 'Rédigez votre mémo ici...',
                                        modules: {
                                            toolbar: '#toolbar-container'
                                        }
                                    });

                                    // Charger le contenu initial
                                    if (this.content) {
                                        this.quill.root.innerHTML = this.content;
                                    }

                                    // Synchro vers Livewire à chaque changement
                                    this.quill.on('text-change', () => {
                                        this.content = this.quill.root.innerHTML;
                                    });
                                }
                             }"
                             x-init="initQuill()"
                        >
                            
                            <!-- BARRE D'OUTILS PERSONNALISÉE (STYLE RUBAN WORD) -->
                            <div id="toolbar-container" class="w-full max-w-4xl mb-4 !border-0 bg-white rounded-lg shadow-sm flex flex-wrap items-center justify-center gap-x-2">
                                
                                <!-- Groupe : Police -->
                                <span class="ql-formats flex items-center">
                                    <select class="ql-header">
                                        <option value="1">Titre 1</option>
                                        <option value="2">Titre 2</option>
                                        <option value="3">Titre 3</option>
                                        <option selected>Normal</option>
                                    </select>
                                </span>

                                <!-- Groupe : Style (Gras, Italique...) -->
                                <span class="ql-formats">
                                    <button class="ql-bold" title="Gras"></button>
                                    <button class="ql-italic" title="Italique"></button>
                                    <button class="ql-underline" title="Souligné"></button>
                                    <button class="ql-strike" title="Barré"></button>
                                </span>

                                <!-- Groupe : Couleur -->
                                <span class="ql-formats">
                                    <select class="ql-color" title="Couleur texte"></select>
                                    <select class="ql-background" title="Surlignage"></select>
                                </span>

                                <!-- Groupe : Paragraphe -->
                                <span class="ql-formats">
                                    <button class="ql-list" value="ordered" title="Liste numérotée"></button>
                                    <button class="ql-list" value="bullet" title="Puces"></button>
                                    <button class="ql-indent" value="-1" title="Diminuer retrait"></button>
                                    <button class="ql-indent" value="+1" title="Augmenter retrait"></button>
                                </span>

                                <!-- Groupe : Alignement -->
                                <span class="ql-formats">
                                    <button class="ql-align" value="" title="Gauche"></button>
                                    <button class="ql-align" value="center" title="Centrer"></button>
                                    <button class="ql-align" value="right" title="Droite"></button>
                                    <button class="ql-align" value="justify" title="Justifier"></button>
                                </span>

                                <!-- Groupe : Insertion (Lien, etc.) -->
                                <span class="ql-formats flex items-center gap-1">
                                    <button class="ql-link" title="Lien"></button>
                                </span>

                                <!-- Groupe : Nettoyage -->
                                <span class="ql-formats">
                                    <button class="ql-clean" title="Effacer le formatage"></button>
                                </span>
                            </div>

                            <!-- LA FEUILLE BLANCHE (ÉDITEUR) -->
                            <div class="w-full max-w-[21cm] shadow-xl">
                                <div x-ref="quillEditor" class="bg-white text-gray-900 font-serif text-base leading-relaxed h-auto" style="min-height: 29.7cm;"></div>
                            </div>

                            

                        </div>
                        @error('content') <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror
                    </div>

                </div>

                <!-- ... Code précédent (Éditeur Quill/TinyMCE) ... -->
                    
                    <!-- ============================================== -->
                    <!-- SECTION PIÈCES JOINTES (P.J.) -->
                    <!-- ============================================== -->
                    <div class="mt-8 border-t border-gray-100 pt-6">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            Pièces Jointes (P.J.)
                        </label>

                        <div class="space-y-4">
                            
                            <!-- Zone d'Upload (Style Zone Pointillée) -->
                            <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-gray-50 transition-colors relative">
                                <div class="space-y-1 text-center">
                                    <!-- Icône upload -->
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-yellow-600 hover:text-yellow-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-yellow-500">
                                            <span>Téléverser des fichiers</span>
                                            <input id="file-upload" wire:model="attachments" type="file" class="sr-only" multiple>
                                        </label>
                                        <p class="pl-1">ou glisser-déposer</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PDF, DOCX, PNG, JPG jusqu'à 10MB
                                    </p>
                                </div>

                                <!-- Indicateur de chargement pendant l'upload -->
                                <div wire:loading wire:target="attachments" class="absolute inset-0 bg-white/80 flex items-center justify-center">
                                    <div class="flex items-center text-yellow-600 font-semibold">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Upload en cours...
                                    </div>
                                </div>
                            </div>
                            @error('attachments.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                            <!-- Liste des fichiers sélectionnés (Cartes prévisu) -->
                            @if($attachments)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($attachments as $index => $file)
                                        <div class="relative flex items-center p-3 border border-gray-200 rounded-lg bg-gray-50 group hover:border-yellow-400 transition-colors">
                                            <!-- Icône selon le type -->
                                            <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-500 font-bold text-xs uppercase">
                                                {{ $file->extension() }}
                                            </div>
                                            
                                            <div class="ml-4 flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $file->getClientOriginalName() }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ round($file->getSize() / 1024, 2) }} KB
                                                </p>
                                            </div>

                                            <!-- Bouton Supprimer -->
                                            <button type="button" wire:click="removeAttachment({{ $index }})" class="ml-2 inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-gray-300 hover:bg-red-500 focus:outline-none transition-colors">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                        </div>
                    </div>
                    <!-- ============================================== -->
                    <!-- FIN SECTION P.J. -->
                    <!-- ============================================== -->

                </div> <!-- Fin de la div class="p-8 md:p-12 space-y-8" -->

                <!-- Pied de page -->
                <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 flex justify-between items-center text-xs text-gray-500">
                    <span>Auteur: {{ Auth::user()->name }}</span>
                    <span>Document généré par le système</span>
                </div>
            </div>
            
        </div>
    @endif
</div>
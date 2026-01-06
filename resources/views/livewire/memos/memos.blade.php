<div class="min-h-screen transition-colors duration-300 {{ $darkMode ? 'bg-[#121212] text-white' : 'bg-[#f9fafb] text-[#000000]' }} font-sans">

    <!-- Injection des variables CSS pour garantir la charte -->
    <style>
        :root {
            --c-gold: #daaf2c;
            --c-grey: {{ $darkMode ? '#a0a0a0' : '#707173' }};
            --c-black: {{ $darkMode ? '#ffffff' : '#000000' }};
            --c-bg-card: {{ $darkMode ? '#1e1e1e' : '#ffffff' }};
            --c-border: {{ $darkMode ? '#2d2d2d' : '#e5e7eb' }};
        }

        /* Utilitaires Charte */
        .text-charte-gold { color: var(--c-gold); }
        .text-charte-grey { color: var(--c-grey); }
        .bg-charte-gold { background-color: var(--c-gold); }
        .bg-charte-black { background-color: {{ $darkMode ? '#2d2d2d' : '#000000' }}; }

        /* Focus Rings personnalisés */
        .focus-gold:focus {
            --tw-ring-color: var(--c-gold);
            border-color: var(--c-gold);
            outline: none;
            box-shadow: 0 0 0 2px var(--c-gold);
        }

        /* Scrollbar fine */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: var(--c-gold); border-radius: 20px; }

        /* STYLE DES TABLEAUX DANS L'EDITEUR */
        .ql-editor table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .ql-editor table td,
        .ql-editor table th {
            border: 1px solid #000;
            padding: 8px;
            min-width: 30px;
            height: 25px;
        }

        /* QUILL DARK MODE OVERRIDE */
        .ql-toolbar.ql-snow {
            background-color: {{ $darkMode ? '#2d2d2d' : '#ffffff' }};
            border-color: var(--c-border) !important;
        }
        .ql-snow .ql-stroke { stroke: {{ $darkMode ? '#ffffff' : '#444' }}; }
        .ql-snow .ql-fill { fill: {{ $darkMode ? '#ffffff' : '#444' }}; }
        .ql-snow .ql-picker { color: {{ $darkMode ? '#ffffff' : '#444' }}; }
    </style>

    <!-- ========================================================== -->
    <!-- VUE 1 : LISTE DES MÉMOS -->
    <!-- ========================================================== -->
    @if(!$isCreating)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- En-tête -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight" style="color: var(--c-black);">Mémorandums</h1>
                    <p class="mt-1 text-sm font-medium" style="color: var(--c-grey);">Gérez, suivez et archivez vos communications internes.</p>
                </div>
                <div class="mt-4 md:mt-0">
                    @if(!in_array(auth()->user()->poste, ['Directeur', 'Sous-Directeur']))
                        <!-- Bouton Nouveau Mémo -->
                        <button wire:click="createMemo"
                            class="group inline-flex items-center justify-center px-6 py-3 text-base font-bold text-black transition-all duration-200 rounded-full shadow-lg transform hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#daaf2c]"
                            style="background-color: var(--c-gold);">
                            <svg class="w-5 h-5 mr-2 -ml-1 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Nouveau Mémo
                        </button>
                    @endif
                </div>
            </div>

            <!-- Navigation par Onglets -->
            <div class="border-b mb-8" style="border-color: var(--c-border);">
                <nav class="-mb-px flex space-x-8 overflow-x-auto custom-scrollbar" aria-label="Tabs">
                    @php
                        $tabs = [
                            'drafted' => 'Brouillons',
                            'document' => 'Mémos Envoyés',
                            'incoming' => 'Mémos Sortants',
                            'incoming2' => 'Mémos Entrants'
                        ];

                        if(auth()->user()->poste?->value == "Secretaire") { 
                            $tabs['blockout'] = 'Blocs Sortants';
                            $tabs['blockint'] = 'Blocs Entrants';
                        } else {
                            $tabs['favorites'] = 'Favoris';
                            $tabs['archives'] = 'Archives';
                        }
                    @endphp

                    @foreach($tabs as $key => $label)
                        <button wire:click="selectTab('{{ $key }}')"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors duration-200 flex items-center gap-2 group"
                            style="{{ $activeTab === $key 
                                ? 'border-color: var(--c-gold); color: var(--c-black);' 
                                : 'border-color: transparent; color: var(--c-grey);' }}">
                            <span>{{ $label }}</span>
                        </button>
                    @endforeach
                </nav>
            </div>

            <!-- Contenu dynamique des listes -->
            <div class="rounded-xl shadow-sm border min-h-[400px] p-6 transition-all" style="background-color: var(--c-bg-card); border-color: var(--c-border);">
                @switch($activeTab)
                    @case('drafted') <livewire:memos.drafted-memos wire:key="tab-drafted"/> @break
                    @case('document') <livewire:memos.docs-memos wire:key="tab-document"/> @break
                    @case('incoming') <livewire:memos.incoming-memos wire:key="tab-incoming"/> @break
                    @case('incoming2') <livewire:memos.incoming2-memos wire:key="tab-incoming2"/> @break
                    @case('blockout') <livewire:memos.blockout-memos wire:key="tab-blockout"/> @break
                    @case('blockint') <livewire:memos.blockint-memos wire:key="tab-blockint"/> @break
                    @case('favorites') <livewire:favorites.favorite-memos wire:key="tab-favorites"/> @break
                    @case('archives') <livewire:memos.archives wire:key="tab-archives"/> @break 
                @endswitch
            </div>
        </div>

    <!-- ========================================================== -->
    <!-- VUE 2 : ÉDITEUR DE MÉMO (CRÉATION/EDITION) -->
    <!-- ========================================================== -->
    @else
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in-up">
            
            <!-- Barre d'actions -->
            <div class="mb-8 border rounded-xl shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-all" style="background-color: var(--c-bg-card); border-color: var(--c-border);">
                
                <button wire:click="cancelCreation" type="button" class="group flex items-center transition-colors">
                    <div class="mr-3 h-10 w-10 rounded-full flex items-center justify-center transition-colors duration-200 {{ $darkMode ? 'bg-white/5 group-hover:bg-[#daaf2c]/20' : 'bg-gray-100 group-hover:bg-[#daaf2c]/20' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col items-start">
                        <span class="font-bold text-base" style="color: var(--c-black);">Retour</span>
                        <span class="text-xs font-normal" style="color: var(--c-grey);">Vers la liste</span>
                    </div>
                </button>
                
                <div class="flex items-center justify-end space-x-3 w-full sm:w-auto">
                    <button wire:click="cancelCreation" type="button" class="px-5 py-2.5 text-sm font-medium rounded-lg border transition-all {{ $darkMode ? 'bg-white/5 border-gray-700 text-white hover:bg-white/10' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                        Annuler
                    </button>
                    
                    <button wire:click="save" wire:loading.attr="disabled" type="button" 
                        class="relative inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-lg shadow-md text-black transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        style="background-color: var(--c-gold);">
                        
                        <span wire:loading.remove wire:target="save" class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Enregistrer
                        </span>

                        <span wire:loading wire:target="save" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Traitement...
                        </span>
                    </button>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-md bg-red-50 p-4 border-l-4 border-red-500 shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Attention</h3>
                            <ul class="mt-2 list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="text-sm text-red-700">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- FEUILLE DE PAPIER -->
            <div class="bg-white rounded-lg shadow-2xl overflow-hidden relative" style="border: 1px solid var(--c-border);">
                
                <div class="px-8 py-6 flex justify-between items-center" 
                     style="background-color: {{ $darkMode ? '#1e1e1e' : '#000000' }}; color: white; border-bottom: 4px solid var(--c-gold);">
                    <div>
                        <h2 class="text-2xl font-bold tracking-wider uppercase" style="font-family: 'Times New Roman', serif;">Mémorandum</h2>
                        <p class="text-xs font-bold tracking-widest mt-1" style="color: var(--c-gold);">INTERNE / CONFIDENTIEL</p>
                    </div>
                    <div class="text-right opacity-80">
                        <p class="text-sm">Date : {{ now()->format('d/m/Y') }}</p>
                    </div>
                </div>

                <div class="p-8 md:p-12 space-y-10">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-2">
                            <label for="concern" class="block text-xs font-bold uppercase tracking-wide mb-1" style="color: #707173;">Pour (Concerne)</label>
                            <input type="text" wire:model="concern" id="concern" 
                                class="block w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-black placeholder-gray-300 sm:text-lg transition-colors focus:ring-0 focus:border-[#daaf2c]" 
                                placeholder="Ex: Direction Générale...">
                        </div>

                        <div class="space-y-2">
                            <label for="object" class="block text-xs font-bold uppercase tracking-wide mb-1" style="color: #707173;">Objet</label>
                            <input type="text" wire:model="object" id="object" 
                                class="block w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-black font-bold placeholder-gray-300 sm:text-lg transition-colors focus:ring-0 focus:border-[#daaf2c]" 
                                placeholder="Sujet principal...">
                        </div>
                    </div>

                    <!-- DISTRIBUTION LIST -->
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <h3 class="text-sm font-bold uppercase tracking-wide mb-4 flex items-center text-black">
                            <svg class="w-4 h-4 mr-2" style="color: var(--c-gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            Liste de Distribution
                        </h3>

                        <div class="flex flex-col md:flex-row gap-4 mb-4">
                            <div class="flex-1">
                                <select wire:model="newRecipientEntity" class="block w-full rounded-md border-gray-300 shadow-sm focus-gold text-sm text-black">
                                    <option value="">-- Destinataire --</option>
                                    @foreach($entities as $entity)
                                        <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex-1">
                                <select wire:model="newRecipientAction" class="block w-full rounded-md border-gray-300 shadow-sm focus-gold text-sm text-black">
                                    <option value="">-- Action requise --</option>
                                    @foreach($actionsList as $action)
                                        <option value="{{ $action }}">{{ $action }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button wire:click="addRecipient" type="button" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Ajouter
                            </button>
                        </div>

                        @if(count($recipients) > 0)
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300 bg-white">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="py-2 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase">Entité</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Action</th>
                                            <th class="relative py-2 pl-3 pr-4 sm:pr-6"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($recipients as $index => $recipient)
                                            <tr>
                                                <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm font-medium text-gray-900">{{ $recipient['entity_name'] }}</td>
                                                <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-500">
                                                    <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-black ring-1 ring-inset ring-[#daaf2c]/50">
                                                        {{ $recipient['action'] }}
                                                    </span>
                                                </td>
                                                <td class="relative whitespace-nowrap py-3 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                    <button wire:click="removeRecipient({{ $index }})" class="text-gray-400 hover:text-red-600">
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
                        @endif
                    </div>

                    <!-- ÉDITEUR QUILL -->
                    <div class="pt-2">
                        <label class="block text-xs font-bold uppercase tracking-wide mb-3 flex justify-between items-center text-gray-500">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Corps du Document
                            </span>
                        </label>

                        <div wire:ignore 
                            class="flex flex-col items-center rounded-xl p-4 border shadow-inner transition-colors"
                            style="background-color: {{ $darkMode ? '#2d2d2d' : '#f3f4f6' }}; border-color: var(--c-border);"
                            x-data="{
                                content: @entangle('content'),
                                quill: null,
                                initQuill() {
                                    this.quill = new Quill(this.$refs.quillEditor, {
                                        theme: 'snow',
                                        placeholder: 'Commencez à rédiger...',
                                        modules: { toolbar: '#toolbar-container' }
                                    });
                                    if (this.content) { this.quill.root.innerHTML = this.content; }
                                    this.quill.on('text-change', () => { this.content = this.quill.root.innerHTML; });
                                }
                            }"
                            x-init="initQuill()">
                            
                            <!-- Toolbar -->
                            <div id="toolbar-container" class="w-full max-w-4xl mb-6 bg-white rounded-t-lg shadow-sm flex flex-wrap items-center justify-center gap-1 border border-gray-200 p-2 z-20 sticky top-0">
                                <span class="ql-formats">
                                    <select class="ql-font">
                                        <option value="tahoma" selected>Tahoma</option>
                                        <option value="arial">Arial</option>
                                        <option value="timesnewroman">Times New Roman</option>
                                    </select>
                                    <select class="ql-size">
                                        <option value="12pt" selected>12pt</option>
                                        <option value="14pt">14pt</option>
                                        <option value="18pt">18pt</option>
                                    </select>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-bold"></button>
                                    <button class="ql-italic"></button>
                                    <button class="ql-underline"></button>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-list" value="ordered"></button>
                                    <button class="ql-list" value="bullet"></button>
                                </span>
                            </div>

                            <!-- Zone A4 -->
                            <div class="relative w-full max-w-[21cm] bg-white shadow-2xl border border-gray-300">
                                <div x-ref="quillEditor" class="text-gray-900 min-h-[29.7cm]"></div>
                            </div>
                        </div>
                    </div>

                    <style>
                        .ql-container.ql-snow { border: none !important; }
                        .ql-editor { padding: 50px 70px !important; min-height: 29.7cm; font-family: 'Tahoma', sans-serif; line-height: 1.5; font-size: 12pt; }
                    </style>

                    <!-- ATTACHMENTS SECTION -->
                    <div class="mt-8 border-t border-gray-100 pt-6">
                        <label class="block text-xs font-bold uppercase tracking-wide mb-3 flex items-center gap-2" style="color: #707173;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                            Pièces Jointes (P.J.)
                        </label>

                        <div class="space-y-4">
                            <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-yellow-50/20 hover:border-[#daaf2c] transition-colors relative">
                                <div class="space-y-1 text-center">
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-bold text-[#daaf2c] focus-within:ring-2 focus-within:ring-[#daaf2c]">
                                            <span>Téléverser des fichiers</span>
                                            <input id="file-upload" wire:model="attachments" type="file" class="sr-only" multiple>
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">PDF, DOCX, PNG, JPG jusqu'à 10MB</p>
                                </div>
                            </div>

                            @if($attachments)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($attachments as $index => $file)
                                        <div class="relative flex items-center p-3 border border-gray-200 rounded-lg bg-gray-50 group hover:border-[#daaf2c] transition-colors">
                                            <div class="ml-4 flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $file->getClientOriginalName() }}</p>
                                            </div>
                                            <button type="button" wire:click="removeAttachment({{ $index }})" class="ml-2 p-1.5 rounded-full text-white bg-gray-300 hover:bg-red-500">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 flex justify-between items-center text-xs text-gray-500">
                    <span>Auteur: <strong class="text-gray-900">{{ Auth::user()->name }}</strong></span>
                    <span>Document généré par le système</span>
                </div>
            </div>
        </div>
    @endif
</div>
<div class="min-h-screen bg-[#f9fafb] text-[#000000] font-sans">

    <!-- Injection des variables CSS pour garantir la charte -->
    <style>
        :root {
            --c-gold: #daaf2c;
            --c-grey: #707173;
            --c-black: #000000;
        }
        /* Utilitaires Charte */
        .text-charte-gold { color: var(--c-gold); }
        .text-charte-grey { color: var(--c-grey); }
        .bg-charte-gold { background-color: var(--c-gold); }
        .bg-charte-black { background-color: var(--c-black); }
        
        /* Focus Rings personnalisés */
        .focus-gold:focus {
            --tw-ring-color: var(--c-gold);
            border-color: var(--c-gold);
            outline: none;
            box-shadow: 0 0 0 2px var(--c-gold);
        }

        
    /* ... vos styles existants ... */

    /* STYLE DES TABLEAUX DANS L'EDITEUR */
    .ql-editor table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }
    .ql-editor table td, 
    .ql-editor table th {
        border: 1px solid #000; /* Bordure noire */
        padding: 8px;
        min-width: 30px;
        height: 25px;
    }


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
                  <!-- On vérifie que le poste N'EST PAS dans la liste des exclus -->
                    @if(!in_array(auth()->user()->poste, ['Secretaire', 'Directeur', 'Sous-Directeur']))
                        
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
            <div class="border-b border-gray-200 mb-8">
                <nav class="-mb-px flex space-x-8 overflow-x-auto custom-scrollbar" aria-label="Tabs">
                    @php
                        $tabs = [
                            'incoming' => 'Mémos Sortants',
                            'incoming2' => 'Mémos Entrants',
                        ];
                        
                        // Note: J'ai corrigé "Secretaire" en "secretaire" (minuscule) ou utilise Str::contains si nécessaire, 
                        // mais gardons ta logique exacte pour l'instant.
                        if(auth()->user()->poste == 'Secretaire') { 
                            $tabs['blockout'] = 'Blocs Mémos Sortants';
                            $tabs['blockint'] = 'Blocs Mémos Entrants';
                        } else {
                            $tabs['drafted'] = 'Mes Mémos';
                            $tabs['document'] = 'Mémos Envoyés';
                            $tabs['favorites'] = 'Favoris';
                            $tabs['archives'] = 'Archives';
                        }
                    @endphp

                    @foreach($tabs as $key => $label)
                        <button
                            wire:click="selectTab('{{ $key }}')"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors duration-200 flex items-center gap-2 group"
                            style="{{ $activeTab === $key 
                                ? 'border-color: var(--c-gold); color: var(--c-black);' 
                                : 'border-color: transparent; color: var(--c-grey);' }}"
                        >
                            <!-- Gestion des Icônes par Clé -->
                            @switch($key)
                                @case('incoming')
                                    <!-- Mémos Sortants : Avion en papier -->
                                    <svg class="w-5 h-5 {{ $activeTab === $key ? 'text-yellow-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                    </svg>
                                    @break

                                @case('incoming2')
                                    <!-- Mémos Entrants : Boîte de réception -->
                                    <svg class="w-5 h-5 {{ $activeTab === $key ? 'text-yellow-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    @break

                                @case('blockout')
                                    <!-- Blocs Sortants : Piles de dossiers vers le haut -->
                                    <svg class="w-5 h-5 {{ $activeTab === $key ? 'text-yellow-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M9 12l3-3m0 0l3 3m-3-3v10.125" />
                                    </svg>
                                    @break

                                @case('blockint')
                                    <!-- Blocs Entrants : Piles de dossiers vers le bas -->
                                    <svg class="w-5 h-5 {{ $activeTab === $key ? 'text-yellow-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15m0-3l-3 3m0 0l-3-3m3 3V1.5" />
                                    </svg>
                                    @break

                                @case('drafted')
                                    <!-- Mes Mémos (Brouillons) : Crayon -->
                                    <svg class="w-5 h-5 {{ $activeTab === $key ? 'text-yellow-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                    @break

                                @case('document')
                                    <!-- Mémos Envoyés : Document coché -->
                                    <svg class="w-5 h-5 {{ $activeTab === $key ? 'text-yellow-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    @break

                                @case('favorites')
                                    <!-- Favoris : Étoile -->
                                    <svg class="w-5 h-5 {{ $activeTab === $key ? 'text-yellow-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="{{ $activeTab === $key ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                    </svg>
                                    @break

                                @case('archives')
                                    <!-- Archives : Boîte d'archives -->
                                    <svg class="w-5 h-5 {{ $activeTab === $key ? 'text-yellow-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                    </svg>
                                    @break
                            @endswitch

                            <span>{{ $label }}</span>
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
            <div class="mb-8 bg-white border border-gray-100 rounded-xl shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-all duration-300">
                
                <!-- Bouton Retour -->
                <button wire:click="cancelCreation" type="button" class="group flex items-center text-gray-500 hover:text-black transition-colors">
                    <div class="mr-3 h-10 w-10 rounded-full bg-gray-100 group-hover:bg-[#daaf2c]/20 group-hover:text-[#daaf2c] flex items-center justify-center transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col items-start">
                        <span class="font-bold text-base text-black">Retour</span>
                        <span class="text-xs font-normal" style="color: var(--c-grey);">Vers la liste</span>
                    </div>
                </button>
                
                <!-- Groupe Actions -->
                <div class="flex items-center justify-end space-x-3 w-full sm:w-auto border-t sm:border-t-0 border-gray-100 pt-3 sm:pt-0">
                    <button wire:click="cancelCreation" type="button" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200">
                        Annuler
                    </button>
                    
                    <!-- BOUTON ENREGISTRER (Charte: Fond Noir ou Or) -->
                    <!-- Choix : Fond Or, Texte Noir pour l'action principale -->
                    <button wire:click="save" wire:loading.attr="disabled" type="button" 
                        class="relative inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-lg shadow-md text-black transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#daaf2c]"
                        style="background-color: var(--c-gold);">
                        
                        <!-- Contenu visible quand ça ne charge pas -->
                        <span wire:loading.remove wire:target="save" class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Enregistrer
                        </span>

                        <!-- Contenu visible PENDANT le chargement -->
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

            <!-- Message d'erreur global -->
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

            <!-- FEUILLE DE PAPIER (FORMULAIRE PRINCIPAL) -->
            <div class="bg-white rounded-lg shadow-2xl overflow-hidden relative" style="border: 1px solid #e5e7eb;">
                
                <!-- Bandeau décoratif (Noir & Or) -->
                <div class="px-8 py-6 flex justify-between items-center" 
                     style="background-color: var(--c-black); color: white; border-bottom: 4px solid var(--c-gold);">
                    <div>
                        <h2 class="text-2xl font-bold tracking-wider uppercase" style="font-family: 'Times New Roman', serif;">Mémorandum</h2>
                        <p class="text-xs font-bold tracking-widest mt-1" style="color: var(--c-gold);">INTERNE / CONFIDENTIEL</p>
                    </div>
                    <div class="text-right opacity-80">
                        <p class="text-sm">Date : {{ now()->format('d/m/Y') }}</p>
                    </div>
                </div>

                <!-- Corps du formulaire -->
                <div class="p-8 md:p-12 space-y-10">
                    
                    <!-- 1. Section Méta-données -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <!-- Destinataire principal / Concerne -->
                        <div class="space-y-2">
                            <label for="concern" class="block text-xs font-bold uppercase tracking-wide mb-1" style="color: var(--c-grey);">Pour (Concerne)</label>
                            <input type="text" wire:model="concern" id="concern" 
                                class="block w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-black placeholder-gray-300 sm:text-lg transition-colors focus:ring-0 focus:border-[#daaf2c]" 
                                style="color: var(--c-black);"
                                placeholder="Ex: Direction Générale...">
                            @error('concern') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Objet -->
                        <div class="space-y-2">
                            <label for="object" class="block text-xs font-bold uppercase tracking-wide mb-1" style="color: var(--c-grey);">Objet</label>
                            <input type="text" wire:model="object" id="object" 
                                class="block w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-black font-bold placeholder-gray-300 sm:text-lg transition-colors focus:ring-0 focus:border-[#daaf2c]" 
                                style="color: var(--c-black);"
                                placeholder="Sujet principal...">
                            @error('object') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- 2. SECTION DESTINATAIRES -->
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <h3 class="text-sm font-bold uppercase tracking-wide mb-4 flex items-center" style="color: var(--c-black);">
                            <svg class="w-4 h-4 mr-2" style="color: var(--c-gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Liste de Distribution
                        </h3>

                        <!-- Formulaire d'ajout -->
                        <div class="flex flex-col md:flex-row gap-4 mb-4">
                            <!-- Select Entité -->
                            <div class="flex-1">
                                <select wire:model="newRecipientEntity" class="block w-full rounded-md border-gray-300 shadow-sm focus-gold text-sm">
                                    <option value="">-- Sélectionner un destinataire --</option>
                                    @foreach($entities as $entity)
                                        <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                                    @endforeach
                                </select>
                                @error('newRecipientEntity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Select Action -->
                            <div class="flex-1">
                                <select wire:model="newRecipientAction" class="block w-full rounded-md border-gray-300 shadow-sm focus-gold text-sm">
                                    <option value="">-- Action requise --</option>
                                    @foreach($actionsList as $action)
                                        <option value="{{ $action }}">{{ $action }}</option>
                                    @endforeach
                                </select>
                                @error('newRecipientAction') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Bouton Ajouter (Noir) -->
                            <button wire:click="addRecipient" type="button" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
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
                                                    <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-black ring-1 ring-inset ring-[#daaf2c]/50">
                                                        {{ $recipient['action'] }}
                                                    </span>
                                                </td>
                                                <td class="relative whitespace-nowrap py-3 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                    <button wire:click="removeRecipient({{ $index }})" class="text-gray-400 hover:text-red-600 transition-colors">
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
<div class="pt-2">
    <label class="block text-xs font-bold uppercase tracking-wide mb-3 flex items-center gap-2" style="color: var(--c-grey);">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Corps du Document
    </label>

    <div wire:ignore 
         class="flex flex-col items-center bg-gray-50 rounded-lg p-4 border border-gray-200"
         x-data="{
            content: @entangle('content'),
            quill: null,
            
            initQuill() {
                // Initialisation de Quill
                this.quill = new Quill(this.$refs.quillEditor, {
                    theme: 'snow',
                    placeholder: 'Rédigez votre mémo ici...',
                    modules: {
                        toolbar: '#toolbar-container',
                        table: true 
                    }
                });

                // Charger le contenu initial
                if (this.content) {
                    this.quill.root.innerHTML = this.content;
                }
                
                // Update Livewire on change
                this.quill.on('text-change', () => {
                    this.content = this.quill.root.innerHTML;
                });
            },

            insertCustomTable() {
                let rows = prompt('Nombre de lignes ?', 3);
                let cols = prompt('Nombre de colonnes ?', 3);

                if (!rows || !cols || isNaN(rows) || isNaN(cols)) return;

                // Génération du HTML du tableau
                let tableRows = '';
                for (let r = 0; r < rows; r++) {
                    tableRows += '<tr>';
                    for (let c = 0; c < cols; c++) {
                        tableRows += '<td style=\'border: 1px solid #000000; padding: 8px; min-width: 50px; height: 30px;\'>&nbsp;</td>';
                    }
                    tableRows += '</tr>';
                }

                let tableHTML = `<br><table style='width:100%; border-collapse: collapse; border: 1px solid #000000; margin: 10px 0;'><tbody>${tableRows}</tbody></table><p><br></p>`;

                // Insertion Directe
                this.quill.focus();
                const selection = window.getSelection();
                
                if (selection.rangeCount > 0) {
                    let range = selection.getRangeAt(0);
                    
                    // Vérifier si le curseur est dans l'éditeur
                    if (!this.$refs.quillEditor.contains(range.commonAncestorContainer)) {
                        range = document.createRange();
                        range.selectNodeContents(this.quill.root);
                        range.collapse(false);
                    }

                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = tableHTML;
                    const fragment = document.createDocumentFragment();
                    while (tempDiv.firstChild) {
                        fragment.appendChild(tempDiv.firstChild);
                    }

                    const lastNode = fragment.lastChild;
                    range.deleteContents();
                    range.insertNode(fragment);

                    // Placer le curseur après le tableau
                    const newRange = document.createRange();
                    newRange.setStartAfter(lastNode);
                    newRange.setEndAfter(lastNode);
                    selection.removeAllRanges();
                    selection.addRange(newRange);

                    // Sync
                    this.quill.update();
                    this.content = this.quill.root.innerHTML;
                }
            }
         }"
         x-init="initQuill()">
        
        <!-- BARRE D'OUTILS -->
        <div id="toolbar-container" class="w-full max-w-4xl mb-4 !border-0 bg-white rounded-lg shadow-sm flex flex-wrap items-center justify-center gap-x-2 border border-gray-200">
            <span class="ql-formats">
                <select class="ql-header">
                    <option value="1">Titre 1</option>
                    <option value="2">Titre 2</option>
                    <option selected>Normal</option>
                </select>
            </span>
            <span class="ql-formats">
                <button class="ql-bold"></button>
                <button class="ql-italic"></button>
                <button class="ql-underline"></button>
            </span>
            <span class="ql-formats">
                <select class="ql-color"></select>
                <select class="ql-background"></select>
            </span>
            <span class="ql-formats">
                <button class="ql-list" value="ordered"></button>
                <button class="ql-list" value="bullet"></button>
                <button class="ql-align" value="center"></button>
                <button class="ql-align" value="justify"></button>
            </span>

            <!-- BOUTON TABLEAU CUSTOM -->
            <span class="ql-formats border-l pl-2">
                <button type="button" @click.prevent="insertCustomTable()" class="hover:bg-gray-100 p-1 rounded" title="Insérer un tableau">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="3" y1="9" x2="21" y2="9"></line>
                        <line x1="3" y1="15" x2="21" y2="15"></line>
                        <line x1="9" y1="3" x2="9" y2="21"></line>
                        <line x1="15" y1="3" x2="15" y2="21"></line>
                    </svg>
                </button>
            </span>

            <span class="ql-formats">
                <button class="ql-clean"></button>
            </span>
        </div>

       

                            <!-- LA FEUILLE BLANCHE -->
                            <div class="w-full max-w-[21cm] shadow-xl ring-1 ring-gray-900/5">
                                <div x-ref="quillEditor" class="bg-white text-gray-900 font-serif text-base leading-relaxed h-auto" style="min-height: 29.7cm;"></div>
                            </div>
                        </div>
                        @error('content') <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- 4. SECTION PIÈCES JOINTES (P.J.) -->
                    <div class="mt-8 border-t border-gray-100 pt-6">
                        <label class="block text-xs font-bold uppercase tracking-wide mb-3 flex items-center gap-2" style="color: var(--c-grey);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            Pièces Jointes (P.J.)
                        </label>

                        <div class="space-y-4">
                            <!-- Zone d'Upload (Dashed with Gold Hover) -->
                            <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-yellow-50/20 hover:border-[#daaf2c] transition-colors relative">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-bold focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-[#daaf2c]" style="color: var(--c-gold);">
                                            <span>Téléverser des fichiers</span>
                                            <input id="file-upload" wire:model="attachments" type="file" class="sr-only" multiple>
                                        </label>
                                        <p class="pl-1">ou glisser-déposer</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PDF, DOCX, PNG, JPG jusqu'à 10MB
                                    </p>
                                </div>

                                <!-- Loading -->
                                <div wire:loading wire:target="attachments" class="absolute inset-0 bg-white/80 flex items-center justify-center">
                                    <div class="flex items-center font-semibold" style="color: var(--c-gold);">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Upload en cours...
                                    </div>
                                </div>
                            </div>
                            @error('attachments.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                            <!-- Preview Fichiers -->
                            @if($attachments)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($attachments as $index => $file)
                                        <div class="relative flex items-center p-3 border border-gray-200 rounded-lg bg-gray-50 group hover:border-[#daaf2c] transition-colors">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-500 font-bold text-xs uppercase">
                                                {{ $file->extension() }}
                                            </div>
                                            <div class="ml-4 flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $file->getClientOriginalName() }}</p>
                                                <p class="text-xs text-gray-500">{{ round($file->getSize() / 1024, 2) }} KB</p>
                                            </div>
                                            <button type="button" wire:click="removeAttachment({{ $index }})" class="ml-2 inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-gray-300 hover:bg-red-500 transition-colors">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                <!-- Pied de page -->
                <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 flex justify-between items-center text-xs text-gray-500">
                    <span>Auteur: <strong class="text-gray-900">{{ Auth::user()->name }}</strong></span>
                    <span>Document généré par le système</span>
                </div>
            </div>
        </div>
    @endif
</div>
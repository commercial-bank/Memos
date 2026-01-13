<div class="min-h-screen transition-colors duration-300 {{ $darkMode ? 'bg-[#121212] text-white' : 'bg-[#f9fafb] text-[#000000]' }} font-sans">

    <!-- Styles Dynamiques -->
    <style>
        :root {
            --c-gold: #daaf2c;
            --c-grey: {{ $darkMode ? '#a0a0a0' : '#707173' }};
            --c-black: {{ $darkMode ? '#ffffff' : '#000000' }};
            --c-bg-card: {{ $darkMode ? '#1e1e1e' : '#ffffff' }};
            --c-border: {{ $darkMode ? '#2d2d2d' : '#e5e7eb' }};
        }
        .focus-gold:focus { --tw-ring-color: var(--c-gold); border-color: var(--c-gold); outline: none; box-shadow: 0 0 0 2px var(--c-gold); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: var(--c-gold); border-radius: 20px; }
        
        /* Quill */
        .ql-editor table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .ql-editor table td, .ql-editor table th { border: 1px solid #000; padding: 8px; min-width: 30px; height: 25px; }
        .ql-toolbar.ql-snow { background-color: {{ $darkMode ? '#2d2d2d' : '#ffffff' }}; border-color: var(--c-border) !important; }
        .ql-snow .ql-stroke { stroke: {{ $darkMode ? '#ffffff' : '#444' }}; }
        .ql-snow .ql-fill { fill: {{ $darkMode ? '#ffffff' : '#444' }}; }
        .ql-snow .ql-picker { color: {{ $darkMode ? '#ffffff' : '#444' }}; }
        .ql-container.ql-snow { border: none !important; }
        .ql-editor { padding: 50px 70px !important; min-height: 29.7cm; font-family: 'Tahoma', sans-serif; line-height: 1.5; font-size: 12pt; }

        /* --- CONFIGURATION QUILL --- */

/* 1. Supprimer la bordure par défaut de Quill */
.ql-container.ql-snow { 
    border: none !important; 
}

/* 2. Configurer la zone d'édition pour ressembler à du papier A4 */
.ql-editor {
    padding: 2.5cm 2cm !important; /* Marges standard A4 */
    min-height: 29.7cm;
    background-color: white;
    
    /* Police par défaut stricte */
    font-family: 'Tahoma', sans-serif; 
    font-size: 14px; 
    line-height: 1.5;
    color: #000000;
}

    /* 3. Forcer l'affichage correct des polices dans le menu déroulant */
    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="Tahoma"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="Tahoma"]::before { content: 'Tahoma'; font-family: 'Tahoma'; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="Arial"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="Arial"]::before { content: 'Arial'; font-family: 'Arial'; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="Times New Roman"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="Times New Roman"]::before { content: 'Times New Roman'; font-family: 'Times New Roman'; }

    .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="Verdana"]::before,
    .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="Verdana"]::before { content: 'Verdana'; font-family: 'Verdana'; }

    /* 4. Forcer l'affichage correct des tailles */
    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="12px"]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="12px"]::before { content: '12 pt'; }

    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="14px"]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="14px"]::before { content: '14 pt'; }

    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="16px"]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="16px"]::before { content: '16 pt'; }

    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="18px"]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="18px"]::before { content: '18 pt'; }

    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="20px"]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="20px"]::before { content: '20 pt'; }


    /* --- PERSONNALISATION DES TITRES (HEADERS) --- */

    /* Apparence dans le menu déroulant */
    .ql-snow .ql-picker.ql-header .ql-picker-label::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item::before {
        content: 'Normal'; /* Par défaut */
    }

    .ql-snow .ql-picker.ql-header .ql-picker-label[data-value="1"]::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item[data-value="1"]::before {
        content: 'Titre 1 (H1)';
        font-size: 18px;
        font-weight: bold;
    }

    .ql-snow .ql-picker.ql-header .ql-picker-label[data-value="2"]::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item[data-value="2"]::before {
        content: 'Titre 2 (H2)';
        font-size: 16px;
        font-weight: bold;
        padding-left: 10px;
    }

    .ql-snow .ql-picker.ql-header .ql-picker-label[data-value="3"]::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item[data-value="3"]::before {
        content: 'Titre 3 (H3)';
        font-size: 14px;
        font-weight: bold;
        padding-left: 20px;
    }

    .ql-snow .ql-picker.ql-header .ql-picker-label[data-value="4"]::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item[data-value="4"]::before { content: 'Titre 4'; padding-left: 30px; }
    .ql-snow .ql-picker.ql-header .ql-picker-label[data-value="5"]::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item[data-value="5"]::before { content: 'Titre 5'; padding-left: 40px; }
    .ql-snow .ql-picker.ql-header .ql-picker-label[data-value="6"]::before,
    .ql-snow .ql-picker.ql-header .ql-picker-item[data-value="6"]::before { content: 'Titre 6'; padding-left: 50px; }

    /* --- RENDU VISUEL DANS L'EDITEUR (Simulation A4) --- */
    /* Pour que l'utilisateur voie à quoi ressemblent les titres */
    .ql-editor h1 { font-size: 2em; margin-bottom: 0.5em; font-weight: bold; }
    .ql-editor h2 { font-size: 1.5em; margin-bottom: 0.5em; font-weight: bold; }
    .ql-editor h3 { font-size: 1.17em; margin-bottom: 0.5em; font-weight: bold; }
    .ql-editor h4 { font-size: 1em; margin-bottom: 0.5em; font-weight: bold; text-decoration: underline; }
    .ql-editor h5 { font-size: 0.83em; margin-bottom: 0.5em; font-weight: bold; }
    .ql-editor h6 { font-size: 0.67em; margin-bottom: 0.5em; font-weight: bold; }


    /* --- GESTION DES TABLEAUX (AFFICHAGE & IMPRESSION) --- */
    .ql-editor table {
        width: 100%;
        border-collapse: collapse; /* Essentiel pour des bordures nettes */
        margin-bottom: 15px;
        table-layout: fixed; /* Force les colonnes à respecter la largeur */
    }

    .ql-editor table td {
        border: 1px solid #000; /* Bordure noire pour l'impression */
        padding: 6px 8px;
        min-width: 30px;
        vertical-align: top;
    }

    /* En mode sombre, on adapte pour l'éditeur, mais l'impression restera nette */
    @media (prefers-color-scheme: dark) {
        /* Si vous voulez que le tableau soit blanc en mode sombre dans l'éditeur,
        laissez tel quel car .ql-editor a background-color: white */
    }

    </style>

    <!-- ========================================================== -->
    <!-- VUE 1 : LISTE DES MÉMOS -->
    <!-- ========================================================== -->
    @if(!$isCreating)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight" style="color: var(--c-black);">Mémorandums</h1>
                    <p class="mt-1 text-sm font-medium" style="color: var(--c-grey);">Gérez, suivez et archivez vos communications internes.</p>
                </div>
                <div class="mt-4 md:mt-0">
                    @if(!in_array(auth()->user()->poste, ['Directeur', 'Sous-Directeur']))
                        <button wire:click="createMemo"
                            class="group inline-flex items-center justify-center px-6 py-3 text-base font-bold text-black transition-all duration-200 rounded-full shadow-lg transform hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#daaf2c]"
                            style="background-color: var(--c-gold);">
                            <svg class="w-5 h-5 mr-2 -ml-1 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Nouveau Mémo
                        </button>
                    @endif
                </div>
            </div>

            <!-- Onglets -->
            <div class="border-b mb-8" style="border-color: {{ $darkMode ? '#2d2d2d' : '#ebe5e9' }}; width: 1300px;">
                <nav class="-mb-px flex space-x-6 overflow-x-auto custom-scrollbar pb-1" aria-label="Tabs">
                    @php
                        $tabs = [
                            'drafted' => ['label' => 'Brouillons', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>'],
                            'document' => ['label' => 'Mémos Envoyés', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>'],
                            'incoming' => ['label' => 'Mémos Sortants', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>'],
                            'incoming2' => ['label' => 'Mémos Entrants', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>'],
                            'favorites' =>  ['label' => 'Favoris', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>'],
                            'archives'  => ['label' => 'Archives', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>']
                            ];
                        $userPoste = auth()->user()->poste?->value ?? auth()->user()->poste;
                        if (str_contains($userPoste, 'Secretaire')) { 
                            $tabs['blockout'] = ['label' => 'Blocs Sortants', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path>'];
                            $tabs['blockint'] = ['label' => 'Blocs Entrants', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>'];
                        } 
                    @endphp

                    @foreach($tabs as $key => $data)
                        <button wire:click="selectTab('{{ $key }}')"
                            class="whitespace-nowrap py-4 px-3 border-b-2 font-bold text-sm transition-all duration-200 flex items-center gap-2 group outline-none focus:outline-none"
                            style="{{ $activeTab === $key ? 'border-color: #daaf2c; color: ' . ($darkMode ? '#ffffff' : '#000000') . ';' : 'border-color: transparent; color: ' . ($darkMode ? '#a0a0a0' : '#6b7280') . ';' }}">
                            <svg class="w-5 h-5 transition-colors duration-200 {{ $activeTab === $key ? 'text-[#daaf2c]' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $data['icon'] !!}</svg>
                            <span>{{ $data['label'] }}</span>
                        </button>
                    @endforeach
                </nav>
            </div>

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
    <!-- VUE 2 : CRÉATION / ÉDITION -->
    <!-- ========================================================== -->
    @else
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in-up">
            
            <!-- Actions -->
            <div class="mb-8 border rounded-xl shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-all" style="background-color: var(--c-bg-card); border-color: var(--c-border);">
                <button wire:click="cancelCreation" type="button" class="group flex items-center transition-colors">
                    <div class="mr-3 h-10 w-10 rounded-full flex items-center justify-center transition-colors duration-200 {{ $darkMode ? 'bg-white/5 group-hover:bg-[#daaf2c]/20' : 'bg-gray-100 group-hover:bg-[#daaf2c]/20' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </div>
                    <div class="flex flex-col items-start">
                        <span class="font-bold text-base" style="color: var(--c-black);">Retour</span>
                        <span class="text-xs font-normal" style="color: var(--c-grey);">Vers la liste</span>
                    </div>
                </button>
                <div class="flex items-center justify-end space-x-3 w-full sm:w-auto">
                    <button wire:click="cancelCreation" type="button" class="px-5 py-2.5 text-sm font-medium rounded-lg border transition-all {{ $darkMode ? 'bg-white/5 border-gray-700 text-white hover:bg-white/10' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' }}">Annuler</button>
                    <button wire:click="save" wire:loading.attr="disabled" type="button" class="relative inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-lg shadow-md text-black transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50" style="background-color: var(--c-gold);">
                        <span wire:loading.remove wire:target="save" class="flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>Enregistrer</span>
                        <span wire:loading wire:target="save" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Traitement...</span>
                    </button>
                </div>
            </div>

            <!-- Errors -->
            @if ($errors->any())
                <div class="mb-6 rounded-md bg-red-50 p-4 border-l-4 border-red-500 shadow-sm">
                    <div class="flex"><div class="flex-shrink-0"><svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg></div>
                    <div class="ml-3"><h3 class="text-sm font-medium text-red-800">Attention</h3><ul class="mt-2 list-disc pl-5 space-y-1">@foreach ($errors->all() as $error)<li class="text-sm text-red-700">{{ $error }}</li>@endforeach</ul></div></div>
                </div>
            @endif

            <!-- Formulaire Papier -->
            <div class="bg-white rounded-lg shadow-2xl overflow-hidden relative" style="border: 1px solid var(--c-border);">
                
                <div class="px-8 py-6 flex justify-between items-center" style="background-color: {{ $darkMode ? '#1e1e1e' : '#000000' }}; color: white; border-bottom: 4px solid var(--c-gold);">
                    <div><h2 class="text-2xl font-bold tracking-wider uppercase" style="font-family: 'Times New Roman', serif;">Mémorandum</h2><p class="text-xs font-bold tracking-widest mt-1" style="color: var(--c-gold);">INTERNE / CONFIDENTIEL</p></div>
                    <div class="text-right opacity-80"><p class="text-sm">Date : {{ now()->format('d/m/Y') }}</p></div>
                </div>

                <div class="p-8 md:p-12 space-y-10">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1" style="color: #707173;">Pour (Concerne)</label>
                            <input type="text" wire:model="concern" class="block w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-black placeholder-gray-300 sm:text-lg transition-colors focus:ring-0 focus:border-[#daaf2c]" placeholder="Ex: Direction Générale...">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1" style="color: #707173;">Objet</label>
                            <input type="text" wire:model="object" class="block w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-black font-bold placeholder-gray-300 sm:text-lg transition-colors focus:ring-0 focus:border-[#daaf2c]" placeholder="Sujet principal...">
                        </div>
                    </div>

                    <!-- RECHERCHE DESTINATAIRES INTELLIGENTE -->
                    <div class="flex flex-col md:flex-row gap-4 mb-4 items-end">
                        <!-- Champ Recherche -->
                        <div class="flex-1 w-full relative">
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1" style="color: var(--c-grey);">Destinataire <span class="text-red-500">*</span></label>
                            <div x-data="{ open: false }" class="relative">
                                <input type="text" class="block w-full rounded-md shadow-sm p-2.5 border sm:text-sm transition-all focus:ring-[#daaf2c] focus:border-[#daaf2c]"
                                    style="background-color: var(--c-bg-card); border-color: {{ $errors->has('newRecipientEntity') ? 'red' : 'var(--c-border)' }}; color: var(--c-black);"
                                    placeholder="Tapez pour rechercher (ex: DRH)..."
                                    wire:model.live.debounce.300ms="searchRecipient"
                                    @focus="open = true" @click.away="open = false" @input="open = true">
                                
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    @if($newRecipientEntity)
                                        <svg class="w-5 h-5 text-green-500 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @else
                                        <svg class="w-4 h-4" style="color: var(--c-grey);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    @endif
                                </div>

                                <div 
                                    x-show="open && $wire.searchRecipient.length > 0"  
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-100 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute z-50 mt-1 w-full rounded-md shadow-lg border max-h-60 overflow-auto custom-scrollbar" style="background-color: var(--c-bg-card); border-color: var(--c-border);">
                                    <ul class="py-1 text-sm">
                                        @forelse($this->filteredEntities as $entite)
                                            <li wire:key="entity-{{ $entite->id }}"
                                                wire:click="selectRecipientEntity({{ $entite->id }}, '{{ addslashes($entite->name) }}')" 
                                                @click="open = false"
                                                class="cursor-pointer px-4 py-2 hover:bg-[#daaf2c] hover:text-black transition-colors border-b last:border-0"
                                                style="border-color: {{ $darkMode ? '#2d2d2d' : '#f3f4f6' }}; color: var(--c-black);">
                                                <div class="flex flex-col">
                                                    <span class="font-bold">{{ $entite->name }}</span>
                                                    @if($entite->ref) <span class="text-xs font-medium mt-0.5" style="color: var(--c-grey);">{{ $entite->ref }}</span> @endif
                                                </div>
                                            </li>
                                        @empty
                                            <li class="px-4 py-3 text-xs italic text-center" style="color: var(--c-grey);">Aucun résultat.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                            @error('newRecipientEntity') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                        </div>

                        <!-- Champ Action -->
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold uppercase tracking-wide mb-1" style="color: var(--c-grey);">Action Requise <span class="text-red-500">*</span></label>
                            <select wire:model="newRecipientAction" class="block w-full rounded-md shadow-sm p-2.5 border sm:text-sm focus:ring-[#daaf2c] focus:border-[#daaf2c]" style="background-color: var(--c-bg-card); border-color: var(--c-border); color: var(--c-black);">
                                <option value="">-- Sélectionner --</option>
                                @foreach($actionsList as $action) <option value="{{ $action }}">{{ $action }}</option> @endforeach
                            </select>
                            @error('newRecipientAction') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Bouton Ajouter -->
                        <div class="w-full md:w-auto">
                            <button wire:click="addRecipient" type="button" class="w-full inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-md shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#daaf2c] hover:opacity-90" style="background-color: var(--c-black); color: white;">
                                <svg wire:loading.remove wire:target="addRecipient" class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <svg wire:loading wire:target="addRecipient" class="animate-spin h-4 w-4 mr-2 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Ajouter
                            </button>
                        </div>
                    </div>

                    <!-- Tableau Destinataires ajoutés -->
                    @if(count($recipients) > 0)
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg mb-6">
                            <table class="min-w-full divide-y divide-gray-300 bg-white">
                                <thead class="bg-gray-50">
                                    <tr><th class="py-2 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase">Entité</th><th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Action</th><th class="relative py-2 pl-3 pr-4 sm:pr-6"></th></tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($recipients as $index => $recipient)
                                        <tr>
                                            <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm font-medium text-gray-900">{{ $recipient['entity_name'] }}</td>
                                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-500"><span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-black ring-1 ring-inset ring-[#daaf2c]/50">{{ $recipient['action'] }}</span></td>
                                            <td class="relative whitespace-nowrap py-3 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <button wire:click="removeRecipient({{ $index }})" class="text-gray-400 hover:text-red-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Quill Editor -->
                    <div class="pt-2">
                        <label class="block text-xs font-bold uppercase tracking-wide mb-3 flex items-center text-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Corps du Document
                        </label>

                        <div wire:ignore 
                            class="flex flex-col items-center rounded-xl p-4 border shadow-inner transition-colors" 
                            style="background-color: {{ $darkMode ? '#2d2d2d' : '#f3f4f6' }}; border-color: var(--c-border);"
                            
                            x-data="{ 
                                content: @entangle('content'), 
                                quill: null, 
                                initQuill() { 
                                    // 1. FORCER L'UTILISATION DES STYLES INLINE (Vital pour PDF/Impression)
                                    // Cela transforme <span class='ql-font-arial'> en <span style='font-family: Arial'>
                                    var Font = Quill.import('attributors/style/font');
                                    var Size = Quill.import('attributors/style/size');
                                    var Align = Quill.import('attributors/style/align');
                                    
                                    // 2. DÉFINIR LES WHITELISTS (Doit correspondre exactement aux options du select HTML)
                                    Font.whitelist = ['Tahoma', 'Arial', 'Times New Roman', 'Verdana'];
                                    Size.whitelist = ['12px', '14px', '16px', '18px', '20px'];
                                    
                                    // 3. ENREGISTRER LES FORMATS
                                    Quill.register(Font, true);
                                    Quill.register(Size, true);
                                    Quill.register(Align, true);

                                    // 4. INITIALISER L'ÉDITEUR
                                    this.quill = new Quill(this.$refs.quillEditor, { 
                                        theme: 'snow', 
                                        placeholder: 'Saisissez le contenu du mémorandum...', 
                                        modules: { 
                                            toolbar: '#toolbar-container' 
                                        } 
                                    });

                                    // 5. CHARGER LE CONTENU
                                    if (this.content) { 
                                        this.quill.root.innerHTML = this.content; 
                                    } 

                                    // 6. SYNCHRONISER AVEC LIVEWIRE
                                    this.quill.on('text-change', () => { 
                                        this.content = this.quill.root.innerHTML; 
                                    }); 
                                } 
                            }" 
                            x-init="initQuill()">
                            
                            <!-- BARRE D'OUTILS PERSONNALISÉE -->
                            <div id="toolbar-container" class="w-full max-w-4xl mb-6 bg-white rounded-t-lg shadow-sm flex flex-wrap items-center justify-center gap-2 border border-gray-200 p-2 z-20 sticky top-0">
                                
                                <!-- TITRES (NIVEAUX) -->
                                <span class="ql-formats">
                                    <select class="ql-header" style="width: 130px;">
                                        <option selected>Normal</option>
                                        <option value="1">Titre 1</option>
                                        <option value="2">Titre 2</option>
                                        <option value="3">Titre 3</option>
                                        <option value="4">Titre 4</option>
                                        <option value="5">Titre 5</option>
                                        <option value="6">Titre 6</option>
                                    </select>
                                </span>

                                <!-- Polices -->
                                <span class="ql-formats">
                                    <select class="ql-font" style="width: 150px;">
                                        <option value="Tahoma" selected>Tahoma (Défaut)</option>
                                        <option value="Arial">Arial</option>
                                        <option value="Times New Roman">Times New Roman</option>
                                        <option value="Verdana">Verdana</option>
                                    </select>
                                </span>

                                <!-- Tailles -->
                                <span class="ql-formats">
                                    <select class="ql-size">
                                        <option value="12px">12 px</option>
                                        <option value="14px" selected>14 px</option>
                                        <option value="16px">16 px</option>
                                        <option value="18px">18 px</option>
                                        <option value="20px">20 px</option>
                                    </select>
                                </span>

                                <span class="border-l border-gray-300 mx-1 h-6"></span>

                                <!-- Styles -->
                                <span class="ql-formats">
                                    <button class="ql-bold" title="Gras"></button>
                                    <button class="ql-italic" title="Italique"></button>
                                    <button class="ql-underline" title="Souligné"></button>
                                    <select class="ql-color" title="Couleur du texte"></select>
                                    <select class="ql-background" title="Surlignage"></select>
                                </span>

                                <span class="border-l border-gray-300 mx-1 h-6"></span>

                                <!-- Alignement -->
                                <span class="ql-formats">
                                    <button class="ql-align" value="" title="Gauche"></button>
                                    <button class="ql-align" value="center" title="Centré"></button>
                                    <button class="ql-align" value="right" title="Droite"></button>
                                    <button class="ql-align" value="justify" title="Justifié"></button>
                                </span>

                                <span class="border-l border-gray-300 mx-1 h-6"></span>

                                <!-- Listes -->
                                <span class="ql-formats">
                                    <button class="ql-list" value="ordered" title="Liste numérotée"></button>
                                    <button class="ql-list" value="bullet" title="Liste à puces"></button>
                                </span>
                            </div>

                            <!-- ZONE D'ÉDITION (FEUILLE A4) -->
                            <div class="relative w-full max-w-[21cm] bg-white shadow-2xl border border-gray-300">
                                <!-- Calque visuel des sauts de page A4 (optionnel pour repère visuel) -->
                                <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden opacity-50">
                                    <!-- Ligne pointillée tous les ~1123px (hauteur A4 en px à 96dpi) -->
                                    <div class="w-full border-b border-dashed border-gray-300 absolute top-[1123px]"></div>
                                    <div class="w-full border-b border-dashed border-gray-300 absolute top-[2246px]"></div>
                                </div>

                                <!-- Éditeur réel -->
                                <div x-ref="quillEditor" class="text-gray-900 min-h-[29.7cm] ql-custom-editor"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div class="mt-8 border-t border-gray-100 pt-6">
                        <label class="block text-xs font-bold uppercase tracking-wide mb-3 flex items-center gap-2" style="color: #707173;"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>Pièces Jointes (P.J.)</label>
                        <div class="space-y-4">
                            <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-yellow-50/20 hover:border-[#daaf2c] transition-colors relative">
                                <div class="space-y-1 text-center">
                                    <div class="flex text-sm text-gray-600 justify-center"><label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-bold text-[#daaf2c] focus-within:ring-2 focus-within:ring-[#daaf2c]"><span>Téléverser des fichiers</span><input id="file-upload" wire:model="attachments" type="file" class="sr-only" multiple></label></div>
                                    <p class="text-xs text-gray-500">PDF, DOCX, PNG, JPG jusqu'à 10MB</p>
                                </div>
                            </div>
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
                                            <button type="button" wire:click="removeAttachment({{ $index }})" class="ml-2 inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-gray-300 hover:bg-red-500">
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
                    <span>Auteur: <strong class="text-gray-900">{{ Auth::user()->name }}</strong></span><span>Document généré par le système</span>
                </div>
            </div>
        </div>
    @endif
</div>
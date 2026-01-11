<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 transition-colors duration-300">

    <!-- CONFIGURATION CSS DYNAMIQUE -->
    <style>
        :root {
            --profile-bg-card: {{ $darkMode ? '#1e1e1e' : '#ffffff' }};
            --profile-border: {{ $darkMode ? '#2d2d2d' : '#e5e7eb' }};
            --profile-text-main: {{ $darkMode ? '#ffffff' : '#111827' }};
            --profile-text-muted: {{ $darkMode ? '#a0a0a0' : '#6b7280' }};
            --profile-input-bg: {{ $darkMode ? '#2d2d2d' : '#ffffff' }};
            --profile-input-readonly: {{ $darkMode ? '#1a1a1a' : '#f3f4f6' }};
        }
    </style>

    <!-- SECTION : EN-TÊTE -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold" style="color: var(--profile-text-main);">Mon Profil</h1>
        <p class="mt-1 text-sm" style="color: var(--profile-text-muted);">Gérez vos informations personnelles et professionnelles.</p>
    </div>

    <!-- GRILLE PRINCIPALE -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        <!-- COLONNE GAUCHE : RÉSUMÉ -->
        <div class="md:col-span-1">
            <div class="shadow rounded-xl p-6 text-center border transition-all" style="background-color: var(--profile-bg-card); border-color: var(--profile-border);">
                <!-- Avatar -->
                <div class="h-24 w-24 rounded-full bg-[#daaf2c] flex items-center justify-center text-black text-3xl font-bold mx-auto mb-4 border-4 {{ $darkMode ? 'border-yellow-900/30' : 'border-yellow-100' }}">
                    {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                </div>

                <h2 class="text-xl font-bold" style="color: var(--profile-text-main);">{{ $user->first_name }} {{ $user->last_name }}</h2>
                <p style="color: var(--profile-text-muted);" class="text-sm">{{ '@' . $user->user_name }}</p>

                <div class="mt-4 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $darkMode ? 'bg-green-900/30 text-green-400' : 'bg-green-100 text-green-800' }}">
                    @if($user->is_active) Compte Actif @endif
                </div>

                <hr class="my-6" style="border-color: var(--profile-border);">

                <div class="text-left space-y-3">
                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider" style="color: var(--profile-text-muted);">Connecté depuis</p>
                        <p class="text-sm font-medium" style="color: var(--profile-text-main);">
                            <i class="fas fa-clock mr-2 text-[#daaf2c]"></i>
                            {{ $user->updated_at ? $user->updated_at->diffForHumans() : 'Jamais' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLONNE DROITE : FORMULAIRE -->
        <div class="md:col-span-2 space-y-6">
            <form wire:submit.prevent="save">

                <!-- SECTION 1 : IDENTITÉ -->
                <div class="shadow rounded-xl p-6 mb-6 border transition-all" style="background-color: var(--profile-bg-card); border-color: var(--profile-border);">
                    <h3 class="text-lg font-medium mb-4 pb-2 border-b" style="color: var(--profile-text-main); border-color: var(--profile-border);">Identité & Contact</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium" style="color: var(--profile-text-muted);">Prénom</label>
                            <input type="text" value="{{ $user->first_name }}" readonly class="mt-1 block w-full rounded-md border shadow-sm sm:text-sm p-2 cursor-not-allowed" style="background-color: var(--profile-input-readonly); border-color: var(--profile-border); color: var(--profile-text-muted);">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium" style="color: var(--profile-text-muted);">Nom</label>
                            <input type="text" value="{{ $user->last_name }}" readonly class="mt-1 block w-full rounded-md border shadow-sm sm:text-sm p-2 cursor-not-allowed" style="background-color: var(--profile-input-readonly); border-color: var(--profile-border); color: var(--profile-text-muted);">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium" style="color: var(--profile-text-muted);">Nom D'utilisateur</label>
                            <input type="text" value="{{ $user->user_name }}" readonly class="mt-1 block w-full rounded-md border shadow-sm sm:text-sm p-2 cursor-not-allowed" style="background-color: var(--profile-input-readonly); border-color: var(--profile-border); color: var(--profile-text-muted);">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium" style="color: var(--profile-text-muted);">Email</label>
                            <input type="text" value="{{ $user->email }}" readonly class="mt-1 block w-full rounded-md border shadow-sm sm:text-sm p-2 cursor-not-allowed" style="background-color: var(--profile-input-readonly); border-color: var(--profile-border); color: var(--profile-text-muted);">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2 : INFOS PROFESSIONNELLES -->
                <div class="shadow rounded-xl p-6 mb-6 border transition-all" style="background-color: var(--profile-bg-card); border-color: var(--profile-border);">
                    <h3 class="text-lg font-medium mb-4 pb-2 border-b" style="color: var(--profile-text-main); border-color: var(--profile-border);">Informations Professionnelles</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label for="poste" class="block text-sm font-medium" style="color: var(--profile-text-muted);">Poste / Fonction <span class="text-[#daaf2c]">*</span></label>
                            <select id="poste" wire:model="poste" {{ $isLocked ? 'disabled' : '' }}  class="mt-1 block w-full rounded-md shadow-sm sm:text-sm p-2 border focus:ring-[#daaf2c]" style="background-color: var(--profile-input-bg); border-color: var(--profile-border); color: var(--profile-text-main);">
                                <option value="">-- Sélectionner --</option>
                                @foreach(App\Enums\Poste::cases() as $posteCase)
                                    <option value="{{ $posteCase->value }}">{{ $posteCase->label() }}</option>
                                @endforeach
                            </select>
                            @error('poste') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium" style="color: var(--profile-text-muted);">
                                Direction <span class="text-[#daaf2c]">*</span>
                            </label>

                            <!-- Conteneur Alpine.js pour gérer l'ouverture/fermeture -->
                            <div x-data="{ open: false }" class="relative mt-1">
                                
                                <!-- Champ de saisie (Recherche) -->
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        class="block w-full rounded-md shadow-sm p-2 border sm:text-sm transition-all focus:ring-[#daaf2c] focus:border-[#daaf2c]"
                                        style="background-color: var(--profile-input-bg); border-color: var(--profile-border); color: var(--profile-text-main);"
                                        placeholder="Rechercher une direction..."
                                        wire:model.live.debounce.300ms="searchDirection"
                                        @focus="open = true"
                                        @click.away="open = false"
                                        @input="open = true"
                                        {{ $isLocked ? 'disabled' : '' }}
                                    >
                                    
                                    <!-- Icône flèche ou loupe -->
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        @if($dir_id)
                                            <!-- Petite croix pour vider si sélectionné (optionnel) -->
                                            <i class="fas fa-check text-green-500 text-xs"></i>
                                        @else
                                            <i class="fas fa-search text-xs" style="color: var(--profile-text-muted);"></i>
                                        @endif
                                    </div>
                                </div>

                                <!-- Liste déroulante des résultats -->
                                <div 
                                    x-show="open" 
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-100 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute z-50 mt-1 w-full rounded-md shadow-lg border max-h-60 overflow-auto"
                                    style="background-color: var(--profile-bg-card); border-color: var(--profile-border);"
                                    style="display: none;" 
                                >
                                    <ul class="py-1 text-sm">
                                        <!-- Option vide / Reset -->
                                        <li 
                                            wire:click="selectDirection(null, '')"
                                            @click="open = false"
                                            class="cursor-pointer px-4 py-2 hover:bg-[#daaf2c] hover:text-black transition-colors"
                                            style="color: var(--profile-text-main);"
                                        >
                                            -- Aucune / Réinitialiser --
                                        </li>

                                        <!-- Liste filtrée -->
                                        @forelse($this->filteredDirections as $entite)
                                            <li 
                                                wire:click="selectDirection({{ $entite->id }}, '{{ $entite->name }}')"
                                                @click="open = false"
                                                class="cursor-pointer px-4 py-2 hover:bg-[#daaf2c] hover:text-black transition-colors border-b last:border-0 border-gray-700/10"
                                                style="color: var(--profile-text-main); border-color: var(--profile-border);"
                                            >
                                                <div class="flex flex-col">
                                                    <span class="font-bold">{{ $entite->name }}</span>
                                                    @if($entite->ref)
                                                        <span class="text-[10px] uppercase opacity-70">{{ $entite->ref }}</span>
                                                    @endif
                                                </div>
                                            </li>
                                        @empty
                                            <li class="px-4 py-2 text-xs italic text-center" style="color: var(--profile-text-muted);">
                                                Aucune direction trouvée pour "{{ $searchDirection }}"
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Input caché pour s'assurer que l'ID est bien soumis même si on ne touche pas au JS -->
                            <input type="hidden" wire:model="dir_id">
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium" style="color: var(--profile-text-muted);">Sous-Direction</label>
                            
                            <div x-data="{ open: false }" class="relative mt-1">
                                <input 
                                    type="text" 
                                    class="block w-full rounded-md shadow-sm p-2 border sm:text-sm transition-all focus:ring-[#daaf2c] focus:border-[#daaf2c]"
                                    style="background-color: var(--profile-input-bg); border-color: var(--profile-border); color: var(--profile-text-main);"
                                    placeholder="{{ count($sous_directions) > 0 ? 'Rechercher...' : 'Sélectionnez une direction d\'abord' }}"
                                    wire:model.live.debounce.300ms="searchSousDirection"
                                    @focus="open = true"
                                    @click.away="open = false"
                                    @input="open = true"
                                    {{ $isLocked || count($sous_directions) == 0 ? 'disabled' : '' }}
                                >
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-xs" style="color: var(--profile-text-muted);"></i>
                                </div>

                                <div 
                                    x-show="open" 
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-100 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute z-50 mt-1 w-full rounded-md shadow-lg border max-h-60 overflow-auto"
                                    style="background-color: var(--profile-bg-card); border-color: var(--profile-border);"
                                    style="display: none;" 
                                    style="display: none;" class="absolute z-50 mt-1 w-full rounded-md shadow-lg border max-h-60 overflow-auto"
                                    style="background-color: var(--profile-bg-card); border-color: var(--profile-border);">
                                    <ul class="py-1 text-sm">
                                        <li wire:click="selectSousDirection(null, '')" @click="open = false" class="cursor-pointer px-4 py-2 hover:bg-[#daaf2c] hover:text-black transition-colors" style="color: var(--profile-text-main);">-- Aucune --</li>
                                        @forelse($this->filteredSousDirections as $sd)
                                            <li wire:click="selectSousDirection({{ $sd->id }}, '{{ $sd->name }}')" @click="open = false" class="cursor-pointer px-4 py-2 hover:bg-[#daaf2c] hover:text-black transition-colors border-b last:border-0 border-gray-700/10" style="color: var(--profile-text-main);">
                                                {{ $sd->name }}
                                            </li>
                                        @empty
                                            <li class="px-4 py-2 text-xs italic text-center" style="color: var(--profile-text-muted);">Aucun résultat</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium" style="color: var(--profile-text-muted);">Département</label>
                            
                            <div x-data="{ open: false }" class="relative mt-1">
                                <input 
                                    type="text" 
                                    class="block w-full rounded-md shadow-sm p-2 border sm:text-sm transition-all focus:ring-[#daaf2c] focus:border-[#daaf2c]"
                                    style="background-color: var(--profile-input-bg); border-color: var(--profile-border); color: var(--profile-text-main);"
                                    placeholder="{{ count($departements) > 0 ? 'Rechercher...' : 'Sélectionnez une S.D d\'abord' }}"
                                    wire:model.live.debounce.300ms="searchDepartement"
                                    @focus="open = true"
                                    @click.away="open = false"
                                    @input="open = true"
                                    {{ $isLocked || count($departements) == 0 ? 'disabled' : '' }}
                                >
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-xs" style="color: var(--profile-text-muted);"></i>
                                </div>

                                <div
                                    x-show="open" 
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-100 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute z-50 mt-1 w-full rounded-md shadow-lg border max-h-60 overflow-auto"
                                    style="background-color: var(--profile-bg-card); border-color: var(--profile-border);"
                                    style="display: none;"  
                                    style="display: none;" class="absolute z-50 mt-1 w-full rounded-md shadow-lg border max-h-60 overflow-auto"
                                    style="background-color: var(--profile-bg-card); border-color: var(--profile-border);">
                                    <ul class="py-1 text-sm">
                                        <li wire:click="selectDepartement(null, '')" @click="open = false" class="cursor-pointer px-4 py-2 hover:bg-[#daaf2c] hover:text-black transition-colors" style="color: var(--profile-text-main);">-- Aucun --</li>
                                        @forelse($this->filteredDepartements as $dept)
                                            <li wire:click="selectDepartement({{ $dept->id }}, '{{ $dept->name }}')" @click="open = false" class="cursor-pointer px-4 py-2 hover:bg-[#daaf2c] hover:text-black transition-colors border-b last:border-0 border-gray-700/10" style="color: var(--profile-text-main);">
                                                {{ $dept->name }}
                                            </li>
                                        @empty
                                            <li class="px-4 py-2 text-xs italic text-center" style="color: var(--profile-text-muted);">Aucun résultat</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>

                       <div class="sm:col-span-3">
                            <label class="block text-sm font-medium" style="color: var(--profile-text-muted);">Service</label>
                            
                            <div x-data="{ open: false }" class="relative mt-1">
                                <input 
                                    type="text" 
                                    class="block w-full rounded-md shadow-sm p-2 border sm:text-sm transition-all focus:ring-[#daaf2c] focus:border-[#daaf2c]"
                                    style="background-color: var(--profile-input-bg); border-color: var(--profile-border); color: var(--profile-text-main);"
                                    placeholder="{{ count($services) > 0 ? 'Rechercher...' : 'Sélectionnez un départ. d\'abord' }}"
                                    wire:model.live.debounce.300ms="searchService"
                                    @focus="open = true"
                                    @click.away="open = false"
                                    @input="open = true"
                                    {{ $isLocked || count($services) == 0 ? 'disabled' : '' }}
                                >
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-xs" style="color: var(--profile-text-muted);"></i>
                                </div>

                                <div 
                                    x-show="open" 
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-100 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute z-50 mt-1 w-full rounded-md shadow-lg border max-h-60 overflow-auto"
                                    style="background-color: var(--profile-bg-card); border-color: var(--profile-border);"
                                    style="display: none;" 
                                    style="display: none;" class="absolute z-50 mt-1 w-full rounded-md shadow-lg border max-h-60 overflow-auto"
                                    style="background-color: var(--profile-bg-card); border-color: var(--profile-border);">
                                    <ul class="py-1 text-sm">
                                        <li wire:click="selectService(null, '')" @click="open = false" class="cursor-pointer px-4 py-2 hover:bg-[#daaf2c] hover:text-black transition-colors" style="color: var(--profile-text-main);">-- Aucun --</li>
                                        @forelse($this->filteredServices as $serv)
                                            <li wire:click="selectService({{ $serv->id }}, '{{ $serv->name }}')" @click="open = false" class="cursor-pointer px-4 py-2 hover:bg-[#daaf2c] hover:text-black transition-colors border-b last:border-0 border-gray-700/10" style="color: var(--profile-text-main);">
                                                {{ $serv->name }}
                                            </li>
                                        @empty
                                            <li class="px-4 py-2 text-xs italic text-center" style="color: var(--profile-text-muted);">Aucun résultat</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-6">
                            <label for="manager" class="block text-sm font-medium" style="color: var(--profile-text-muted);">
                                Manager (N+1) <span class="text-[#daaf2c]">*</span>
                            </label>

                            <!-- Conteneur Alpine.js -->
                            <div x-data="{ open: false }" class="relative mt-1">
                                
                                <!-- Champ de Saisie -->
                                <input 
                                    type="text" 
                                    class="block w-full rounded-md shadow-sm p-2 border sm:text-sm transition-all focus:ring-[#daaf2c] focus:border-[#daaf2c]"
                                    style="background-color: var(--profile-input-bg); border-color: var(--profile-border); color: var(--profile-text-main);"
                                    
                                    {{-- Message dynamique selon l'état --}}
                                    placeholder="{{ !$dir_id ? 'Veuillez sélectionner votre Direction ci-dessus d\'abord' : 'Rechercher un manager (Nom/Prénom)...' }}"
                                    
                                    wire:model.live.debounce.300ms="searchManager"
                                    
                                    {{-- Désactivé si verrouillé OU si pas de direction sélectionnée --}}
                                    {{ $isLocked || !$dir_id ? 'disabled' : '' }}
                                    
                                    @focus="open = true"
                                    @click.away="open = false"
                                    @input="open = true"
                                >
                                
                                <!-- Icône -->
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                    @if(!$dir_id)
                                        <i class="fas fa-lock text-xs text-gray-400"></i>
                                    @else
                                        <i class="fas fa-user-tie text-xs" style="color: var(--profile-text-muted);"></i>
                                    @endif
                                </div>

                                <!-- Liste Déroulante -->
                                @if($dir_id)
                                    <div 
                                    x-show="open" 
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-100 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute z-50 mt-1 w-full rounded-md shadow-lg border max-h-60 overflow-auto"
                                    style="background-color: var(--profile-bg-card); border-color: var(--profile-border);"
                                    style="display: none;"  
                                        style="display: none;" 
                                        class="absolute z-50 mt-1 w-full rounded-md shadow-lg border max-h-60 overflow-auto"
                                        style="background-color: var(--profile-bg-card); border-color: var(--profile-border);"
                                    >
                                        <ul class="py-1 text-sm">
                                            <!-- Option "Aucun" -->
                                            <li 
                                                wire:click="selectManager(null, '')" 
                                                @click="open = false" 
                                                class="cursor-pointer px-4 py-2 hover:bg-[#daaf2c] hover:text-black transition-colors"
                                                style="color: var(--profile-text-main);"
                                            >
                                                -- Aucun --
                                            </li>

                                            <!-- Liste Filtrée -->
                                            @forelse($this->filteredManagers as $m)
                                                <li 
                                                    wire:click="selectManager({{ $m->id }}, '{{ $m->first_name }} {{ $m->last_name }}')"
                                                    @click="open = false"
                                                    class="cursor-pointer px-4 py-2 hover:bg-[#daaf2c] hover:text-black transition-colors border-b last:border-0 border-gray-700/10"
                                                    style="color: var(--profile-text-main);"
                                                >
                                                    <div class="flex flex-col">
                                                        <span class="font-bold">{{ $m->first_name }} {{ $m->last_name }}</span>
                                                        <span class="text-[10px] uppercase opacity-70">
                                                            {{ $m->poste ? (is_string($m->poste) ? $m->poste : $m->poste->value) : 'Sans poste' }}
                                                        </span>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="px-4 py-2 text-xs italic text-center" style="color: var(--profile-text-muted);">
                                                    @if(strlen($searchManager) > 0)
                                                        Aucun collaborateur trouvé dans cette direction pour "{{ $searchManager }}"
                                                    @else
                                                        Commencez à taper pour rechercher...
                                                    @endif
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Input caché pour la soumission -->
                            <input type="hidden" wire:model="manager_id">
                            
                            <!-- Affichage des erreurs de validation -->
                            @error('manager_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>


                    </div>
                </div>

                

                <!-- SECTION 3 : INTÉRIMS -->
                <div class="shadow rounded-xl p-6 mb-6 border transition-all" style="background-color: var(--profile-bg-card); border-color: var(--profile-border);">
                    <h3 class="text-lg font-medium mb-4 pb-2 border-b" style="color: var(--profile-text-main); border-color: var(--profile-border);">
                        <i class="fas fa-exchange-alt text-[#daaf2c] mr-2"></i> Intérims & Délégations
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Qui me remplace -->
                        <div class="rounded-lg p-4 border" style="background-color: {{ $darkMode ? 'rgba(255,255,255,0.03)' : '#f9fafb' }}; border-color: var(--profile-border);">
                            <h4 class="text-xs font-bold mb-3 uppercase tracking-widest" style="color: var(--profile-text-muted);">Qui me remplace ?</h4>
                            @forelse($user->replacements as $remplacement)
                                <div class="p-3 mb-2 rounded-md border text-sm" style="background-color: var(--profile-bg-card); border-color: var(--profile-border); color: var(--profile-text-main);">
                                    <div class="flex justify-between font-bold">
                                        {{ $remplacement->substitute->first_name ?? 'Inconnu' }}
                                        <span class="text-[10px] px-2 rounded-full {{ \Carbon\Carbon::now()->between($remplacement->date_begin_replace, $remplacement->date_end_replace) ? 'bg-green-500/20 text-green-500' : 'bg-gray-500/20 text-gray-500' }}">
                                            Statut
                                        </span>
                                    </div>
                                    <div class="text-[10px] mt-1" style="color: var(--profile-text-muted);">Du {{ \Carbon\Carbon::parse($remplacement->date_begin_replace)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($remplacement->date_end_replace)->format('d/m/Y') }}</div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-xs italic" style="color: var(--profile-text-muted);">Aucun remplaçant.</div>
                            @endforelse
                        </div>

                        <!-- Qui je remplace -->
                        <div class="rounded-lg p-4 border" style="background-color: {{ $darkMode ? 'rgba(255,255,255,0.03)' : '#f9fafb' }}; border-color: var(--profile-border);">
                            <h4 class="text-xs font-bold mb-3 uppercase tracking-widest" style="color: var(--profile-text-muted);">Qui je remplace ?</h4>
                            @forelse($user->replacing as $mission)
                                <div class="p-3 mb-2 rounded-md border text-sm" style="background-color: var(--profile-bg-card); border-color: var(--profile-border); color: var(--profile-text-main);">
                                    <div class="flex justify-between font-bold">
                                        {{ $mission->user->first_name ?? 'Inconnu' }}
                                        <span class="text-[10px] px-2 rounded-full {{ \Carbon\Carbon::now()->between($mission->date_begin_replace, $mission->date_end_replace) ? 'bg-yellow-500/20 text-yellow-500' : 'bg-gray-500/20 text-gray-500' }}">
                                            Mission
                                        </span>
                                    </div>
                                    <div class="text-[10px] mt-1" style="color: var(--profile-text-muted);">Du {{ \Carbon\Carbon::parse($mission->date_begin_replace)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($mission->date_end_replace)->format('d/m/Y') }}</div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-xs italic" style="color: var(--profile-text-muted);">Aucune mission.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- SECTION ACTIONS -->
                <div class="flex justify-end gap-4">
                    @if($isLocked)
                        <div class="flex items-center text-blue-500 text-sm italic font-medium">
                            <i class="fas fa-info-circle mr-2"></i> Informations verrouillées. Contactez l'administrateur pour toute modification.
                        </div>
                    @else
                        <div wire:loading wire:target="save" class="flex items-center text-[#daaf2c] text-sm font-bold">
                            <svg class="animate-spin h-5 w-5 mr-2" ...>...</svg>
                            Traitement...
                        </div>
                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-[#daaf2c] px-6 py-2 text-sm font-bold text-black shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[#daaf2c] transition-all">
                            Enregistrer et figer mon profil
                        </button>
                    @endif
                </div>
                
            </form>
        </div>
    </div>
</div>
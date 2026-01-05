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
                            <label class="block text-sm font-medium" style="color: var(--profile-text-muted);">Direction</label>
                            <select wire:model.live="dir_id" {{ $isLocked ? 'disabled' : '' }} class="mt-1 block w-full rounded-md shadow-sm p-2 border" style="background-color: var(--profile-input-bg); border-color: var(--profile-border); color: var(--profile-text-main);">
                                <option value="">-- Sélectionner --</option>
                                @foreach($entites as $entite)
                                    <option value="{{ $entite->id }}">{{ $entite->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium" style="color: var(--profile-text-muted);">Sous-Direction</label>
                            <select wire:model.live="sd_id" {{ $isLocked ? 'disabled' : '' }} class="mt-1 block w-full rounded-md shadow-sm p-2 border" style="background-color: var(--profile-input-bg); border-color: var(--profile-border); color: var(--profile-text-main);">
                                <option value="">-- Sélectionner --</option>
                                @foreach($sous_directions as $sd)
                                    <option value="{{ $sd->id }}">{{ $sd->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium" style="color: var(--profile-text-muted);">Département</label>
                            <select wire:model.live="dep_id" {{ $isLocked ? 'disabled' : '' }} class="mt-1 block w-full rounded-md shadow-sm p-2 border" style="background-color: var(--profile-input-bg); border-color: var(--profile-border); color: var(--profile-text-main);">
                                <option value="">-- Sélectionner --</option>
                                @foreach($departements as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium" style="color: var(--profile-text-muted);">Service</label>
                            <select wire:model="serv_id" {{ $isLocked ? 'disabled' : '' }} class="mt-1 block w-full rounded-md shadow-sm p-2 border" style="background-color: var(--profile-input-bg); border-color: var(--profile-border); color: var(--profile-text-main);">
                                <option value="">-- Sélectionner --</option>
                                @foreach($services as $serv)
                                    <option value="{{ $serv->id }}">{{ $serv->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-6">
                            <label for="manager" class="block text-sm font-medium" style="color: var(--profile-text-muted);">
                                Manager (N+1)
                            </label>

                            <!-- Conteneur de recherche avec Alpine.js -->
                            <div x-data="{ open: false, selectedName: '{{ $user->manager ? $user->manager->first_name . ' ' . $user->manager->last_name : '-- Sélectionner --' }}' }" class="relative mt-1">
                                
                                <!-- Champ de saisie (Recherche) -->
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        class="block w-full rounded-md shadow-sm p-2 border sm:text-sm transition-all"
                                        style="background-color: var(--profile-input-bg); border-color: var(--profile-border); color: var(--profile-text-main);"
                                        placeholder="Rechercher un manager..."
                                        wire:model.live.debounce.300ms="searchManager"
                                        @focus="open = true"
                                        @click.away="open = false"
                                        {{ $isLocked ? 'disabled' : '' }}
                                        x-model="selectedName"
                                        @input="open = true"
                                    >
                                    
                                    <!-- Icône flèche -->
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <i class="fas fa-chevron-down text-xs" style="color: var(--profile-text-muted);"></i>
                                    </div>
                                </div>

                                <!-- Liste déroulante -->
                                <div 
                                    x-show="open" 
                                    x-transition
                                    class="absolute z-10 mt-1 w-full rounded-md shadow-lg border max-h-60 overflow-auto"
                                    style="background-color: var(--profile-bg-card); border-color: var(--profile-border);"
                                >
                                    <ul class="py-1 text-sm">
                                        <li 
                                            @click="open = false; $wire.set('manager_id', null); selectedName = '-- Sélectionner --'"
                                            class="cursor-pointer px-4 py-2 hover:bg-[#daaf2c] hover:text-black"
                                            style="color: var(--profile-text-main);"
                                        >
                                            -- Aucun --
                                        </li>

                                        @forelse($this->filteredManagers as $m)
                                            <li 
                                                @click="
                                                    open = false; 
                                                    $wire.set('manager_id', {{ $m->id }}); 
                                                    selectedName = '{{ $m->first_name }} {{ $m->last_name }}';
                                                "
                                                class="cursor-pointer px-4 py-2 hover:bg-[#daaf2c] hover:text-black transition-colors"
                                                style="color: var(--profile-text-main);"
                                            >
                                                <div class="flex flex-col">
                                                    <span class="font-bold">{{ $m->first_name }} {{ $m->last_name }}</span>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="px-4 py-2 text-xs italic" style="color: var(--profile-text-muted);">
                                                Aucun résultat pour "{{ $searchManager }}"
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Input caché pour stocker la valeur réelle pour le formulaire -->
                            <input type="hidden" wire:model="manager_id">
                            
                            @error('manager_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- En-tête -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Mon Profil</h1>
        <p class="mt-1 text-sm text-gray-600">Gérez vos informations personnelles et professionnelles.</p>
    </div>

    <!-- Message de succès -->
    @if (session()->has('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- COLONNE GAUCHE : Carte résumé -->
        <div class="md:col-span-1">
            <div class="bg-white shadow rounded-lg p-6 text-center">
                <!-- Avatar : Initiales dynamiques -->
                <div class="h-24 w-24 rounded-full bg-yellow-600 flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4 border-4 border-yellow-100">
                    {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                </div>
                
                <h2 class="text-xl font-bold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</h2>
                <p class="text-gray-500 text-sm">{{ '@' . $user->user_name }}</p>
                
                <div class="mt-4 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    
                    @if($user->is_active)
                        Compte Actif
                    @endif 

                </div>

                <hr class="my-6 border-gray-200">

                <div class="text-left space-y-3">
                    <div>
                        <p class="text-xs text-gray-400 uppercase">Connecté depuis</p>
                        <p class="text-sm font-medium text-gray-700">
                            <!-- Changement de l'icône pour une horloge -->
                            <i class="fas fa-clock mr-2 text-[#daaf2c]"></i> 
                            
                            <!-- Affiche "il y a 2 heures", "il y a 1 jour", etc. -->
                            {{ $user->updated_at ? $user->updated_at->diffForHumans() : 'Jamais' }}
                        </p>
                    </div> 
                </div>
            </div>
        </div>

        <!-- COLONNE DROITE : Formulaire d'édition -->
        <div class="md:col-span-2 space-y-6">
            <!-- wire:submit.prevent="save" permet d'envoyer le formulaire en AJAX sans recharger la page -->
            <form wire:submit.prevent="save">
                
                <!-- SECTION 1 : Identité & Contact (Lecture Seule) -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 pb-2 border-b">Identité & Contact</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        
                        <!-- PRÉNOM -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Prénom <span class="text-xs text-gray-400 font-normal ml-1">(Verrouillé)</span>
                            </label>
                            <input type="text" value="{{ $user->first_name }}" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm border p-2 bg-gray-100 text-gray-500 cursor-not-allowed focus:ring-0">
                        </div>

                        <!-- NOM -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Nom <span class="text-xs text-gray-400 font-normal ml-1">(Verrouillé)</span>
                            </label>
                            <input type="text" value="{{ $user->last_name }}" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm border p-2 bg-gray-100 text-gray-500 cursor-not-allowed focus:ring-0">
                        </div>

                        <!-- USERNAME -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Nom D'utilisateur
                            </label>
                            <input type="text" value="{{ $user->user_name }}" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm border p-2 bg-gray-100 text-gray-500 cursor-not-allowed focus:ring-0">
                        </div>

                        <!-- EMAIL -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Email 
                            </label>
                            <input type="text" value="{{ $user->email }}" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm border p-2 bg-gray-100 text-gray-500 cursor-not-allowed focus:ring-0">
                        </div>

                    </div>
                </div>

                <!-- SECTION 2 : Infos Professionnelles (Modifiables) -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 pb-2 border-b">Informations Professionnelles</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        
                        <!-- POSTE (SELECT) -->
                        <div class="sm:col-span-6">
                            <label for="poste" class="block text-sm font-medium text-gray-700">
                                Poste / Fonction <span class="text-red-600 font-bold ml-0.5" title="Champ obligatoire">*</span>
                            </label>
                            
                            <!-- wire:model relie ce champ à la variable $poste du composant -->
                            <select id="poste" wire:model="poste" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-yellow-500 sm:text-sm border p-2">
                                <option value="">-- Sélectionner --</option>
                                <option value="Stagiaire Professionnel">Stagiaire Professionnel</option>
                                <option value="Employer">Employer</option>
                                <option value="Chef-Service">Chef-Service</option>
                                <option value="Chef-Departement">Chef-Departement</option>
                                <option value="Secretaire">Secretaire/Assistante</option>
                                <option value="Sous-Directeur">Sous-Directeur</option>
                                <option value="Directeur">Directeur</option>
                                <!-- Option de fallback si le poste actuel n'est pas dans la liste -->
                                @if(!in_array($poste, ['Stagiaire Professionnel', 'Employer', 'Chef-Service', 'Chef-Departement', 'Secretaire', 'Sous-Directeur', 'Directeur']) && $poste)
                                    <option value="{{ $poste }}">{{ $poste }} (Actuel)</option>
                                @endif
                            </select>
                            @error('poste') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- ENTITE -->
                        <div class="sm:col-span-3">
                            <label for="entity" class="block text-sm font-medium text-gray-700">
                                Entité <span class="text-red-600 font-bold ml-0.5" title="Champ obligatoire">*</span>
                            </label>

                            <select id="entity" wire:model="entity_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-yellow-500 sm:text-sm border p-2">
                                
                                @if($user_entity)
                                    <option value="{{ $user_entity->id }}">{{ $user_entity->name }}</option>
                                @else
                                    <option value="">-- Sélectionner --</option>
                                @endif

                                @foreach($entites as $entite)
                                    <option value="{{ $entite->id }}">{{ $entite->name }}</option>
                                @endforeach
                               
                            </select>

                            @error('entity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Sous Direction -->
                        <div class="sm:col-span-3">
                            <label for="sous_direction" class="block text-sm font-medium text-gray-700">
                                Sous Direction 
                            </label>

                            <select id="sous_direction" wire:model="sous_direction_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-yellow-500 sm:text-sm border p-2">
                                
                                @if($user_sd)
                                    <option value="{{ $user_sd->id }}">{{ $user_sd->name }}</option>
                                @else
                                    <option value="">-- Sélectionner --</option>
                                @endif

                                @foreach($sd as $value)
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                               
                            </select>

                            @error('entity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- SERVICE -->
                        <div class="sm:col-span-3">
                            <label for="departement" class="block text-sm font-medium text-gray-700">
                                Département 
                            </label>
                            <input type="text" id="departement" wire:model="departement" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-yellow-500 sm:text-sm border p-2">
                            @error('departement') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- SERVICE -->
                        <div class="sm:col-span-3">
                            <label for="service" class="block text-sm font-medium text-gray-700">
                                Service 
                            </label>
                            <input type="text" id="service" wire:model="service" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-yellow-500 sm:text-sm border p-2">
                            @error('service') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- N+1 -->
                        <div class="sm:col-span-3">
                            <label for="manager" class="block text-sm font-medium text-gray-700">
                                Manager (N+1) 
                            </label>

                            <select id="manager" wire:model="manager_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-yellow-500 sm:text-sm border p-2">
                                <!-- Valeur vide pour forcer le choix (déclenche 'required' si laissé tel quel) -->
                                <option>-- Sélectionner --</option> 
                            

                                @foreach($user_all as $value)
                                    <option value="{{ $value->id }}">{{ $value->first_name }} {{ $value->last_name }}</option>
                                @endforeach
                            </select>

                            @error('manager_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                    </div>
                </div>

                <!-- SECTION 3 : Intérims & Délégations (Lecture Seule) -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 pb-2 border-b">
                        <i class="fas fa-exchange-alt text-yellow-500 mr-2"></i> Intérims & Délégations
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- COLONNE 1 : Qui me remplace ? -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wide">
                                Qui me remplace ?
                            </h4>

                            @if($user->replacements->count() > 0)
                                <ul class="divide-y divide-gray-200 bg-white rounded-md border border-gray-200">
                                    @foreach($user->replacements as $remplacement)
                                        <li class="p-3">
                                            <div class="flex items-center justify-between">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $remplacement->substitute->first_name ?? 'Inconnu' }} {{ $remplacement->substitute->last_name ?? '' }}
                                                </div>
                                                <!-- Badge Statut (Actif ou non selon la date) -->
                                                @php
                                                    $now = \Carbon\Carbon::now();
                                                    $isActive = $now->between($remplacement->date_begin_replace, $remplacement->date_end_replace);
                                                @endphp
                                                @if($isActive)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        En cours
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Programmé/Passé
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <div class="mt-2 text-xs text-gray-500">
                                                <i class="far fa-calendar-alt mr-1"></i>
                                                Du {{ \Carbon\Carbon::parse($remplacement->date_begin_replace)->format('d/m/Y') }}
                                                au {{ \Carbon\Carbon::parse($remplacement->date_end_replace)->format('d/m/Y') }}
                                            </div>

                                            <div class="mt-2">
                                                <span class="text-xs text-gray-400">Droits :</span>
                                                @if(is_array($remplacement->action_replace))
                                                    @foreach($remplacement->action_replace as $action)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $action }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="text-xs">{{ $remplacement->action_replace }}</span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-center py-4 text-sm text-gray-500 italic">
                                    Aucun remplaçant prévu actuellement.
                                </div>
                            @endif
                        </div>

                        <!-- COLONNE 2 : Qui je remplace ? -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wide">
                                Qui je remplace ?
                            </h4>

                            @if($user->replacing->count() > 0)
                                <ul class="divide-y divide-gray-200 bg-white rounded-md border border-gray-200">
                                    @foreach($user->replacing as $mission)
                                        <li class="p-3">
                                            <div class="flex items-center justify-between">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $mission->user->first_name ?? 'Inconnu' }} {{ $mission->user->last_name ?? '' }}
                                                </div>
                                                <!-- Badge Statut -->
                                                @php
                                                    $now = \Carbon\Carbon::now();
                                                    $isMissionActive = $now->between($mission->date_begin_replace, $mission->date_end_replace);
                                                @endphp
                                                @if($isMissionActive)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 animate-pulse">
                                                        Actif
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Inactif
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="mt-2 text-xs text-gray-500">
                                                <i class="far fa-calendar-alt mr-1"></i>
                                                Du {{ \Carbon\Carbon::parse($mission->date_begin_replace)->format('d/m/Y') }}
                                                au {{ \Carbon\Carbon::parse($mission->date_end_replace)->format('d/m/Y') }}
                                            </div>

                                            <div class="mt-2">
                                                <span class="text-xs text-gray-400">Mes droits :</span>
                                                @if(is_array($mission->action_replace))
                                                    @foreach($mission->action_replace as $action)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            {{ $action }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="text-xs">{{ $mission->action_replace }}</span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-center py-4 text-sm text-gray-500 italic">
                                    Aucune mission d'intérim en cours.
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex justify-end gap-4">
                    <!-- Indicateur de chargement -->
                    <div wire:loading wire:target="save" class="flex items-center text-yellow-600 mr-2">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sauvegarde...
                    </div>

                    <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-yellow-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                        Enregistrer les modifications
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
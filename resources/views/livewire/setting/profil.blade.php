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
                    Compte Actif
                </div>

                <hr class="my-6 border-gray-200">

                <div class="text-left space-y-3">
                    <div>
                        <p class="text-xs text-gray-400 uppercase">Membre depuis</p>
                        <!-- Formatage de la date avec Carbon -->
                        <p class="text-sm font-medium text-gray-700">
                            <i class="far fa-calendar-alt mr-2"></i> {{ $user->created_at ? $user->created_at->format('d M Y') : 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase">Domaine</p>
                        <p class="text-sm font-medium text-gray-700">
                            <i class="fas fa-globe mr-2"></i> {{ $user->domain ?? 'Non défini' }}
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
                            <label for="poste" class="block text-sm font-medium text-gray-700">Poste / Fonction</label>
                            <!-- wire:model relie ce champ à la variable $poste du composant -->
                            <select id="poste" wire:model="poste" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-yellow-500 sm:text-sm border p-2">
                                <option value="">Sélectionner un poste...</option>
                                <option value="Stagiaire Professionnel">Stagiaire Professionnel</option>
                                <option value="Employer">Employé</option>
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
                            <label for="entity" class="block text-sm font-medium text-gray-700">Entité</label>
                            <input type="text" id="entity" wire:model="entity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-yellow-500 sm:text-sm border p-2">
                            @error('entity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- SIGLE ENTITE (Ici mis en lecture seule comme demandé précédemment, sinon changer en wire:model) -->
                        <div class="sm:col-span-3">
                            <label for="entity_sigle" class="block text-sm font-medium text-gray-700">Sigle Entité</label>
                            <input 
                                type="text" 
                                id="entity_sigle" 
                                wire:model="entity_sigle" 
                                placeholder="Ex: DPTDSI"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 sm:text-sm border p-2"
                            >
                            <!-- Affichage de l'erreur de validation si nécessaire -->
                            @error('entity_sigle') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- SERVICE -->
                        <div class="sm:col-span-3">
                            <label for="service" class="block text-sm font-medium text-gray-700">Service / Département</label>
                            <input type="text" id="service" wire:model="service" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-yellow-500 sm:text-sm border p-2">
                            @error('service') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- N+1 -->
                        <div class="sm:col-span-3">
                            <label for="n1" class="block text-sm font-medium text-gray-700">Manager (N+1)</label>
                            <input type="text" id="n1" wire:model="n1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-yellow-500 sm:text-sm border p-2">
                            @error('n1') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
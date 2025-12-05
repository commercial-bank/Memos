<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- EN-TÊTE GLOBAL -->
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Paramètres & Configurations
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Gérez les utilisateurs, les entités et les sous-directions.
            </p>
        </div>
        
        <!-- Bouton Ajouter (Visible seulement si on n'est pas sur l'onglet Users) -->
        @if($activeTab !== 'users')
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <button wire:click="openCreateModal" type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Ajouter {{ $activeTab === 'entities' ? 'une Entité' : 'une Sous-Direction' }}
                </button>
            </div>
        @endif
    </div>

    <!-- CONTENEUR PRINCIPAL (Carte blanche) -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200">
        
        <!-- 1. BARRE DE NAVIGATION (ONGLETS) -->
        <div class="border-b border-gray-200 bg-gray-50">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                
                <!-- Onglet Utilisateurs -->
                <button wire:click="$set('activeTab', 'users')" 
                    class="{{ $activeTab === 'users' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Utilisateurs
                </button>

                <!-- Onglet Entités -->
                <button wire:click="$set('activeTab', 'entities')" 
                    class="{{ $activeTab === 'entities' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Entités
                </button>

                <!-- Onglet Sous-Directions -->
                <button wire:click="$set('activeTab', 'sous_directions')" 
                    class="{{ $activeTab === 'sous_directions' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Sous-Directions
                </button>
            </nav>
        </div>

        <!-- 2. BARRE DE RECHERCHE UNIFIÉE -->
        <div class="p-4 bg-white border-b border-gray-200 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
            <div class="relative w-full max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" 
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm transition duration-150 ease-in-out" 
                    placeholder="{{ $activeTab === 'users' ? 'Rechercher un utilisateur (Nom, Email)...' : 'Rechercher par nom ou référence...' }}">
            </div>
            
            <div class="flex items-center gap-3">
                <div wire:loading class="text-yellow-600 text-sm font-medium flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Chargement...
                </div>
                <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                    <span class="font-bold text-gray-900">{{ $data->total() }}</span> résultat(s)
                </span>
            </div>
        </div>

        <!-- 3. CONTENU DYNAMIQUE -->
        <div class="overflow-x-auto relative min-h-[300px]">
            
            <!-- >>> VUE TABLEAU UTILISATEURS (TON DESIGN CONSERVÉ) <<< -->
            @if($activeTab === 'users')
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Utilisateur</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date d'ajout</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Droits Admin</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($data as $user)
                            <tr wire:key="user-row-{{ $user->id }}" class="{{ !$user->is_active ? 'bg-gray-50 opacity-60' : 'hover:bg-gray-50 transition-colors' }}">
                                
                                <!-- Colonne Utilisateur (Avec Nom + Prénom) -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <!-- Avatar Jaune -->
                                            <div class="h-10 w-10 rounded-full {{ !$user->is_active ? 'bg-gray-300 text-gray-500' : 'bg-yellow-100 text-yellow-700 border border-yellow-200' }} flex items-center justify-center font-bold text-sm">
                                                {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <!-- Nom Complet (First Name + Last Name) -->
                                            <div class="text-sm font-bold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Colonne Date -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                                </td>

                                <!-- Colonne Droits Admin (Toggle Jaune) -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($user->is_active)
                                        <label class="inline-flex items-center cursor-pointer relative">
                                            <input 
                                                type="checkbox" 
                                                class="sr-only peer" 
                                                wire:change="toggleAdmin({{ $user->id }})"
                                                wire:loading.attr="disabled"
                                                @if($user->is_admin) checked @endif
                                                @if($user->id === auth()->id()) disabled @endif
                                            >
                                            <!-- Design du toggle (Jaune au clic) -->
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
                                        </label>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Compte inactif</span>
                                    @endif
                                </td>

                                <!-- Colonne Statut -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-green-600' : 'bg-red-500' }}"></span> 
                                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>

                                <!-- Colonne Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($user->id === auth()->id())
                                        <span class="text-gray-400 italic text-xs">Moi-même</span>
                                    @else
                                        <button 
                                            wire:click="toggleStatus({{ $user->id }})"
                                            wire:loading.attr="disabled"
                                            class="{{ $user->is_active 
                                                ? 'text-red-600 hover:text-red-900 hover:bg-red-50' 
                                                : 'text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50' 
                                            }} px-3 py-1.5 rounded-md transition duration-150 ease-in-out font-medium"
                                        >
                                            {{ $user->is_active ? 'Désactiver' : 'Réactiver' }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        <p>Aucun utilisateur trouvé.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif

            <!-- >>> VUE TABLEAU ENTITÉS & SOUS-DIRECTIONS <<< -->
            @if($activeTab !== 'users')
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Création</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($data as $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $item->ref }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $item->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->created_at ? $item->created_at->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="openEditModal({{ $item->id }})" class="text-yellow-600 hover:text-yellow-900 mr-4 font-semibold">Modifier</button>
                                    <button wire:confirm="Êtes-vous sûr de vouloir supprimer cet élément ?" wire:click="deleteStructure({{ $item->id }})" class="text-red-600 hover:text-red-900 font-semibold">Supprimer</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        <p>Aucune donnée trouvée.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
            
        </div>

        <!-- PAGINATION -->
        @if($data->hasPages())
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $data->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL (Pour Entités et Sous-Directions) -->
    @if($showModal)
        <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full {{ $isEditing ? 'bg-yellow-100' : 'bg-green-100' }}">
                            @if($isEditing)
                                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            @else
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                            @endif
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                {{ $isEditing ? 'Modifier' : 'Ajouter' }} {{ $activeTab === 'entities' ? 'une Entité' : 'une Sous-Direction' }}
                            </h3>
                        </div>
                    </div>

                    <form wire:submit.prevent="saveStructure" class="mt-5 sm:mt-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Référence (Code)</label>
                            <input type="text" wire:model="ref" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm" placeholder="Ex: DRH-001">
                            @error('ref') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom</label>
                            <input type="text" wire:model="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm" placeholder="Nom complet...">
                            @error('name') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-500 text-base font-medium text-white hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:col-start-2 sm:text-sm">
                                {{ $isEditing ? 'Enregistrer' : 'Créer' }}
                            </button>
                            <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>
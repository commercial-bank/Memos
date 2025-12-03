<!-- CONTENEUR PRINCIPAL -->
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-10">

                    <!-- EN-TÊTE DE SECTION -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Paramètres de l'application</h2>
                            <p class="text-sm text-gray-500 mt-1">Gérez les configurations des modules et les privilèges d'accès.</p>
                        </div>
                    </div>

                    <!-- 1. SECTION MODULES (GRID 2x2) -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            Configuration des Tables (Modules)
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- GRILLE PRINCIPALE : "grid-cols-2" met les cartes côte à côte sur écran moyen et large -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">

                                <!-- FORMULAIRE 1 : ENTITY (Gauche) -->
                                <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-blue-500 h-full">
                                    <div class="flex items-center justify-between mb-6">
                                        <h3 class="text-xl font-bold text-gray-900">Nouvelle Entité</h3>
                                        <span class="text-xs font-semibold text-blue-700 bg-blue-100 px-2 py-1 rounded-full uppercase">Entity</span>
                                    </div>
                                    
                                    <form action="#" method="POST" class="flex flex-col h-full">
                                        <div class="space-y-4 flex-grow">
                                            <!-- Champ REF -->
                                            <div>
                                                <label for="entity_ref" class="block text-sm font-medium text-gray-700 mb-1">Réf (Ref)</label>
                                                <input type="text" id="entity_ref" name="ref" placeholder="Ex: ENT-001" 
                                                    class="w-full rounded-md border border-gray-300 p-2.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition">
                                            </div>

                                            <!-- Champ NAME -->
                                            <div>
                                                <label for="entity_name" class="block text-sm font-medium text-gray-700 mb-1">Nom (Name)</label>
                                                <input type="text" id="entity_name" name="name" placeholder="Nom de l'entité" 
                                                    class="w-full rounded-md border border-gray-300 p-2.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition">
                                            </div>

                                            <!-- Champ TYPE -->
                                            <div>
                                                <label for="entity_type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                                <select id="entity_type" name="type" 
                                                        class="w-full rounded-md border border-gray-300 p-2.5 bg-white focus:border-blue-500 focus:ring-blue-500 shadow-sm transition">
                                                    <option value="">Sélectionner...</option>
                                                    <option value="interne">Interne</option>
                                                    <option value="externe">Externe</option>
                                                    <option value="partenaire">Partenaire</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Bouton en bas -->
                                        <div class="mt-8">
                                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-md shadow-sm transition duration-150 ease-in-out flex items-center justify-center">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                Ajouter l'Entité
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- FORMULAIRE 2 : SERVICE (Droite) -->
                                <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-green-500 h-full">
                                    <div class="flex items-center justify-between mb-6">
                                        <h3 class="text-xl font-bold text-gray-900">Nouveau Service</h3>
                                        <span class="text-xs font-semibold text-green-700 bg-green-100 px-2 py-1 rounded-full uppercase">Service</span>
                                    </div>
                                    
                                    <form action="#" method="POST" class="flex flex-col h-full">
                                        <div class="space-y-4 flex-grow">
                                            <!-- Champ NAME -->
                                            <div>
                                                <label for="service_name" class="block text-sm font-medium text-gray-700 mb-1">Nom du Service</label>
                                                <input type="text" id="service_name" name="name" placeholder="Ex: Marketing, IT..." 
                                                    class="w-full rounded-md border border-gray-300 p-2.5 focus:border-green-500 focus:ring-green-500 shadow-sm transition">
                                            </div>

                                            <!-- Champ TYPE -->
                                            <div>
                                                <label for="service_type" class="block text-sm font-medium text-gray-700 mb-1">Type de Service</label>
                                                <select id="service_type" name="type" 
                                                        class="w-full rounded-md border border-gray-300 p-2.5 bg-white focus:border-green-500 focus:ring-green-500 shadow-sm transition">
                                                    <option value="">Sélectionner...</option>
                                                    <option value="support">Support</option>
                                                    <option value="operationnel">Opérationnel</option>
                                                    <option value="direction">Direction</option>
                                                </select>
                                            </div>
                                            
                                            <!-- Bloc vide pour équilibrer la hauteur si nécessaire ou description -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optionnel)</label>
                                                <textarea rows="2" class="w-full rounded-md border border-gray-300 p-2.5 focus:border-green-500 focus:ring-green-500 shadow-sm"></textarea>
                                            </div>
                                        </div>

                                        <!-- Bouton en bas -->
                                        <div class="mt-8">
                                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 px-4 rounded-md shadow-sm transition duration-150 ease-in-out flex items-center justify-center">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                Ajouter le Service
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            </div>

                        </div>
                    </div>

                    <hr class="border-gray-200">

                    <!-- 2. SECTION UTILISATEURS (STATIQUE) -->

                  

                        <div class="space-y-4">
                            <!-- Barre de recherche -->
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    Contrôle d'accès & Droits
                                </h3>
                                <div class="relative">
                                    <input 
                                        wire:model.live.debounce.300ms="search" 
                                        type="text" 
                                        placeholder="Rechercher un user..." 
                                        class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 w-64 shadow-sm"
                                    >
                                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                            </div>

                            <!-- Tableau -->
                            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm relative">
                                
                                <!-- Indicateur de chargement discret en haut du tableau -->
                                <div wire:loading.flex class="absolute inset-0 bg-white/50 z-10 flex items-center justify-center backdrop-blur-[1px]">
                                    <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>

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
                                        @forelse ($users as $user)
                                            <tr wire:key="user-row-{{ $user->id }}" class="{{ !$user->is_active ? 'bg-gray-50 opacity-60' : 'hover:bg-gray-50 transition-colors' }}">
                                                
                                                <!-- Colonne Utilisateur -->
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-9 w-9">
                                                            <div class="h-9 w-9 rounded-full {{ !$user->is_active ? 'bg-gray-300 text-gray-500' : 'bg-yellow-100 text-white-700' }} flex items-center justify-center font-bold text-sm">
                                                                {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Colonne Date -->
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $user->created_at->format('d M Y') }}
                                                </td>

                                                <!-- Colonne Droits Admin (CORRIGÉE) -->
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
                                                            <!-- Design du toggle -->
                                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                                        </label>
                                                    @else
                                                        <span class="text-xs text-gray-400">-</span>
                                                    @endif
                                                </td>

                                                <!-- Colonne Statut -->
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600' }}">
                                                        <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-green-600' : 'bg-gray-500' }}"></span> 
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
                                                                : 'text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50' 
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
                                                    Aucun utilisateur trouvé.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-4">
                                {{ $users->links() }}
                            </div>
                        </div>



                </div>    
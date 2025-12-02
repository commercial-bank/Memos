<!-- CONTENEUR PRINCIPAL -->
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-10">

                    <!-- EN-TÊTE DE SECTION -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Paramètres de l'application</h2>
                            <p class="text-sm text-gray-500 mt-1">Gérez les configurations des modules et les privilèges d'accès.</p>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition shadow-sm">
                                Annuler
                            </button>
                            <button type="button" class="px-4 py-2 bg-[#daaf2c] text-white rounded-lg text-sm font-medium hover:bg-[#daaf2c] transition shadow-sm shadow-indigo-200">
                                Enregistrer les modifications
                            </button>
                        </div>
                    </div>

                    <!-- 1. SECTION MODULES (GRID 2x2) -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            Configuration des Tables (Modules)
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- MODULE 1 : PRODUITS (Exemple rempli) -->
                            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-300 relative overflow-hidden">
                                <div class="absolute top-0 right-0 p-4 opacity-10">
                                    <span class="text-6xl font-bold text-gray-900">1</span>
                                </div>
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-800">Module Produits</h4>
                                        <span class="px-2 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-full ring-1 ring-green-600/20">Actif</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Nom de la table (DB)</label>
                                            <input type="text" value="app_products" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                        </div>
                                        <div class="flex gap-3">
                                            <div class="w-1/2">
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Quota Entrées</label>
                                                <input type="number" value="1000" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                            </div>
                                            <div class="w-1/2">
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Visibilité</label>
                                                <select class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                                    <option selected>Public</option>
                                                    <option>Privé</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MODULE 2 : CLIENTS (Exemple rempli) -->
                            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-300 relative overflow-hidden">
                                <div class="absolute top-0 right-0 p-4 opacity-10">
                                    <span class="text-6xl font-bold text-gray-900">2</span>
                                </div>
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-800">Module Clients</h4>
                                        <span class="px-2 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-full ring-1 ring-green-600/20">Actif</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Nom de la table (DB)</label>
                                            <input type="text" value="app_customers" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                        </div>
                                        <div class="flex gap-3">
                                            <div class="w-1/2">
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Quota Entrées</label>
                                                <input type="number" value="5000" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                            </div>
                                            <div class="w-1/2">
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Export</label>
                                                <select class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                                    <option selected>Autorisé</option>
                                                    <option>Bloqué</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MODULE 3 : COMMANDES (Exemple Maintenance) -->
                            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-300 relative overflow-hidden">
                                <div class="absolute top-0 right-0 p-4 opacity-10">
                                    <span class="text-6xl font-bold text-gray-900">3</span>
                                </div>
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-800">Module Commandes</h4>
                                        <span class="px-2 py-1 bg-yellow-50 text-yellow-700 text-xs font-medium rounded-full ring-1 ring-yellow-600/20">Maintenance</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">API Endpoint</label>
                                            <input type="text" value="/api/v1/orders" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                        </div>
                                        <div class="flex gap-3">
                                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg w-full cursor-pointer hover:bg-gray-50">
                                                <input type="checkbox" checked class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                                <span class="text-sm text-gray-700">Lecture seule</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MODULE 4 : STOCKS (Exemple Inactif) -->
                            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-300 relative overflow-hidden">
                                <div class="absolute top-0 right-0 p-4 opacity-10">
                                    <span class="text-6xl font-bold text-gray-900">4</span>
                                </div>
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-800">Module Stocks</h4>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full ring-1 ring-gray-500/20">Inactif</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Nom de la table (DB)</label>
                                            <input type="text" placeholder="Non configuré" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                        </div>
                                        <div class="flex gap-3">
                                            <button type="button" class="w-full py-2.5 text-sm text-indigo-600 font-medium bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">Activer le module</button>
                                        </div>
                                    </div>
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
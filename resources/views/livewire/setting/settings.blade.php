<div class="p-6">
    <!-- 1. EN-TÊTE ET NOTIFICATIONS -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Paramètres Généraux</h1>
            <p class="text-sm text-gray-500">Gestion des utilisateurs et des structures.</p>
        </div>
        
        <!-- Notification Flash -->
        <div x-data="{ show: false, message: '' }"
             @notify.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)"
             x-show="show"
             x-transition
             class="fixed top-5 right-5 z-50 bg-green-500 text-white px-4 py-2 rounded shadow-lg"
             style="display: none;">
            <span x-text="message"></span>
        </div>
    </div>

    <!-- 2. NAVIGATION (ONGLETS) -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button wire:click="$set('activeTab', 'users')"
                    class="{{ $activeTab === 'users' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Utilisateurs
            </button>
            <button wire:click="$set('activeTab', 'entities')"
                    class="{{ $activeTab === 'entities' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Entités
            </button>
            <button wire:click="$set('activeTab', 'sous_directions')"
                    class="{{ $activeTab === 'sous_directions' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Sous-Directions
            </button>
        </nav>
    </div>

    <!-- 3. BARRE D'ACTIONS (RECHERCHE & AJOUT) -->
    <div class="flex justify-between items-center mb-4">
        <div class="w-1/3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher..." class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
        </div>
        
        <!-- Le bouton "Ajouter" ne s'affiche que pour les structures, car les users viennent généralement du LDAP ou d'un autre formulaire -->
        @if($activeTab !== 'users')
            <button wire:click="openCreateModal" class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 text-sm font-medium">
                <i class="fas fa-plus mr-2"></i> Ajouter
            </button>
        @endif
    </div>

    <!-- 4. CONTENU DES TABLEAUX -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        
        <!-- TABLEAU: UTILISATEURS -->
        @if($activeTab === 'users')
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom / Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Poste</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actif</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->last_name }} {{ $user->first_name }}</div>
                                </div>
                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->poste ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button wire:click="toggleAdmin({{ $user->id }})" class="{{ $user->is_admin ? 'text-green-600' : 'text-gray-300' }}">
                                    <i class="fas fa-toggle-{{ $user->is_admin ? 'on' : 'off' }} fa-lg"></i>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button wire:click="toggleStatus({{ $user->id }})" class="{{ $user->is_active ? 'text-green-600' : 'text-red-400' }}">
                                    <i class="fas fa-circle text-xs"></i>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="openEditModal({{ $user->id }})" class="text-yellow-600 hover:text-yellow-900 flex items-center justify-end gap-1 ml-auto">
                                    <i class="fas fa-user-clock"></i> Intérim
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucun utilisateur trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        <!-- TABLEAU: ENTITÉS / SOUS-DIRECTIONS -->
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->ref }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="openEditModal({{ $item->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Éditer</button>
                                <button wire:click="deleteStructure({{ $item->id }})" wire:confirm="Êtes-vous sûr de vouloir supprimer cet élément ?" class="text-red-600 hover:text-red-900">Supprimer</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">Aucune donnée trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $data->links() }}
        </div>
    </div>

    <!-- 5. MODALE UNIQUE (Gère Users et Structures) -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <!-- Overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showModal', false)"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Contenu de la modale -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    
                    <!-- Header Modale -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            @if($activeTab === 'users')
                                Gestion des remplacements
                            @else
                                {{ $isEditing ? 'Modifier' : 'Créer' }} {{ $activeTab === 'entities' ? 'une Entité' : 'une Sous-Direction' }}
                            @endif
                        </h3>
                    </div>

                    <!-- Body Modale -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        
                        <!-- CAS 1 : FORMULAIRE STRUCTURES -->
                        @if($activeTab !== 'users')
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Référence</label>
                                    <input type="text" wire:model="ref" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                    @error('ref') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                                    <input type="text" wire:model="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        
                        <!-- CAS 2 : FORMULAIRE REMPLACEMENTS (USERS) -->
                        @else
                            <div class="space-y-6">
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-info-circle text-blue-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-blue-700">
                                                Vous configurez les remplaçants pour l'utilisateur ID #{{ $itemId }}.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Formulaire Ajout -->
                                <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                                    <h4 class="text-sm font-bold text-gray-700 mb-3">Nouveau remplaçant</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="col-span-2">
                                            <label class="block text-xs font-medium text-gray-500">Remplaçant</label>
                                            <select wire:model="replace_user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm p-2">
                                                <option value="">Sélectionner...</option>
                                                @foreach(\App\Models\User::where('id', '!=', $itemId)->where('is_active', true)->orderBy('last_name')->get() as $u)
                                                    <option value="{{ $u->id }}">{{ $u->last_name }} {{ $u->first_name }} ({{ $u->poste ?? 'N/A' }})</option>
                                                @endforeach
                                            </select>
                                            @error('replace_user_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Du</label>
                                            <input type="date" wire:model="date_begin" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm p-2">
                                            @error('date_begin') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Au</label>
                                            <input type="date" wire:model="date_end" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm p-2">
                                            @error('date_end') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="col-span-2">
                                            <label class="block text-xs font-medium text-gray-500 mb-2">Droits</label>
                                            <div class="flex space-x-4">
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" wire:model="replace_actions" value="VISER" class="rounded text-yellow-600 focus:ring-yellow-500">
                                                    <span class="ml-2 text-sm text-gray-600">Viser</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" wire:model="replace_actions" value="SIGNER" class="rounded text-yellow-600 focus:ring-yellow-500">
                                                    <span class="ml-2 text-sm text-gray-600">Signer</span>
                                                </label>
                                            </div>
                                            @error('replace_actions') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="col-span-2 text-right">
                                            <button type="button" wire:click.prevent="addReplacement" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700">
                                                <i class="fas fa-plus mr-1"></i> Ajouter
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Liste Historique -->
                                <div>
                                    <h4 class="text-sm font-bold text-gray-700 mb-2">Remplacements programmés</h4>
                                    @if(count($userReplacements) > 0)
                                        <div class="overflow-x-auto border rounded-lg">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qui</th>
                                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quand</th>
                                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quoi</th>
                                                        <th class="px-3 py-2 text-right"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @foreach($userReplacements as $rep)
                                                        <tr>
                                                            <td class="px-3 py-2 text-sm text-gray-900">
                                                                {{ $rep->substitute->first_name }} {{ $rep->substitute->last_name }}
                                                            </td>
                                                            <td class="px-3 py-2 text-sm text-gray-500 text-xs">
                                                                {{ \Carbon\Carbon::parse($rep->date_begin_replace)->format('d/m') }} - 
                                                                {{ \Carbon\Carbon::parse($rep->date_end_replace)->format('d/m') }}
                                                            </td>
                                                            <td class="px-3 py-2 text-sm">
                                                                @foreach($rep->action_replace ?? [] as $act)
                                                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-1 rounded">{{ $act }}</span>
                                                                @endforeach
                                                            </td>
                                                            <td class="px-3 py-2 text-right">
                                                                <button wire:click="removeReplacement({{ $rep->id }})" class="text-red-500 hover:text-red-700">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500 italic">Aucun remplacement actif.</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>

                    <!-- Footer Modale -->
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        @if($activeTab !== 'users')
                            <button type="button" wire:click="saveStructure" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Enregistrer
                            </button>
                        @endif
                        <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Fermer
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>
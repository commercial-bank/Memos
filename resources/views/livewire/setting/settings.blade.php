<div class="p-6 bg-gray-50 min-h-screen">
    <!-- 1. EN-TÊTE ET NOTIFICATIONS -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Paramètres & Supervision</h1>
            <p class="text-sm text-gray-500 mt-1">Administration centrale, gestion des utilisateurs et audit des flux.</p>
        </div>
        
        <!-- Notification Flash -->
        <div x-data="{ show: false, message: '' }"
             @notify.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="fixed top-5 right-5 z-50 bg-gray-900 text-white px-6 py-3 rounded-lg shadow-xl flex items-center gap-3"
             style="display: none;">
            <i class="fas fa-check-circle text-green-400"></i>
            <span x-text="message" class="font-medium"></span>
        </div>
    </div>

    <!-- 2. NAVIGATION (ONGLETS MODERNES) -->
    <div class="mb-8">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                @foreach([
                    'users' => 'Utilisateurs',
                    'entities' => 'Entités',
                    'sous_directions' => 'Sous-Directions',
                    'audit' => 'Audit & Suivi'
                ] as $key => $label)
                    <button wire:click="$set('activeTab', '{{ $key }}')"
                            class="{{ $activeTab === $key 
                                ? 'border-[#daaf2c] text-[#daaf2c]' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} 
                                whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 flex items-center gap-2">
                        @if($key === 'audit') <i class="fas fa-eye"></i> @endif
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>
    </div>

    <!-- 3. SECTION AUDIT : DASHBOARD KPI (Visible seulement sur l'onglet Audit) -->
    @if($activeTab === 'audit')
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Card Total -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Mémos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
                        <i class="fas fa-file-alt fa-lg"></i>
                    </div>
                </div>
            </div>
            <!-- Card En Cours -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">En cours</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $stats['pending'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-orange-50 rounded-lg text-orange-600">
                        <i class="fas fa-hourglass-half fa-lg"></i>
                    </div>
                </div>
            </div>
            <!-- Card Signés -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Validés / Signés</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['signed'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-green-50 rounded-lg text-green-600">
                        <i class="fas fa-signature fa-lg"></i>
                    </div>
                </div>
            </div>
            <!-- Card Aujourd'hui -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Créés ce jour</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['today'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-lg text-purple-600">
                        <i class="fas fa-calendar-day fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- 4. BARRE D'ACTIONS -->
    <div class="flex justify-between items-center mb-6">
        <div class="w-full md:w-1/3 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" 
                   placeholder="{{ $activeTab === 'audit' ? 'Rechercher un mémo par objet, ref...' : 'Rechercher...' }}" 
                   class="pl-10 w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#daaf2c] focus:border-[#daaf2c] sm:text-sm py-2.5">
        </div>
        
        @if(!in_array($activeTab, ['users', 'audit']))
            <button wire:click="openCreateModal" class="bg-[#daaf2c] text-black px-5 py-2.5 rounded-lg hover:bg-yellow-500 text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> Ajouter
            </button>
        @endif
    </div>

    <!-- 5. CONTENU DES TABLEAUX -->
    <div class="bg-white shadow-sm ring-1 ring-black ring-opacity-5 rounded-xl overflow-hidden">
        
        <!-- TABLEAU: AUDIT & SUIVI (NOUVEAU) -->
        @if($activeTab === 'audit')
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Référence / Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Objet</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Auteur</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Détails</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data as $memo)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="block text-sm font-bold text-gray-900">{{ $memo->reference ?? 'En attente' }}</span>
                                <span class="block text-xs text-gray-500">{{ $memo->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 line-clamp-1" title="{{ $memo->object }}">{{ $memo->object }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600 mr-2">
                                        {{ substr($memo->user->first_name ?? 'U', 0, 1) }}{{ substr($memo->user->last_name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="text-sm text-gray-900">{{ $memo->user->last_name ?? '' }} {{ $memo->user->first_name ?? '' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusColor = match(true) {
                                        !empty($memo->signature_dir) => 'bg-green-100 text-green-800',
                                        str_contains($memo->status, 'rejet') => 'bg-red-100 text-red-800',
                                        default => 'bg-yellow-100 text-yellow-800'
                                    };
                                    $statusLabel = !empty($memo->signature_dir) ? 'Signé & Validé' : $memo->status;
                                @endphp
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                    {{ ucfirst($statusLabel) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="openAuditDetails({{ $memo->id }})" class="text-[#daaf2c] hover:text-yellow-700 bg-yellow-50 hover:bg-yellow-100 px-3 py-1.5 rounded-md transition-colors">
                                    <i class="fas fa-history mr-1"></i> Suivi
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">Aucun mémo trouvé dans le système.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        <!-- TABLEAU: UTILISATEURS -->
        @elseif($activeTab === 'users')
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Identité</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Poste</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Admin</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-500 font-bold">
                                        {{ substr($user->first_name ?? '?', 0, 1) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->last_name }} {{ $user->first_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->poste ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button wire:click="toggleAdmin({{ $user->id }})" class="focus:outline-none transition-colors {{ $user->is_admin ? 'text-green-600' : 'text-gray-300 hover:text-gray-400' }}">
                                    <i class="fas fa-toggle-{{ $user->is_admin ? 'on' : 'off' }} fa-2x"></i>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button wire:click="toggleStatus({{ $user->id }})" class="focus:outline-none transition-colors {{ $user->is_active ? 'text-green-500' : 'text-red-400' }}">
                                    <i class="fas fa-circle text-xs"></i>
                                    <span class="sr-only">{{ $user->is_active ? 'Actif' : 'Inactif' }}</span>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="openEditModal({{ $user->id }})" class="text-gray-600 hover:text-[#daaf2c] flex items-center justify-end gap-1 ml-auto">
                                    <i class="fas fa-user-clock"></i> Gérer Remplacements
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
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Référence</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $item->ref }}</td>
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
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $data->links() }}
        </div>
    </div>

    
    <!-- 6. MODALE AUDIT & TIMELINE (MODERNE AVEC VISUAL GRAPH) -->
    @if($showAuditModal && $selectedMemo)
        <div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                
                <!-- Background backdrop -->
                <div class="fixed inset-0 bg-gray-900 bg-opacity-80 transition-opacity backdrop-blur-sm" wire:click="$set('showAuditModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    
                    <!-- Header de la Modale Audit -->
                    <div class="bg-white px-8 py-6 border-b border-gray-100 flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                    {{ $selectedMemo->reference ?? 'NON-REF' }}
                                </span>
                                <span class="text-xs text-gray-400 font-mono">{{ $selectedMemo->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mt-2">{{ $selectedMemo->object }}</h3>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- BOUTON PDF AJOUTÉ ICI -->
                            <a href="{{ route('memos.print', $selectedMemo->id) }}" target="_blank" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 text-sm font-bold hover:bg-gray-50 hover:text-red-600 transition-colors shadow-sm">
                                <i class="fas fa-file-pdf text-red-500"></i>
                                <span>Voir PDF</span>
                            </a>

                            <!-- Bouton Fermer -->
                            <button wire:click="$set('showAuditModal', false)" class="text-gray-400 hover:text-gray-800 transition-colors bg-gray-50 hover:bg-gray-100 p-2 rounded-full h-10 w-10 flex items-center justify-center">
                                <i class="fas fa-times fa-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-8 py-6 bg-gray-50/50 max-h-[75vh] overflow-y-auto custom-scrollbar">
                        
                        <!-- 1. VISUAL WORKFLOW GRAPH (NOUVEAU) -->
                        <div class="mb-10">
                            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-6">Parcours Visuel</h4>
                            
                            @php
                                // Préparation des données pour le Graphique
                                // On fusionne le Créateur (Table Memos) + Les acteurs (Table Historiques)
                                $timeline = collect([]);
                                
                                // Étape 1 : Création
                                $timeline->push([
                                    'user' => $selectedMemo->user,
                                    'action' => 'Création',
                                    'date' => $selectedMemo->created_at,
                                    'status' => 'start', // start, valid, reject
                                    'comment' => 'Initié'
                                ]);

                                // Étape 2 : Historique (On trie par ANCIEN -> RÉCENT pour le graphique gauche->droite)
                                foreach($auditHistory->sortBy('created_at') as $h) {
                                    $isReject = str_contains(strtolower($h->workflow_comment ?? ''), 'rejet');
                                    $timeline->push([
                                        'user' => $h->user,
                                        'action' => $h->visa,
                                        'date' => $h->created_at,
                                        'status' => $isReject ? 'reject' : 'valid',
                                        'comment' => $h->workflow_comment
                                    ]);
                                }
                            @endphp

                            <!-- Container Scrollable horizontalement si le workflow est long -->
                            <div class="overflow-x-auto pb-4">
                                <div class="flex items-start min-w-max">
                                    
                                    @foreach($timeline as $index => $step)
                                        <div class="relative flex flex-col items-center group min-w-[140px]">
                                            
                                            <!-- Ligne de connexion (sauf pour le dernier) -->
                                            @if(!$loop->last)
                                                <div class="absolute top-5 left-1/2 w-full h-1 bg-gray-200 -z-10">
                                                    <!-- Si l'étape suivante existe, la ligne est colorée -->
                                                    <div class="h-full bg-green-500 origin-left transition-all duration-1000" style="width: 100%"></div>
                                                </div>
                                            @endif

                                            <!-- Cercle Avatar / Icone -->
                                            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center bg-white shadow-sm z-10 transition-transform transform group-hover:scale-110
                                                {{ $step['status'] === 'start' ? 'border-blue-500 text-blue-500' : '' }}
                                                {{ $step['status'] === 'valid' ? 'border-green-500 text-green-500' : '' }}
                                                {{ $step['status'] === 'reject' ? 'border-red-500 text-red-500' : '' }}
                                            ">
                                                @if($step['status'] === 'start')
                                                    <i class="fas fa-play text-xs"></i>
                                                @elseif($step['status'] === 'reject')
                                                    <i class="fas fa-times text-xs"></i>
                                                @else
                                                    <i class="fas fa-check text-xs"></i>
                                                @endif
                                            </div>

                                            <!-- Info Utilisateur -->
                                            <div class="mt-3 text-center px-2">
                                                <p class="text-xs font-bold text-gray-900 truncate max-w-[120px]">
                                                    {{ $step['user']->first_name ?? '?' }} {{ $step['user']->last_name ?? '' }}
                                                </p>
                                                <p class="text-[10px] text-gray-500 uppercase tracking-wide">{{ $step['action'] }}</p>
                                                <p class="text-[10px] text-gray-400 mt-1 bg-white px-2 py-0.5 rounded-full border border-gray-100 shadow-sm inline-block">
                                                    {{ $step['date']->format('d/m H:i') }}
                                                </p>
                                            </div>

                                            <!-- Tooltip au survol (si commentaire) -->
                                            @if($step['comment'] && $step['status'] !== 'start')
                                                <div class="absolute top-14 opacity-0 group-hover:opacity-100 transition-opacity bg-gray-800 text-white text-xs rounded p-2 w-40 text-center z-20 pointer-events-none transform translate-y-2">
                                                    "{{ Str::limit($step['comment'], 50) }}"
                                                    <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-800 rotate-45"></div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach

                                    <!-- Étape Finale : Statut Actuel (Virtuel) -->
                                    <div class="relative flex flex-col items-center min-w-[100px] opacity-50">
                                        <div class="absolute top-5 right-1/2 w-1/2 h-1 bg-gray-200 -z-10"></div>
                                        <div class="w-10 h-10 rounded-full border-2 border-gray-300 border-dashed flex items-center justify-center bg-gray-50">
                                            <i class="fas fa-flag-checkered text-gray-400"></i>
                                        </div>
                                        <div class="mt-3 text-center">
                                            <p class="text-xs font-bold text-gray-500">Fin</p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- 2. DÉTAILS TEXTUELS (Timeline Verticale) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            
                            <!-- Colonne Gauche : Info Document -->
                            <div class="md:col-span-1 space-y-4">
                                <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Fiche Technique</h4>
                                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-3">
                                    <div>
                                        <label class="block text-xs text-gray-400">Expéditeur</label>
                                        <span class="text-sm font-semibold text-gray-800">{{ $selectedMemo->user->first_name }} {{ $selectedMemo->user->last_name }}</span>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-400">Poste</label>
                                        <span class="text-sm text-gray-600">{{ $selectedMemo->user->poste ?? 'N/A' }}</span>
                                    </div>
                                    <hr class="border-gray-100">
                                    <div>
                                        <label class="block text-xs text-gray-400">Direction Workflow</label>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ ucfirst($selectedMemo->workflow_direction) }}
                                        </span>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-400">Statut Actuel</label>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                            {{ !empty($selectedMemo->signature_dir) ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ !empty($selectedMemo->signature_dir) ? 'Signé' : $selectedMemo->status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Colonne Droite : Historique Détaillé -->
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Journal des actions</h4>
                                <div class="relative border-l-2 border-gray-200 ml-3 space-y-6">
                                    
                                    @forelse($auditHistory as $history)
                                        <div class="relative pl-8 group">
                                            <!-- Point sur la ligne -->
                                            <div class="absolute -left-[9px] top-0 h-4 w-4 rounded-full border-2 border-white transition-all
                                                {{ str_contains(strtolower($history->workflow_comment), 'rejet') ? 'bg-red-500 shadow-red-200' : 'bg-[#daaf2c] shadow-yellow-200' }} shadow-md">
                                            </div>
                                            
                                            <!-- Card Contenu -->
                                            <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                                                <div class="flex justify-between items-start mb-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-bold text-gray-900">
                                                            {{ $history->user->first_name ?? '' }} {{ $history->user->last_name ?? '' }}
                                                        </span>
                                                        <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full uppercase tracking-wide">
                                                            {{ $history->visa }}
                                                        </span>
                                                    </div>
                                                    <span class="text-xs text-gray-400 font-mono">
                                                        {{ $history->created_at->format('d/m/Y H:i') }}
                                                    </span>
                                                </div>
                                                
                                                @if($history->workflow_comment)
                                                    <div class="bg-gray-50 p-3 rounded text-sm text-gray-700 italic border-l-2 {{ str_contains(strtolower($history->workflow_comment), 'rejet') ? 'border-red-400' : 'border-gray-300' }}">
                                                        "{{ $history->workflow_comment }}"
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="relative pl-8">
                                            <p class="text-sm text-gray-400 italic">Aucune action enregistrée après la création.</p>
                                        </div>
                                    @endforelse
                                    
                                    <!-- Point de départ (Création) -->
                                    <div class="relative pl-8 pb-2">
                                        <div class="absolute -left-[9px] top-0 h-4 w-4 rounded-full bg-blue-500 border-2 border-white shadow-md shadow-blue-200"></div>
                                        <div>
                                            <span class="text-sm font-bold text-gray-900">Création du document</span>
                                            <p class="text-xs text-gray-500">Par {{ $selectedMemo->user->first_name }} {{ $selectedMemo->user->last_name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 flex justify-end">
                         <button type="button" wire:click="$set('showAuditModal', false)" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 px-6 rounded-lg shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#daaf2c]">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- 7. MODALE STANDARD (EDITION/CREATION) -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            @if($activeTab === 'users')
                                Gestion des remplacements
                            @else
                                {{ $isEditing ? 'Modifier' : 'Créer' }} {{ $activeTab === 'entities' ? 'une Entité' : 'une Sous-Direction' }}
                            @endif
                        </h3>
                    </div>

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        @if($activeTab !== 'users')
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Référence</label>
                                    <input type="text" wire:model="ref" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#daaf2c] focus:border-[#daaf2c] sm:text-sm">
                                    @error('ref') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                                    <input type="text" wire:model="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#daaf2c] focus:border-[#daaf2c] sm:text-sm">
                                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @else
                            <!-- CODE EXISTANT GESTION REMPLACEMENT (INCHANGÉ) -->
                            <div class="space-y-6 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0"><i class="fas fa-info-circle text-blue-400"></i></div>
                                        <div class="ml-3"><p class="text-sm text-blue-700">Vous configurez les remplaçants pour l'utilisateur ID #{{ $itemId }}.</p></div>
                                    </div>
                                </div>
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
                                                <label class="inline-flex items-center"><input type="checkbox" wire:model="replace_actions" value="VISER" class="rounded text-[#daaf2c] focus:ring-[#daaf2c]"><span class="ml-2 text-sm text-gray-600">Viser</span></label>
                                                <label class="inline-flex items-center"><input type="checkbox" wire:model="replace_actions" value="REJETER" class="rounded text-[#daaf2c] focus:ring-[#daaf2c]"><span class="ml-2 text-sm text-gray-600">Rejeter</span></label>
                                                <label class="inline-flex items-center"><input type="checkbox" wire:model="replace_actions" value="SIGNER" class="rounded text-[#daaf2c] focus:ring-[#daaf2c]"><span class="ml-2 text-sm text-gray-600">Signer</span></label>
                                            </div>
                                            @error('replace_actions') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-span-2 text-right">
                                            <button type="button" wire:click.prevent="addReplacement" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700"><i class="fas fa-plus mr-1"></i> Ajouter</button>
                                        </div>
                                    </div>
                                </div>
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
                                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $rep->substitute->first_name }} {{ $rep->substitute->last_name }}</td>
                                                            <td class="px-3 py-2 text-sm text-gray-500 text-xs">{{ \Carbon\Carbon::parse($rep->date_begin_replace)->format('d/m') }} - {{ \Carbon\Carbon::parse($rep->date_end_replace)->format('d/m') }}</td>
                                                            <td class="px-3 py-2 text-sm">
                                                                @foreach($rep->action_replace ?? [] as $act) <span class="inline-block bg-blue-100 text-blue-800 text-xs px-1 rounded">{{ $act }}</span> @endforeach
                                                            </td>
                                                            <td class="px-3 py-2 text-right">
                                                                <button wire:click="removeReplacement({{ $rep->id }})" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
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

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        @if($activeTab !== 'users')
                            <button type="button" wire:click="saveStructure" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#daaf2c] text-base font-medium text-black hover:bg-yellow-500 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm font-bold">Enregistrer</button>
                        @endif
                        <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
<div class="p-6 bg-gray-50 min-h-screen font-sans">
    <!-- CHARTE GRAPHIQUE & STYLES NASA DNA -->
    <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(112, 113, 115, 0.2);
        }
        .text-accent-gold { color: #daaf2c; }
        .bg-accent-gold { background-color: #daaf2c; }
        .text-accent-gray { color: #707173; }
        .border-gold { border-color: #daaf2c; }
        
        /* Arborescence DNA */
        .dna-line { width: 2px; background: linear-gradient(to bottom, #daaf2c, #707173); }
        .node-active { border: 2px solid #daaf2c; box-shadow: 0 0 15px rgba(218, 175, 44, 0.3); }
        
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #daaf2c; border-radius: 10px; }
        
        @keyframes pulse-border {
            0% { border-color: rgba(218, 175, 44, 0.4); }
            50% { border-color: rgba(218, 175, 44, 1); }
            100% { border-color: rgba(218, 175, 44, 0.4); }
        }
        .animate-border { animation: pulse-border 2s infinite; }
    </style>

    <!-- 1. EN-TÊTE DYNAMIQUE -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="h-10 w-1 bg-[#daaf2c] rounded-full"></div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tighter uppercase">
                    {{ $viewingMemoId ? 'Analyse du Memo' : 'Centre de Commande' }}
                </h1>
            </div>
            <p class="text-sm text-[#707173] mt-1 font-medium ml-4 italic">
                {{ $viewingMemoId ? 'Consultation de la généalogie et des états d\'intégrité.' : 'Supervision des flux, personnel et structures.' }}
            </p>
        </div>
        
        @if($viewingMemoId)
            <button wire:click="closeAuditDetails" class="bg-white border border-[#707173] text-[#707173] px-6 py-2 rounded-full text-xs font-black uppercase tracking-widest hover:bg-gray-900 hover:text-[#daaf2c] transition-all flex items-center gap-2 shadow-sm">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </button>
        @endif
    </div>

    @if(!$viewingMemoId)
        <!-- NAVIGATION PAR ONGLETS -->
        <div class="mb-8 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                @foreach([
                    'users' => ['label' => 'Personnel', 'icon' => 'fa-users-cog'],
                    'Direction' => ['label' => 'Directions', 'icon' => 'fa-building'],
                    'Sous-Direction' => ['label' => 'Sous Directions', 'icon' => 'fa-sitemap'],
                    'Departement' => ['label' => 'Départements', 'icon' => 'fa-layer-group'],
                    'Service' => ['label' => 'Services', 'icon' => 'fa-concierge-bell'],
                    'audit' => ['label' => 'Supervision & Audit', 'icon' => 'fa-dna']
                ] as $key => $tab)
                    <button wire:click="$set('activeTab', '{{ $key }}')"
                            class="{{ $activeTab === $key ? 'border-[#daaf2c] text-[#daaf2c]' : 'border-transparent text-[#707173] hover:text-gray-700' }} 
                            whitespace-nowrap py-4 px-1 border-b-2 font-black text-[11px] uppercase tracking-widest transition-all flex items-center gap-2">
                        <i class="fas {{ $tab['icon'] }}"></i>
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- BARRE D'ACTIONS & RECHERCHE -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div class="w-full md:w-1/3 relative group">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-[#707173] text-sm opacity-50 group-focus-within:text-[#daaf2c]"></i>
                <input wire:model.live.debounce.300ms="search" type="text" 
                       placeholder="RECHERCHER DANS CETTE SECTION..." 
                       class="pl-12 w-full bg-white border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-[#daaf2c] focus:border-[#daaf2c] text-xs font-bold uppercase py-3 tracking-widest">
            </div>
            
            @if(!in_array($activeTab, ['users', 'audit']))
                <button wire:click="openCreateModal" class="bg-gray-900 text-[#daaf2c] px-6 py-3 rounded-xl hover:bg-[#daaf2c] hover:text-black text-xs font-black uppercase tracking-widest shadow-xl transition-all flex items-center gap-2 border border-[#707173]">
                    <i class="fas fa-plus-circle"></i> Ajouter Nouveau
                </button>
            @endif
        </div>

        <!-- CONTENU DES TABLEAUX -->
        <div class="bg-white shadow-2xl rounded-3xl overflow-hidden border border-gray-100">
            @if($activeTab === 'audit')
                <!-- TABLEAU AUDIT -->
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest">
                        <tr>
                            <th class="px-6 py-4 text-left">Jeton</th>
                            <th class="px-6 py-4 text-left">Document & Référence</th>
                            <th class="px-6 py-4 text-left">Auteur</th>
                            <th class="px-6 py-4 text-center">Type</th>
                            <th class="px-6 py-4 text-right">Analyse</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($data as $memo)
                            <tr class="hover:bg-gray-50 transition-all">
                                <td class="px-6 py-4">
                                    @if($memo->qr_code)
                                        <div class="p-1 bg-white border border-gray-200 rounded inline-block shadow-sm">
                                            {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(40)->generate(route('memo.verify', $memo->qr_code)) !!}
                                        </div>
                                    @else
                                        <i class="fas fa-shield-slash text-gray-200 fa-2x"></i>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="block text-xs font-black text-gray-800 uppercase truncate max-w-xs">{{ $memo->object }}</span>
                                    <span class="text-[9px] font-mono text-blue-600 tracking-tighter">{{ $memo->reference ?? 'ATTENTE' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-xs font-bold text-gray-900">{{ $memo->user->last_name }}</p>
                                    <p class="text-[9px] text-[#707173] font-black uppercase">{{ $memo->user->entity->ref ?? 'EXT' }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-gray-50 text-[#707173] rounded-full text-[9px] font-black uppercase">
                                        {{ $memo->parent_id ? 'Réponse' : 'Original' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button wire:click="openAuditDetails({{ $memo->id }})" class="bg-gray-900 text-[#daaf2c] px-4 py-2 rounded-full text-[9px] font-black uppercase tracking-widest transition-all shadow-lg hover:bg-[#daaf2c] hover:text-black">
                                        <i class="fas fa-dna mr-1"></i> Analyser 
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-[#707173] uppercase text-[10px] font-black italic">Aucune donnée certifiée.</td></tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($activeTab === 'users')
                <!-- TABLEAU PERSONNEL -->
                <table class="min-w-full divide-y divide-gray-100 text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-[#707173] uppercase tracking-widest">Personnel</th>
                            <th class="px-6 py-4 text-[10px] font-black text-[#707173] uppercase tracking-widest">Poste</th>
                            <th class="px-6 py-4 text-center text-[10px] font-black text-[#707173] uppercase tracking-widest">Admin</th>
                            <th class="px-6 py-4 text-center text-[10px] font-black text-[#707173] uppercase tracking-widest">État</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-[#707173] uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($data as $user)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 flex items-center gap-3">
                                    <div class="h-10 w-10 bg-gray-900 rounded-2xl flex items-center justify-center text-[#daaf2c] font-black">
                                        {{ substr($user->first_name ?? '?', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">{{ $user->last_name }} {{ $user->first_name }}</p>
                                        <p class="text-[10px] font-mono text-[#707173] uppercase tracking-tighter">{{ $user->email }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs font-medium text-[#707173] uppercase italic">{{ $user->poste ?? 'Non défini' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <button wire:click="toggleAdmin({{ $user->id }})" class="transition-all {{ $user->is_admin ? 'text-[#daaf2c]' : 'text-gray-200 hover:text-[#707173]' }}">
                                        <i class="fas fa-crown fa-lg"></i>
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button wire:click="confirmToggleStatus({{ $user->id }})" 
                                            class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $user->is_active ? 'bg-green-50 text-green-600 border-green-200' : 'bg-red-50 text-red-600 border-red-200' }}">
                                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button wire:click="openEditModal({{ $user->id }})" class="text-[#707173] hover:text-gray-900 bg-gray-50 p-2 rounded-lg"><i class="fas fa-user-shield"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            @elseif(in_array($activeTab, ['Direction', 'Sous-Direction', 'Departement', 'Service']))
                <!-- TABLEAU GÉNÉRIQUE DES ENTITÉS -->
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-[#707173] uppercase tracking-widest">Référence Code</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-[#707173] uppercase tracking-widest">Libellé ({{ $activeTab }})</th>
                            @if($activeTab !== 'Direction')
                                <th class="px-6 py-4 text-left text-[10px] font-black text-[#707173] uppercase tracking-widest">Rattaché à</th>
                            @endif
                            <th class="px-6 py-4 text-right text-[10px] font-black text-[#707173] uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($data as $item)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4 text-xs font-black text-gray-900 font-mono tracking-widest uppercase">{{ $item->ref }}</td>
                                <td class="px-6 py-4 text-xs font-bold text-[#707173] uppercase">{{ $item->name }}</td>
                                @if($activeTab !== 'Direction')
                                    <td class="px-6 py-4">
                                        @php $parent = \App\Models\Entity::find($item->upper_id); @endphp
                                        <span class="text-[9px] font-black text-blue-600 bg-blue-50 px-2 py-1 rounded">
                                            {{ $parent ? $parent->name : 'N/A' }}
                                        </span>
                                    </td>
                                @endif
                                <td class="px-6 py-4 text-right text-sm">
                                    <button wire:click="openEditModal({{ $item->id }})" class="text-blue-600 hover:underline mr-4 font-bold text-[10px] uppercase">Modifier</button>
                                    <button wire:click="confirmDeleteStructure({{ $item->id }})" 
                                            class="text-red-500 hover:text-red-700 font-black text-[10px] uppercase tracking-widest bg-red-50 px-3 py-1 rounded-lg transition-all">
                                        <i class="fas fa-trash-alt mr-1"></i> Supprimer
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $data->links() }}
            </div>
        </div>

    @else
        <!-- VUE DÉTAILLÉE : ANALYSE HIERARCHIE (DNA VIEW) -->
        <!-- (Gardé intact comme dans votre code d'origine) -->
        <div class="animate-in fade-in zoom-in duration-300">
            <div class="grid grid-cols-12 gap-8">
                <!-- Colonne Gauche -->
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    <div class="glass-panel p-8 rounded-[2.5rem] shadow-xl border-b-4 border-[#daaf2c] flex flex-col items-center">
                        <h4 class="text-[10px] font-black text-[#707173] uppercase tracking-widest mb-6">Certification Digitale</h4>
                        @if($selectedMemo->qr_code)
                            <div class="p-5 bg-white rounded-3xl shadow-inner border-2 animate-border mb-6">
                                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->generate(route('memo.verify', $selectedMemo->qr_code)) !!}
                            </div>
                        @endif
                    </div>
                    <div class="bg-gray-900 text-white p-8 rounded-[2.5rem] shadow-2xl">
                        <h4 class="text-[10px] font-black text-[#daaf2c] uppercase tracking-widest mb-6 border-b border-white/10 pb-2">Identité du Dossier</h4>
                        <div class="space-y-4 text-xs">
                            <p class="uppercase">{{ $selectedMemo->object }}</p>
                            <p>Émis le: {{ $selectedMemo->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
                <!-- Colonne Droite DNA -->
                <div class="col-span-12 lg:col-span-8">
                    <div class="bg-white p-10 rounded-[3rem] shadow-xl border border-gray-100 min-h-full">
                        <h4 class="text-xs font-black text-[#707173] uppercase tracking-[0.3em] mb-12 flex items-center gap-3">
                            <i class="fas fa-project-diagram text-[#daaf2c]"></i> Hiérarchie du Flux
                        </h4>
                        <div class="relative pl-8 md:pl-20">
                            <div class="dna-line absolute left-[51px] md:left-[99px] top-0 bottom-0"></div>
                            <!-- Logique DNA Parent/Document/Fils ici... -->
                            <!-- (Code DNA existant conservé) -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODALE PRINCIPALE (MODIFIÉE POUR LES USERS) -->
    @if($showModal)
        <div class="fixed inset-0 z-[200] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">
                    <div class="bg-white px-8 pt-8 pb-4 border-b">
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-widest">
                            @if($activeTab === 'users') 
                                Gestion des Paramètres Agents
                            @else 
                                {{ $isEditing ? 'Éditer' : 'Créer' }} {{ $activeTab }} 
                            @endif
                        </h3>
                    </div>

                    <div class="px-8 py-8 sm:p-10">
                        @if(!in_array($activeTab, ['users', 'audit']))
                            <!-- FORMULAIRE ENTITÉ GÉNÉRIQUE -->
                            <div class="space-y-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-[#707173] uppercase tracking-widest mb-1">Code de Référence</label>
                                        <input type="text" wire:model="ref" class="block w-full bg-gray-50 border-gray-200 rounded-xl shadow-sm focus:ring-[#daaf2c] focus:border-[#daaf2c] text-sm font-bold p-3 uppercase tracking-tighter">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-[#707173] uppercase tracking-widest mb-1">Libellé</label>
                                        <input type="text" wire:model="name" class="block w-full bg-gray-50 border-gray-200 rounded-xl shadow-sm focus:ring-[#daaf2c] focus:border-[#daaf2c] text-sm font-bold p-3 uppercase">
                                    </div>
                                </div>
                                @if($activeTab !== 'Direction')
                                    <div>
                                        <label class="block text-[10px] font-black text-[#707173] uppercase mb-1">Structure Parente</label>
                                        <select wire:model="upper_id" class="w-full bg-gray-50 border-gray-200 rounded-xl p-3 text-xs font-bold uppercase">
                                            <option value="">-- CHOISIR LE PARENT --</option>
                                            @php
                                                $targetType = match($activeTab) {
                                                    'Sous-Direction' => 'Direction',
                                                    'Departement'    => 'Sous-Direction',
                                                    'Service'        => 'Departement',
                                                    default          => null
                                                };
                                                $parents = $targetType ? \App\Models\Entity::where('type', $targetType)->get() : [];
                                            @endphp
                                            @foreach($parents as $p)
                                                <option value="{{ $p->id }}">{{ $p->ref }} - {{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>

                        @elseif($activeTab === 'users')
                            <!-- LOGIQUE UTILISATEUR COMPLÈTE (PRO + REMPLACEMENTS) -->
                            <div class="space-y-8 max-h-[65vh] overflow-y-auto custom-scrollbar pr-2">
                                
                                <!-- SECTION A : INFOS PROFESSIONNELLES (POUR L'ADMIN) -->
                                <div class="bg-gray-50 p-6 rounded-[2.5rem] border border-gray-200">
                                    <h4 class="text-[10px] font-black text-gray-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                                        <i class="fas fa-user-tie text-[#daaf2c]"></i> Informations Métiers
                                    </h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Poste -->
                                        <div class="col-span-2">
                                            <label class="block text-[9px] font-black text-[#707173] uppercase mb-1 ml-1">Poste / Fonction</label>
                                            <select wire:model="poste" class="w-full bg-white border-gray-200 rounded-xl p-3 text-xs font-bold uppercase">
                                                <option value="">-- CHOISIR --</option>
                                                @foreach(App\Enums\Poste::cases() as $posteCase)
                                                    <option value="{{ $posteCase->value }}">{{ $posteCase->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Direction -->
                                        <div>
                                            <label class="block text-[9px] font-black text-[#707173] uppercase mb-1 ml-1">Direction</label>
                                            <select wire:model.live="dir_id" class="w-full bg-white border-gray-200 rounded-xl p-3 text-xs font-bold uppercase">
                                                <option value="">-- DIRECTION --</option>
                                                @foreach($directions_list as $d) <option value="{{ $d->id }}">{{ $d->name }}</option> @endforeach
                                            </select>
                                        </div>

                                        <!-- Sous-Direction -->
                                        <div>
                                            <label class="block text-[9px] font-black text-[#707173] uppercase mb-1 ml-1">Sous-Direction</label>
                                            <select wire:model.live="sd_id" class="w-full bg-white border-gray-200 rounded-xl p-3 text-xs font-bold uppercase">
                                                <option value="">-- SOUS-DIRECTION --</option>
                                                @foreach($sd_list as $sd) <option value="{{ $sd->id }}">{{ $sd->name }}</option> @endforeach
                                            </select>
                                        </div>

                                        <!-- Département -->
                                        <div>
                                            <label class="block text-[9px] font-black text-[#707173] uppercase mb-1 ml-1">Département</label>
                                            <select wire:model.live="dep_id" class="w-full bg-white border-gray-200 rounded-xl p-3 text-xs font-bold uppercase">
                                                <option value="">-- DÉPARTEMENT --</option>
                                                @foreach($dep_list as $dep) <option value="{{ $dep->id }}">{{ $dep->name }}</option> @endforeach
                                            </select>
                                        </div>

                                        <!-- Service -->
                                        <div>
                                            <label class="block text-[9px] font-black text-[#707173] uppercase mb-1 ml-1">Service</label>
                                            <select wire:model="serv_id" class="w-full bg-white border-gray-200 rounded-xl p-3 text-xs font-bold uppercase">
                                                <option value="">-- SERVICE --</option>
                                                @foreach($serv_list as $s) <option value="{{ $s->id }}">{{ $s->name }}</option> @endforeach
                                            </select>
                                        </div>

                                        <!-- Manager -->
                                        <div class="col-span-2">
                                            <label class="block text-[9px] font-black text-[#707173] uppercase mb-1 ml-1">Manager (N+1)</label>
                                            <select wire:model="manager_id" class="w-full bg-white border-gray-200 rounded-xl p-3 text-xs font-bold uppercase">
                                                <option value="null">-- CHOISIR MANAGER --</option>
                                                @foreach(\App\Models\User::where('id', '!=', $itemId)->where('is_active', true)->get() as $u) 
                                                    <option value="{{ $u->id }}">{{ $u->last_name }} {{ $u->first_name }}</option> 
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-span-2 text-right mt-2">
                                            <button wire:click.prevent="saveUserProInfo" class="bg-[#daaf2c] text-black px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black hover:text-[#daaf2c] transition-all shadow-md">
                                                Mettre à jour le poste
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="h-px bg-gray-100 flex-1"></div>
                                    <span class="text-[9px] font-black text-[#707173] uppercase tracking-[0.3em]">Zone de Remplacement</span>
                                    <div class="h-px bg-gray-100 flex-1"></div>
                                </div>

                                <!-- SECTION B : LOGIQUE REMPLACEMENT (D'ORIGINE) -->
                                <div class="p-4 bg-white rounded-3xl border border-gray-100">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="col-span-2">
                                            <label class="block text-[10px] font-black text-[#707173] uppercase mb-1">Agent Remplaçant</label>
                                            <select wire:model="replace_user_id" class="w-full bg-gray-50 border-gray-200 rounded-xl p-3 text-xs font-bold uppercase">
                                                <option value="">-- CHOISIR --</option>
                                                @foreach(\App\Models\User::where('id', '!=', $itemId)->where('is_active', true)->get() as $u) <option value="{{ $u->id }}">{{ $u->last_name }} {{ $u->first_name }}</option> @endforeach
                                            </select>
                                        </div>
                                        <div><label class="block text-[10px] font-black text-[#707173] uppercase mb-1">Du</label><input type="date" wire:model="date_begin" class="w-full bg-gray-50 border-gray-200 rounded-xl p-3 text-xs"></div>
                                        <div><label class="block text-[10px] font-black text-[#707173] uppercase mb-1">Au</label><input type="date" wire:model="date_end" class="w-full bg-gray-50 border-gray-200 rounded-xl p-3 text-xs"></div>
                                        <div class="col-span-2">
                                            <p class="text-[10px] font-black text-[#707173] uppercase mb-3">Privilèges délégués</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach(['VISER', 'REJETER', 'SIGNER'] as $action) 
                                                    <label class="inline-flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-xl border border-gray-200 cursor-pointer">
                                                        <input type="checkbox" wire:model="replace_actions" value="{{ $action }}" class="rounded text-[#daaf2c] focus:ring-[#daaf2c]">
                                                        <span class="text-[10px] font-black text-gray-700 uppercase">{{ $action }}</span>
                                                    </label> 
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-span-2 text-right">
                                            <button wire:click.prevent="addReplacement" class="bg-gray-900 text-[#daaf2c] px-6 py-2 rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-[#daaf2c] hover:text-black">Déléguer</button>
                                        </div>
                                    </div>

                                    <div class="mt-6 border-t pt-4">
                                        <h4 class="text-[10px] font-black text-[#707173] uppercase tracking-widest mb-4">Historique Actif</h4>
                                        @foreach($userReplacements as $rep)
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl mb-2 border border-gray-100">
                                                <div>
                                                    <p class="text-[10px] font-bold text-gray-900 uppercase underline decoration-[#daaf2c]">{{ $rep->substitute->last_name }}</p>
                                                    <p class="text-[9px] text-[#707173] font-mono">{{ $rep->date_begin_replace }} / {{ $rep->date_end_replace }}</p>
                                                </div>
                                                <button wire:click="removeReplacement({{ $rep->id }})" class="text-red-400 hover:text-red-600 p-2"><i class="fas fa-trash-alt"></i></button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="bg-gray-50 px-8 py-6 sm:px-10 flex flex-row-reverse gap-3">
                        @if($activeTab !== 'users') 
                            <button type="button" wire:click="saveStructure" class="px-8 py-3 bg-[#daaf2c] text-black rounded-full text-xs font-black uppercase tracking-widest shadow-lg hover:bg-yellow-500 transition-all">Valider</button> 
                        @endif
                        <button type="button" wire:click="$set('showModal', false)" class="px-8 py-3 bg-white text-[#707173] rounded-full text-xs font-black uppercase tracking-widest border border-gray-200 hover:bg-gray-50">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL DE MOTIF DE BLOCAGE (D'ORIGINE) -->
    @if($showDeactivationModal)
        <div class="fixed inset-0 z-[300] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/90 backdrop-blur-sm transition-opacity" wire:click="$set('showDeactivationModal', false)"></div>
                <div class="relative bg-white rounded-[2rem] shadow-2xl max-w-md w-full p-8 border-t-4 border-red-500 overflow-hidden text-center">
                    <div class="h-16 w-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-user-slash fa-2x"></i></div>
                    <h3 class="text-xl font-black text-gray-900 uppercase">Suspension de Compte</h3>
                    <div class="space-y-4 mt-6">
                        <textarea wire:model="blocking_reason" rows="4" placeholder="Motif du blocage..." class="w-full bg-gray-50 border-gray-200 rounded-2xl p-4 text-sm font-medium focus:ring-red-500 focus:border-red-500"></textarea>
                        @error('blocking_reason') <span class="text-[10px] text-red-500 font-bold uppercase">{{ $message }}</span> @enderror
                        <button wire:click="processDeactivation" class="w-full py-4 bg-gray-900 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-red-600 transition-all shadow-lg">Confirmer le blocage</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL DE CONFIRMATION DE SUPPRESSION (STYLE NASA DNA) -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[400] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <!-- Overlay sombre -->
                <div class="fixed inset-0 bg-gray-900/90 backdrop-blur-md transition-opacity" wire:click="$set('showDeleteModal', false)"></div>
                
                <!-- Contenu de la modale -->
                <div class="relative bg-white rounded-[2.5rem] shadow-2xl max-w-md w-full p-10 border-t-8 border-red-600 overflow-hidden text-center transform transition-all animate-in zoom-in duration-200">
                    
                    <!-- Icône d'alerte -->
                    <div class="h-20 w-20 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    
                    <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter mb-2">Confirmation Requise</h3>
                    
                    <p class="text-sm text-[#707173] font-medium mb-8 italic">
                        Êtes-vous sûr de vouloir supprimer définitivement la structure <br>
                        <span class="text-red-600 font-black uppercase not-italic">"{{ $structureToDeleteName }}"</span> ?
                        <br><br>
                        <span class="text-[10px] bg-red-50 px-2 py-1 rounded text-red-500 font-bold uppercase tracking-widest">
                            Attention : Cette action est irréversible.
                        </span>
                    </p>

                    <div class="flex flex-col gap-3">
                        <!-- Bouton Confirmer -->
                        <button wire:click="deleteStructure" 
                                class="w-full py-4 bg-red-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-red-700 transition-all shadow-lg flex items-center justify-center gap-2">
                            <i class="fas fa-check-circle"></i> Confirmer la suppression
                        </button>
                        
                        <!-- Bouton Annuler -->
                        <button wire:click="$set('showDeleteModal', false)" 
                                class="w-full py-4 bg-gray-100 text-[#707173] rounded-2xl font-black uppercase tracking-widest hover:bg-gray-200 transition-all">
                            Annuler l'opération
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
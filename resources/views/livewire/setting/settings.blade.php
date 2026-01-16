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
            <nav class="-mb-px flex space-x-8 overflow-x-auto custom-scrollbar pb-2">
                @foreach([
                    'users' => ['label' => 'Personnel', 'icon' => 'fa-users-cog'],
                    'Direction' => ['label' => 'Directions', 'icon' => 'fa-building'],
                    'Sous-Direction' => ['label' => 'Sous Directions', 'icon' => 'fa-sitemap'],
                    'Departement' => ['label' => 'Départements', 'icon' => 'fa-layer-group'],
                    'Service' => ['label' => 'Services', 'icon' => 'fa-concierge-bell'],
                    'audit' => ['label' => 'Audit & Qualité', 'icon' => 'fa-dna'],
                    'workflow' => ['label' => 'Contrôle Trafic', 'icon' => 'fa-satellite-dish'] 
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
            
            @if(!in_array($activeTab, ['users', 'audit', 'workflow']))
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
            
            @elseif($activeTab === 'workflow')
                <!-- TABLEAU SUPERVISION WORKFLOW (GOD MODE) -->
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest">
                        <tr>
                            <th class="px-6 py-4 text-left">Dossier / Référence</th>
                            <th class="px-6 py-4 text-left">Initiateur</th>
                            <th class="px-6 py-4 text-left">Localisation Actuelle</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Intervention</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($data as $memo)
                            <tr class="hover:bg-yellow-50 transition-all group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-gray-100 rounded text-gray-400 group-hover:text-[#daaf2c]">
                                            <i class="fas fa-file-contract"></i>
                                        </div>
                                        <div>
                                            <span class="block text-xs font-black text-gray-800 uppercase truncate max-w-xs">{{ $memo->object }}</span>
                                            <span class="text-[9px] font-mono text-blue-600 tracking-tighter">{{ $memo->reference ?? 'NON-ENREGISTRÉ' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-[10px] font-bold text-gray-900 uppercase">{{ $memo->user->last_name }} {{ substr($memo->user->first_name, 0, 1) }}.</p>
                                    <p class="text-[9px] text-[#707173]">{{ $memo->created_at->format('d/m/Y') }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <!-- Affichage dynamique des détenteurs JSON -->
                                    <div class="flex -space-x-2">
                                        @php 
                                            $holdersIds = $memo->current_holders ?? [];
                                            $holdersUsers = \App\Models\User::whereIn('id', $holdersIds)->get();
                                        @endphp
                                        @forelse($holdersUsers as $h)
                                            <div title="Chez : {{ $h->last_name }}" class="h-8 w-8 rounded-full bg-[#daaf2c] border-2 border-white flex items-center justify-center text-[9px] font-black text-white shadow-sm cursor-help">
                                                {{ substr($h->last_name, 0, 2) }}
                                            </div>
                                        @empty
                                            <span class="text-[9px] font-bold text-red-500 bg-red-50 px-2 py-1 rounded">FLOTTANT</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 rounded text-[9px] font-black uppercase tracking-widest border 
                                        {{ $memo->status == 'cloture' ? 'bg-green-50 text-green-600 border-green-200' : 'bg-blue-50 text-blue-600 border-blue-200' }}">
                                        {{ $memo->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button wire:click="openWorkflowIntervention({{ $memo->id }})" 
                                            class="bg-gray-900 text-[#daaf2c] px-4 py-2 rounded-full text-[9px] font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all shadow-lg flex items-center gap-2 ml-auto">
                                        <i class="fas fa-bolt"></i> Intervenir
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-[#707173] uppercase text-[10px] font-black italic">Aucun flux actif.</td></tr>
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
                                    <!-- button wire:click="confirmDeleteStructure({{ $item->id }})" 
                                            class="text-red-500 hover:text-red-700 font-black text-[10px] uppercase tracking-widest bg-red-50 px-3 py-1 rounded-lg transition-all">
                                        <i class="fas fa-trash-alt mr-1"></i> Supprimer
                                    </button -->
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
        <div class="animate-in fade-in zoom-in duration-300">
            <div class="grid grid-cols-12 gap-8">
                <!-- Colonne Gauche -->
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    
                    <!-- PANNEAU CERTIFICATION -->
                    <div class="glass-panel p-8 rounded-[2.5rem] shadow-xl border-b-4 border-[#daaf2c] flex flex-col items-center text-center">
                        <h4 class="text-[10px] font-black text-[#707173] uppercase tracking-widest mb-6">Certification Digitale</h4>
                        
                        @if($selectedMemo->qr_code)
                            <!-- QR CODE -->
                            <div class="p-5 bg-white rounded-3xl shadow-inner border-2 animate-border mb-6">
                                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->generate(route('memo.verify', $selectedMemo->qr_code)) !!}
                            </div>

                            <!-- ZONE LIEN DE VÉRIFICATION (NOUVEAU) -->
                            <div class="w-full mt-2">
                                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-2">Accès Public</p>
                                
                                <a href="{{ route('memo.verify', $selectedMemo->qr_code) }}" 
                                   target="_blank"
                                   class="group flex items-center justify-between w-full p-3 bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-md hover:border-[#daaf2c] transition-all duration-300">
                                    
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <!-- Icône -->
                                        <div class="h-10 w-10 rounded-xl bg-gray-50 group-hover:bg-[#daaf2c] text-[#daaf2c] group-hover:text-white flex items-center justify-center transition-colors">
                                            <i class="fas fa-globe-africa text-sm"></i>
                                        </div>
                                        
                                        <!-- Texte -->
                                        <div class="flex flex-col text-left">
                                            <span class="text-[9px] font-black text-gray-900 uppercase">Voir la page de contrôle</span>
                                            <span class="text-[8px] text-gray-400 font-mono truncate max-w-[120px]">
                                                Token: {{ substr($selectedMemo->qr_code, 0, 15) }}...
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Flèche -->
                                    <div class="h-6 w-6 rounded-full border border-gray-100 flex items-center justify-center group-hover:border-[#daaf2c]">
                                        <i class="fas fa-external-link-alt text-[10px] text-gray-400 group-hover:text-[#daaf2c]"></i>
                                    </div>
                                </a>
                            </div>
                        @else
                            <div class="p-6 bg-gray-50 rounded-3xl border border-gray-200 text-gray-400">
                                <i class="fas fa-qrcode fa-2x mb-2 opacity-50"></i>
                                <p class="text-[10px] font-bold uppercase">Aucun code généré</p>
                            </div>
                        @endif
                    </div>

                    <!-- PANNEAU INFO -->
                    <div class="bg-gray-900 text-white p-8 rounded-[2.5rem] shadow-2xl">
                        <h4 class="text-[10px] font-black text-[#daaf2c] uppercase tracking-widest mb-6 border-b border-white/10 pb-2">Identité du Dossier</h4>
                        <div class="space-y-4 text-xs">
                            <div>
                                <span class="block text-[9px] text-gray-500 uppercase font-bold">Objet</span>
                                <p class="uppercase font-bold tracking-tight">{{ $selectedMemo->object }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-[9px] text-gray-500 uppercase font-bold">Émis le</span>
                                    <p class="font-mono text-[#daaf2c]">{{ $selectedMemo->created_at->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <span class="block text-[9px] text-gray-500 uppercase font-bold">Heure</span>
                                    <p class="font-mono text-gray-300">{{ $selectedMemo->created_at->format('H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Colonne Droite DNA -->
                <!-- Colonne Droite DNA -->
                <div class="col-span-12 lg:col-span-8">
                    <div class="bg-white p-10 rounded-[3rem] shadow-xl border border-gray-100 min-h-full relative overflow-hidden">
                        
                        <!-- Titre -->
                        <h4 class="text-xs font-black text-[#707173] uppercase tracking-[0.3em] mb-12 flex items-center gap-3 relative z-10">
                            <i class="fas fa-project-diagram text-[#daaf2c]"></i> Arbre Généalogique
                        </h4>

                        <!-- Conteneur DNA -->
                        <div class="relative pl-4 md:pl-12">
                            
                            <!-- La Ligne Verticale -->
                            <div class="dna-line absolute left-[27px] md:left-[59px] top-0 bottom-0 z-0 opacity-30"></div>

                            <div class="space-y-8 relative z-10">
                                
                                <!-- 1. LE PARENT (L'Origine) -->
                                @if($selectedMemo->parent)
                                    <div class="relative group">
                                        <!-- Connecteur -->
                                        <div class="absolute -left-[34px] md:-left-[66px] top-8 w-8 h-[2px] bg-gray-300"></div>
                                        <div class="absolute -left-[38px] md:-left-[70px] top-6 w-4 h-4 rounded-full bg-gray-300 border-2 border-white"></div>

                                        <div class="bg-gray-50 border border-gray-200 p-4 rounded-3xl ml-4 cursor-pointer hover:bg-white hover:shadow-md transition-all opacity-70 hover:opacity-100"
                                             wire:click="openAuditDetails({{ $selectedMemo->parent->id }})">
                                            
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <span class="text-[9px] font-black text-white bg-gray-400 px-2 py-1 rounded-lg uppercase">Parent</span>
                                                    <span class="text-xs font-bold text-gray-600 uppercase truncate max-w-[200px]">{{ $selectedMemo->parent->object }}</span>
                                                </div>
                                                <i class="fas fa-level-up-alt text-gray-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- 2. LA GÉNÉRATION ACTUELLE (CORRECTION ERREUR FOREACH) -->
                                @php
                                    // Initialisation sécurisée par défaut
                                    $siblings = collect([$selectedMemo]);

                                    // Si un parent existe, on essaie de récupérer ses enfants
                                    if ($selectedMemo->parent) {
                                        // Utilisation de la relation children si elle existe, sinon collection vide
                                        $fetchedSiblings = $selectedMemo->parent->children ?? null;
                                        
                                        // Si la relation renvoie bien une collection non vide, on l'utilise
                                        if ($fetchedSiblings && $fetchedSiblings->count() > 0) {
                                            $siblings = $fetchedSiblings;
                                        }
                                    }
                                @endphp

                                @foreach($siblings as $sibling)
                                    @php $isMe = $sibling->id === $selectedMemo->id; @endphp

                                    <div class="relative {{ $isMe ? 'my-8' : '' }}">
                                        
                                        @if($isMe)
                                            <!-- C'EST LE DOSSIER ACTUEL (FOCUS) -->
                                            <div class="relative transform scale-105 origin-left z-20">
                                                <!-- Connecteur Gold -->
                                                <div class="absolute -left-[34px] md:-left-[66px] top-1/2 w-[34px] md:w-[66px] h-[2px] bg-[#daaf2c] shadow-[0_0_10px_#daaf2c]"></div>
                                                <div class="absolute -left-[39px] md:-left-[71px] top-[calc(50%-6px)] w-5 h-5 rounded-full bg-[#daaf2c] border-4 border-white shadow-lg animate-pulse"></div>

                                                <div class="bg-white border-2 border-[#daaf2c] p-8 rounded-[2rem] shadow-[0_10px_40px_-10px_rgba(218,175,44,0.2)]">
                                                    <div class="flex justify-between items-start mb-4">
                                                        <span class="text-[10px] font-black text-white bg-[#daaf2c] px-3 py-1 rounded-full uppercase tracking-widest shadow-md">
                                                            Dossier Actuel
                                                        </span>
                                                        <span class="text-xs font-mono font-bold text-gray-900">{{ $sibling->reference ?? 'REF-PENDING' }}</span>
                                                    </div>
                                                    
                                                    <h3 class="text-lg font-black text-gray-900 uppercase leading-tight mb-4">
                                                        {{ $sibling->object }}
                                                    </h3>

                                                    <div class="flex items-center gap-4 border-t border-gray-100 pt-4">
                                                        <div class="h-10 w-10 rounded-2xl bg-gray-900 text-[#daaf2c] flex items-center justify-center font-black text-sm shadow-lg">
                                                            {{ substr($sibling->user->last_name ?? '?', 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <p class="text-[10px] font-black text-gray-400 uppercase">Initié par</p>
                                                            <p class="text-xs font-bold text-gray-900 uppercase">{{ $sibling->user->last_name ?? 'Inconnu' }} {{ $sibling->user->first_name ?? '' }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- 3. LES ENFANTS DU DOSSIER ACTUEL (Sous-Branche) -->
                                            @php
                                                // Sécurité pour les enfants du mémo actuel
                                                $myChildren = $sibling->children ?? collect([]);
                                            @endphp

                                            @if($myChildren->count() > 0)
                                                <div class="relative ml-8 md:ml-12 mt-6 pl-8 border-l-2 border-dashed border-gray-200 space-y-4">
                                                    <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-gray-100 border border-gray-300 flex items-center justify-center">
                                                        <i class="fas fa-chevron-down text-[8px] text-gray-400"></i>
                                                    </div>
                                                    
                                                    @foreach($myChildren as $child)
                                                        <div class="relative group cursor-pointer" wire:click="openAuditDetails({{ $child->id }})">
                                                            <!-- Petite ligne horizontale -->
                                                            <div class="absolute -left-8 top-1/2 w-6 h-[2px] bg-gray-200 group-hover:bg-[#daaf2c] transition-colors"></div>
                                                            
                                                            <div class="bg-white p-4 rounded-2xl border border-gray-100 hover:border-[#daaf2c] hover:shadow-md transition-all">
                                                                <div class="flex justify-between items-center">
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                                                        <span class="text-[10px] font-bold text-gray-700 uppercase group-hover:text-[#daaf2c] transition-colors truncate max-w-[150px]">
                                                                            {{ $child->object }}
                                                                        </span>
                                                                    </div>
                                                                    <span class="text-[8px] font-mono text-gray-400">{{ $child->created_at->format('d/m') }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                        @else
                                            <!-- C'EST UN FRÈRE/SOEUR (Autre branche du parent) -->
                                            <div class="relative group ml-4 opacity-60 hover:opacity-100 transition-opacity">
                                                <!-- Connecteur -->
                                                <div class="absolute -left-[34px] md:-left-[66px] top-1/2 w-6 h-[1px] bg-gray-300 group-hover:bg-gray-400"></div>
                                                <div class="absolute -left-[37px] md:-left-[69px] top-[calc(50%-3px)] w-1.5 h-1.5 rounded-full bg-gray-300"></div>

                                                <div class="bg-gray-50 border border-gray-100 p-3 rounded-2xl cursor-pointer hover:bg-white hover:border-gray-300 hover:shadow-sm transition-all"
                                                     wire:click="openAuditDetails({{ $sibling->id }})">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-[8px] font-black text-gray-500 uppercase border border-gray-200 px-1.5 rounded">Autre flux</span>
                                                            <span class="text-[10px] font-bold text-gray-600 uppercase truncate max-w-[150px]">{{ $sibling->object }}</span>
                                                        </div>
                                                        <i class="fas fa-eye text-gray-300 group-hover:text-gray-500 text-xs"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endif

    <!-- MODALE PRINCIPALE (CREATE/EDIT) -->
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
                        @if(!in_array($activeTab, ['users', 'audit', 'workflow']))
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
                                        <label class="block text-[10px] font-black text-[#707173] uppercase mb-1">
                                            Structure Parente (Rattaché à)
                                        </label>
                                        
                                        <select wire:model="upper_id" class="w-full bg-gray-50 border-gray-200 rounded-xl p-3 text-xs font-bold uppercase focus:ring-[#daaf2c] focus:border-[#daaf2c]">
                                            <option value="">-- SÉLECTIONNER --</option>
                                            
                                            @foreach($structureParents as $p)
                                                <option value="{{ $p->id }}">
                                                    {{ $p->ref }} - {{ $p->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        
                                        @error('upper_id') 
                                            <span class="text-red-500 text-[9px] font-bold uppercase">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                @endif
                            </div>

                        @elseif($activeTab === 'users')
                            <!-- LOGIQUE UTILISATEUR COMPLÈTE -->
                            <div class="space-y-8 max-h-[65vh] overflow-y-auto custom-scrollbar pr-2">
                                
                                <div class="bg-gray-50 p-6 rounded-[2.5rem] border border-gray-200">
                                    <h4 class="text-[10px] font-black text-gray-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                                        <i class="fas fa-user-tie text-[#daaf2c]"></i> Informations Métiers
                                    </h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="col-span-2">
                                            <label class="block text-[9px] font-black text-[#707173] uppercase mb-1 ml-1">Poste / Fonction</label>
                                            <select wire:model="poste" class="w-full bg-white border-gray-200 rounded-xl p-3 text-xs font-bold uppercase">
                                                <option value="">-- CHOISIR --</option>
                                                @foreach(App\Enums\Poste::cases() as $posteCase)
                                                    <option value="{{ $posteCase->value }}">{{ $posteCase->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-[9px] font-black text-[#707173] uppercase mb-1 ml-1">Direction</label>
                                            <select wire:model.live="dir_id" class="w-full bg-white border-gray-200 rounded-xl p-3 text-xs font-bold uppercase"><option value="">-- DIRECTION --</option>@foreach($directions_list as $d) <option value="{{ $d->id }}">{{ $d->name }}</option> @endforeach</select>
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-black text-[#707173] uppercase mb-1 ml-1">Sous-Direction</label>
                                            <select wire:model.live="sd_id" class="w-full bg-white border-gray-200 rounded-xl p-3 text-xs font-bold uppercase"><option value="">-- SOUS-DIRECTION --</option>@foreach($sd_list as $sd) <option value="{{ $sd->id }}">{{ $sd->name }}</option> @endforeach</select>
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-black text-[#707173] uppercase mb-1 ml-1">Département</label>
                                            <select wire:model.live="dep_id" class="w-full bg-white border-gray-200 rounded-xl p-3 text-xs font-bold uppercase"><option value="">-- DÉPARTEMENT --</option>@foreach($dep_list as $dep) <option value="{{ $dep->id }}">{{ $dep->name }}</option> @endforeach</select>
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-black text-[#707173] uppercase mb-1 ml-1">Service</label>
                                            <select wire:model="serv_id" class="w-full bg-white border-gray-200 rounded-xl p-3 text-xs font-bold uppercase"><option value="">-- SERVICE --</option>@foreach($serv_list as $s) <option value="{{ $s->id }}">{{ $s->name }}</option> @endforeach</select>
                                        </div>

                                        <div class="col-span-2">
                                            <label class="block text-[9px] font-black text-[#707173] uppercase mb-1 ml-1">Manager (N+1)</label>
                                            <select wire:model="manager_id" class="w-full bg-white border-gray-200 rounded-xl p-3 text-xs font-bold uppercase">
                                                <option value="">-- AUCUN MANAGER --</option> <!-- Valeur vide pour null -->
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

                                <!-- SECTION REMPLACEMENT -->
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
                                        <div class="col-span-2 text-right">
                                            <button wire:click.prevent="addReplacement" class="bg-gray-900 text-[#daaf2c] px-6 py-2 rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-[#daaf2c] hover:text-black">Déléguer</button>
                                        </div>
                                    </div>

                                   <!-- Historique Actif -->
                                    <div class="mt-6 border-t pt-4">
                                        <h4 class="text-[10px] font-black text-[#707173] uppercase tracking-widest mb-4">Historique Actif</h4>
                                        
                                        @foreach($userReplacements as $rep)
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl mb-2 border border-gray-100">
                                                <div>
                                                    <!-- Ici on utilise $rep->substitute qui vient de la relation définie dans le Modèle -->
                                                    <p class="text-[10px] font-bold text-gray-900 uppercase underline decoration-[#daaf2c]">
                                                        {{ $rep->substitute->last_name ?? 'Inconnu' }} {{ $rep->substitute->first_name ?? '' }}
                                                    </p>
                                                    <p class="text-[9px] text-[#707173] font-mono">
                                                        {{ $rep->date_begin_replace }} / {{ $rep->date_end_replace }}
                                                    </p>
                                                </div>
                                                <button wire:click="removeReplacement({{ $rep->id }})" class="text-red-400 hover:text-red-600 p-2">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
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

    <!-- MODAL DE MOTIF DE BLOCAGE -->
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

    <!-- MODAL DE CONFIRMATION DE SUPPRESSION -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[400] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/90 backdrop-blur-md transition-opacity" wire:click="$set('showDeleteModal', false)"></div>
                
                <div class="relative bg-white rounded-[2.5rem] shadow-2xl max-w-md w-full p-10 border-t-8 border-red-600 overflow-hidden text-center transform transition-all animate-in zoom-in duration-200">
                    <div class="h-20 w-20 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    
                    <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter mb-2">Confirmation Requise</h3>
                    <p class="text-sm text-[#707173] font-medium mb-8 italic">
                        Êtes-vous sûr de vouloir supprimer définitivement la structure <br>
                        <span class="text-red-600 font-black uppercase not-italic">"{{ $structureToDeleteName }}"</span> ?
                    </p>

                    <div class="flex flex-col gap-3">
                        <button wire:click="deleteStructure" 
                                class="w-full py-4 bg-red-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-red-700 transition-all shadow-lg flex items-center justify-center gap-2">
                            <i class="fas fa-check-circle"></i> Confirmer la suppression
                        </button>
                        <button wire:click="$set('showDeleteModal', false)" 
                                class="w-full py-4 bg-gray-100 text-[#707173] rounded-2xl font-black uppercase tracking-widest hover:bg-gray-200 transition-all">
                            Annuler l'opération
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODALE D'INTERVENTION WORKFLOW AVEC RÈGLE INTERACTIVE (TIMELINE) -->
    @if($showWorkflowModal && $selectedMemo)
        <div class="fixed inset-0 z-[500] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-gray-900/95 backdrop-blur-sm transition-opacity" wire:click="$set('showWorkflowModal', false)"></div>
                
                <div class="relative bg-white rounded-[2.5rem] shadow-2xl max-w-5xl w-full p-8 border-t-8 border-[#daaf2c] overflow-hidden transform transition-all animate-in zoom-in duration-200">
                    
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 uppercase tracking-widest">Contrôle du Circuit</h3>
                            <p class="text-xs text-[#707173] font-mono mt-1">
                                REF: <span class="text-black font-bold">{{ $selectedMemo->reference ?? 'PENDING' }}</span>
                            </p>
                        </div>
                        <button wire:click="$set('showWorkflowModal', false)" class="text-gray-400 hover:text-red-500 transition-colors">
                            <i class="fas fa-times fa-2x"></i>
                        </button>
                    </div>

                    <!-- 1. LA RÈGLE INTERACTIVE (TIMELINE HORIZONTALE) -->
                    @if(count($circuitUsers) > 0)
                        <div class="mb-10 p-8 bg-gray-50 rounded-3xl border border-gray-100 shadow-inner relative">
                            <h4 class="text-[10px] font-black text-[#707173] uppercase tracking-[0.2em] mb-12 text-center">
                                Ligne de Vie (Clic = {{ strtolower($selectedMemo->workflow_direction) === 'entrant' ? 'Ajouter/Retirer' : 'Transférer' }} la main)
                            </h4>
                            
                            <!-- Conteneur scrollable horizontalement -->
                            <!-- CORRECTION : on passe de pt-4 à pt-32 pour laisser de la place en haut pour la popup -->
                            <div class="overflow-x-auto custom-scrollbar pb-8 pt-32 px-4"> 
                                <div class="flex items-center justify-start relative min-w-[750px] px-6 gap-8">
                                    
                                    <!-- LA LIGNE (Règle) -->
                                    <div class="absolute left-10 right-20 top-6 h-1 bg-gray-200 rounded-full z-0"></div>
                                    
                                    @foreach($circuitUsers as $index => $u)
                                        @php
                                            // (Votre logique PHP existante ici, inchangée)
                                            $currents = $selectedMemo->current_holders ?? [];
                                            $treatments = $selectedMemo->treatment_holders ?? [];
                                            $previous = $selectedMemo->previous_holders ?? [];
                                            
                                            if(is_string($currents)) $currents = json_decode($currents, true) ?? [];
                                            if(is_string($treatments)) $treatments = json_decode($treatments, true) ?? [];
                                            if(is_string($previous)) $previous = json_decode($previous, true) ?? [];

                                            $isTreater = in_array($u->id, $treatments);
                                            $isHolder = in_array($u->id, $currents) && !$isTreater;
                                            $isPast = in_array($u->id, $previous) && !$isHolder && !$isTreater;
                                            
                                            $canRemove = ($isTreater || $isHolder); 
                                        @endphp

                                        <!-- ITEM -->
                                        <div class="relative z-10 flex flex-col items-center flex-shrink-0 group w-24">
                                            
                                            <!-- BOUTON SUPPRIMER (X) -->
                                            @if($canRemove)
                                                <button wire:confirm="Retirer cet utilisateur du circuit actuel ?"
                                                        wire:click="removeUserFromLoop({{ $u->id }})"
                                                        class="absolute -top-2 -right-2 z-30 bg-red-500 text-white w-5 h-5 rounded-full flex items-center justify-center shadow-md transform scale-0 group-hover:scale-100 transition-all duration-200 hover:bg-red-700"
                                                        title="Retirer du circuit">
                                                    <i class="fas fa-times text-[10px]"></i>
                                                </button>
                                            @endif

                                            <!-- LE CERCLE -->
                                            <div class="cursor-pointer transition-all duration-300 transform group-hover:scale-110
                                                w-12 h-12 rounded-full flex items-center justify-center border-4 shadow-lg mb-2 bg-white relative
                                                {{ $isTreater ? 'border-[#daaf2c] bg-yellow-50 scale-110 ring-4 ring-yellow-100 z-20' : '' }}
                                                {{ $isHolder ? 'border-blue-400 bg-blue-50' : '' }}
                                                {{ $isPast ? 'border-gray-400 bg-gray-400 text-white' : '' }}
                                                {{ !$isTreater && !$isHolder && !$isPast ? 'border-gray-200 text-gray-300' : '' }}"
                                                wire:click="jumpToStep({{ $u->id }})"
                                                title="{{ $isTreater ? 'Révoquer la main' : 'Donner la main' }}">
                                                
                                                @if($isTreater)
                                                    <i class="fas fa-pen-nib text-[#daaf2c] text-sm animate-bounce"></i>
                                                @elseif($isHolder)
                                                    <i class="fas fa-eye text-blue-500 text-sm"></i>
                                                @elseif($isPast)
                                                    <i class="fas fa-check font-bold text-white text-sm"></i>
                                                @else
                                                    <span class="text-[10px] font-black">{{ $index + 1 }}</span>
                                                @endif
                                            </div>

                                            <!-- INFO TEXTE -->
                                            <div class="text-center w-full px-1">
                                                <p class="text-[10px] font-black uppercase text-gray-900 truncate">{{ $u->last_name }}</p>
                                                <p class="text-[8px] font-bold text-[#daaf2c] uppercase truncate mt-0.5">{{ $u->poste ?? 'Agent' }}</p>
                                                <p class="text-[8px] text-gray-400 uppercase truncate">{{ $u->entity->ref ?? 'N/A' }}</p>
                                            </div>
                                            
                                            <!-- BADGE ÉTAT -->
                                            @if($isTreater)
                                                <span class="absolute -top-5 bg-[#daaf2c] text-white text-[7px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest shadow-md">MAIN</span>
                                            @endif
                                        </div>
                                    @endforeach

                                    <!-- BOUTON AJOUTER (+) -->
                                    <div class="relative z-10 flex flex-col items-center flex-shrink-0 ml-4">
                                        @if($isAddingToTimeline)
                                            <!-- Mini Formulaire d'ajout -->
                                            <!-- CORRECTION : Positionnement ajusté (right-0) pour ne pas sortir à droite -->
                                            <!-- CORRECTION : z-50 pour passer au dessus de la ligne -->
                                            <div class="absolute bottom-16 right-0 w-56 bg-white p-4 rounded-xl shadow-2xl border border-gray-200 animate-in fade-in slide-in-from-bottom-2 z-50">
                                                <label class="text-[9px] font-black text-gray-500 uppercase mb-2 block">Ajouter un agent</label>
                                                <select wire:model="userToAddId" class="w-full text-[10px] border-gray-200 rounded-lg mb-3 font-bold uppercase focus:ring-[#daaf2c] focus:border-[#daaf2c]">
                                                    <option value="">-- CHOISIR --</option>
                                                    @foreach(\App\Models\User::where('is_active', true)->orderBy('last_name')->get() as $nu)
                                                        @if(!in_array($nu->id, $circuitUsers->pluck('id')->toArray()))
                                                            <option value="{{ $nu->id }}">{{ $nu->last_name }} {{ $nu->first_name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <div class="flex gap-2">
                                                    <button wire:click="addUserToLoop" class="flex-1 bg-[#daaf2c] text-white py-2 rounded-lg text-[9px] font-black uppercase hover:bg-black transition-colors shadow-sm">OK</button>
                                                    <button wire:click="$set('isAddingToTimeline', false)" class="flex-1 bg-gray-100 text-gray-500 py-2 rounded-lg text-[9px] font-black uppercase hover:bg-gray-200 transition-colors">Annuler</button>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Le Bouton Rond (+) -->
                                        <button wire:click="$toggle('isAddingToTimeline')" 
                                                class="w-10 h-10 rounded-full bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 hover:border-[#daaf2c] hover:text-[#daaf2c] hover:bg-white transition-all shadow-sm group"
                                                title="Ajouter un participant">
                                            <i class="fas fa-plus font-black group-hover:rotate-90 transition-transform duration-300"></i>
                                        </button>
                                        <span class="text-[8px] font-black text-gray-300 uppercase mt-2 group-hover:text-[#daaf2c]">Ajouter</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Message si vide -->
                        <div class="mb-8 p-6 bg-yellow-50 text-[#daaf2c] text-center rounded-xl text-xs font-bold border border-yellow-200 uppercase flex items-center justify-center gap-2">
                            <i class="fas fa-exclamation-triangle"></i> Aucun historique ou circuit disponible.
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t border-gray-100 pt-8">
                        <!-- Colonne Info -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest">Résumé du Dossier</h4>
                            <div class="bg-gray-50 p-6 rounded-2xl space-y-3 border border-gray-100">
                                <div class="flex justify-between text-xs items-center">
                                    <span class="text-gray-500 font-bold uppercase">Objet</span>
                                    <span class="font-black text-gray-900 text-right truncate w-40" title="{{ $selectedMemo->object }}">{{ $selectedMemo->object }}</span>
                                </div>
                                <div class="flex justify-between text-xs items-center">
                                    <span class="text-gray-500 font-bold uppercase">Création</span>
                                    <span class="font-bold text-gray-900">{{ $selectedMemo->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between text-xs items-center">
                                    <span class="text-gray-500 font-bold uppercase">Flux</span>
                                    <span class="px-2 py-1 bg-white rounded text-[10px] font-black text-gray-900 border shadow-sm">{{ $selectedMemo->workflow_direction }}</span>
                                </div>
                            </div>
                            <div class="text-[10px] text-gray-400 italic flex items-start gap-2">
                                <i class="fas fa-info-circle mt-0.5"></i>
                                <p>Cliquez sur un nœud de la règle pour effectuer un "Saut d'étape". Le système notifiera l'agent concerné.</p>
                            </div>
                        </div>

                        <!-- Colonne Assignation Manuelle -->
                        <div class="space-y-4 md:border-l md:border-gray-100 md:pl-8">
                            <h4 class="text-xs font-black text-red-600 uppercase flex items-center gap-2 tracking-widest">
                                <i class="fas fa-tools"></i> Intervention Manuelle
                            </h4>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-black text-[#707173] uppercase mb-1">Cible (Hors Timeline)</label>
                                    <select wire:model="target_user_id" class="w-full bg-white border-gray-200 rounded-xl p-3 text-xs font-bold uppercase focus:ring-red-500 focus:border-red-500 shadow-sm">
                                        <option value="">-- CHOISIR UN AGENT --</option>
                                        @foreach(\App\Models\User::where('is_active', true)->orderBy('last_name')->get() as $u)
                                            <option value="{{ $u->id }}">{{ $u->last_name }} {{ $u->first_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('target_user_id') <span class="text-red-500 text-[9px] font-bold">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-[9px] font-black text-[#707173] uppercase mb-1">Motif de l'intervention</label>
                                    <textarea wire:model="transfer_reason" rows="2" class="w-full bg-white border-gray-200 rounded-xl p-3 text-xs font-medium focus:ring-red-500 focus:border-red-500 shadow-sm" placeholder="Raison obligatoire..."></textarea>
                                    @error('transfer_reason') <span class="text-red-500 text-[9px] font-bold">{{ $message }}</span> @enderror
                                </div>

                                <button wire:click="forceWorkflowTransfer" class="w-full py-4 bg-red-600 text-white rounded-xl font-black uppercase tracking-widest hover:bg-red-700 transition-all shadow-lg text-[10px] flex justify-center gap-2 items-center">
                                    <i class="fas fa-exchange-alt"></i> Exécuter le Transfert
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
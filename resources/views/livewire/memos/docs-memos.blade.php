<div class="min-h-screen bg-gray-50 py-8 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if($isViewingPdf)
                    <!-- ========================================== -->
                    <!-- VUE 3 : APERÇU RÉEL PDF (DOMPDF)           -->
                    <!-- ========================================== -->
                    <div class="animate-fade-in">
                        <!-- BARRE D'ACTIONS (Header style original) -->
                        <div class="mb-8 bg-white border border-gray-100 rounded-xl shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-all duration-300">
                            <button wire:click="closePdfView" type="button" class="group flex items-center text-gray-500 hover:text-black transition-colors focus:outline-none">
                                <div class="mr-3 h-10 w-10 rounded-full bg-gray-100 group-hover:bg-[#daaf2c]/20 group-hover:text-[#daaf2c] flex items-center justify-center transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                </div>
                                <div class="flex flex-col items-start text-left">
                                    <span class="font-bold text-base text-black">Retour</span>
                                    <span class="text-xs font-normal text-gray-400">Revenir à la liste</span>
                                </div>
                            </button>
                            
                            <div class="flex items-center justify-end space-x-3 w-full sm:w-auto">
                                <button wire:click="downloadMemoPDF" type="button" 
                                    class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-lg shadow-md text-white transform hover:-translate-y-0.5 transition-all duration-200"
                                    style="background-color: #ef4444;">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Télécharger PDF
                                </button>
                            </div>
                        </div>

                        <!-- ZONE VISIONNEUSE (Style Papier) -->
                        <div class="bg-gray-200 rounded-lg shadow-inner p-4 md:p-8 flex justify-center min-h-[80vh]">
                            <div class="w-full max-w-5xl bg-white shadow-2xl">
                                @if($pdfBase64)
                                    <iframe 
                                        src="data:application/pdf;base64,{{ $pdfBase64 }}#view=FitH" 
                                        class="w-full h-[100vh] border-none">
                                    </iframe>
                                @else
                                    <div class="flex flex-col items-center justify-center py-20">
                                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#daaf2c]"></div>
                                        <p class="mt-4 text-gray-500 font-bold">Génération du rendu final DomPDF...</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                @elseif(!$isEditing)

                <!-- EN-TÊTE & RECHERCHE -->
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Mes Mémos Envoyés</h2>
                        <p class="text-sm text-gray-500">Gérez vos mémos envoyés et vérifiez leur état d'avancement..</p>
                    </div>

                    <div class="relative w-full md:w-96">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input 
                            wire:model.live.debounce.300ms="search" 
                            type="text" 
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm transition duration-150 ease-in-out shadow-sm" 
                            placeholder="Rechercher par objet, concerné..."
                        >
                        <!-- Spinner de chargement -->
                        <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="animate-spin h-5 w-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- TABLEAU MODERNE -->
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">
                                <tr>
                                    <!-- Date : largeur fixe petite -->
                                    <th scope="col" class="w-24 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    
                                    <!-- Objet : prend tout l'espace restant -->
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Objet & Concerne</th>
                                    
                                    <!-- Destinataires : largeur moyenne -->
                                    <th scope="col" class="w-48 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destinataires</th>
                                    
                                    <!-- Statut : largeur moyenne -->
                                    <th scope="col" class="w-32 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut Workflow</th>

                                    <!-- NOUVELLE COLONNE : STATUT DE TRAITEMENT -->
                                    <th scope="col" class="w-48 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut de traitement</th>
                                    
                                    <!-- PJ : tout petit -->
                                    <th scope="col" class="w-16 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P.J.</th>
                                    
                                    <!-- Actions : largeur fixe et padding réduit -->
                                    <th scope="col" class="w-32 px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($memos as $memo)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150 group">
                                        
                                        <!-- 1. DATE -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-900">{{ $memo->created_at->format('d/m/Y') }}</span>
                                                <span class="text-xs">{{ $memo->created_at->format('H:i') }}</span>
                                            </div>
                                        </td>

                                        <!-- 2. OBJET -->
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col max-w-xs sm:max-w-sm md:max-w-md">
                                                <span class="text-sm font-bold text-gray-800 truncate" title="{{ $memo->object }}">{{ $memo->object }}</span>
                                                <span class="text-xs text-gray-500 truncate mt-1">Concerne: {{ $memo->concern }}</span>
                                            </div>
                                        </td>

                                    <!-- 3. DESTINATAIRES (Badges avec Action) -->
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                @php 
                                                    // Récupération de la relation chargée dans le contrôleur
                                                    $destinataires = $memo->destinataires; 
                                                    $count = $destinataires->count();
                                                    $displayLimit = 3; // Augmenté un peu pour la lisibilité
                                                @endphp

                                                @if($count > 0)
                                                    @foreach($destinataires->take($displayLimit) as $dest)
                                                        @php
                                                            // Logique de couleur selon l'action
                                                            $isActionRequired = Str::contains(Str::lower($dest->action), 'nécessaire');
                                                            $badgeClasses = $isActionRequired 
                                                                ? 'bg-orange-100 text-orange-800 border border-orange-200' 
                                                                : 'bg-blue-100 text-blue-800 border border-blue-200';
                                                        @endphp

                                                        <div class="inline-flex flex-col items-start justify-center px-2.5 py-1 rounded-md text-xs font-medium {{ $badgeClasses }}" 
                                                            title="Action attendue : {{ $dest->action }}">
                                                            
                                                            <!-- Nom de l'entité (REF ou Nom tronqué) -->
                                                            <span class="font-bold">
                                                                {{ $dest->entity->ref ?? Str::limit($dest->entity->name, 15) }}
                                                            </span>
                                                            
                                                            <!-- L'action affichée en tout petit en dessous -->
                                                            <span class="text-[10px] opacity-80 leading-tight">
                                                                {{ Str::limit($dest->action, 20) }}
                                                            </span>
                                                        </div>
                                                    @endforeach

                                                    @if($count > $displayLimit)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200" title="Et {{ $count - $displayLimit }} autres...">
                                                            +{{ $count - $displayLimit }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-gray-400 italic flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        Non assigné
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- COLONNE DES STATUS -->
                                         <!-- COLONNE STATUT (Cherche le motif dans l'historique) -->
                                        <td class="px-6 py-4" x-data="{ openReason: false }">
                                            <div class="flex flex-col items-start gap-1">
                                                @php
                                                    $statusRaw = Str::lower($memo->status);
                                                    
                                                    // On cherche dans la relation 'historiques' le dernier commentaire qui n'est pas 'R.A.S'
                                                    $lastHistoryWithComment = $memo->historiques->first(function($h) {
                                                        return !empty($h->workflow_comment) && !Str::contains(Str::upper($h->workflow_comment), 'R.A.S');
                                                    });

                                                    $hasComment = (bool)$lastHistoryWithComment;
                                                    $displayComment = $hasComment ? $lastHistoryWithComment->workflow_comment : '';
                                                    
                                                    // Couleurs des badges
                                                    $colors = [
                                                        'envoyer'   => 'bg-green-100 text-green-800 border-green-200',
                                                        'retourner' => 'bg-red-100 text-red-800 border-red-200',
                                                        'transmis'  => 'bg-blue-100 text-blue-800 border-blue-200',
                                                        'traiter'   => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                                    ];
                                                    $colorClass = $colors[$statusRaw] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                                    $label = ucfirst($memo->status);
                                                @endphp

                                                <!-- 1. LE BADGE (CLIQUABLE s'il y a un commentaire dans l'historique) -->
                                                <button 
                                                    @if($hasComment) @click="openReason = true" @endif
                                                    type="button"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $colorClass }} {{ $hasComment ? 'cursor-pointer hover:shadow-md transition-shadow' : 'cursor-default' }}"
                                                    title="{{ $hasComment ? 'Cliquez pour voir la note de : ' . $lastHistoryWithComment->user->first_name : '' }}"
                                                >
                                                    @if($statusRaw == 'retourner')
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    @endif

                                                    {{ $label }}

                                                    @if($hasComment)
                                                        <svg class="w-3 h-3 ml-1.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                                    @endif
                                                </button>

                                                <!-- 2. LE MODAL (Affiche le commentaire de l'historique) -->
                                                @if($hasComment)
                                                    <div 
                                                        x-show="openReason" 
                                                        style="display: none;"
                                                        class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm"
                                                        @click.self="openReason = false"
                                                        x-transition:enter="transition ease-out duration-200"
                                                        x-transition:enter-start="opacity-0 scale-95"
                                                        x-transition:enter-end="opacity-100 scale-100">
                                                        
                                                        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden border border-gray-100">
                                                            <!-- Header -->
                                                            <div class="{{ $statusRaw == 'retourner' ? 'bg-red-50 border-red-100' : 'bg-gray-50 border-gray-100' }} px-4 py-3 border-b flex justify-between items-center">
                                                                <h3 class="text-xs font-bold {{ $statusRaw == 'retourner' ? 'text-red-800' : 'text-gray-700' }} flex flex-col">
                                                                    <span>Note de workflow</span>
                                                                    <span class="text-[10px] font-normal opacity-70">Par : {{ $lastHistoryWithComment->user->first_name }} {{ $lastHistoryWithComment->user->last_name }}</span>
                                                                </h3>
                                                                <button @click="openReason = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                                </button>
                                                            </div>

                                                            <!-- Contenu (Le commentaire de la table Historiques) -->
                                                            <div class="p-5">
                                                                <div class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed italic">
                                                                    "{{ $displayComment }}"
                                                                </div>
                                                                <div class="mt-4 text-[10px] text-gray-400 text-right">
                                                                    Le {{ $lastHistoryWithComment->created_at->format('d/m/Y à H:i') }}
                                                                </div>
                                                            </div>

                                                            <!-- Footer -->
                                                            <div class="bg-gray-50 px-4 py-3 text-right">
                                                                <button @click="openReason = false" type="button" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                                    Fermer
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        

                                        <!-- Insérez ceci après le <td> du Statut & Note -->
                                        <td class="px-6 py-4">
                                            @if(Str::lower($memo->status) == 'transmis')
                                                <div class="flex flex-col gap-2">
                                                    @foreach($memo->destinataires as $dest)
                                                        <div class="flex items-center justify-between text-[10px] border-b border-gray-50 last:border-0 pb-1">
                                                            <!-- Nom de l'entité (REF) -->
                                                            <span class="font-bold text-gray-600 truncate w-20" title="{{ $dest->entity->name }}">
                                                                {{ $dest->entity->ref ?? Str::limit($dest->entity->name, 10) }} :
                                                            </span>

                                                            <!-- Badge de statut de traitement -->
                                                            @php
                                                                $pStatus = $dest->processing_status;
                                                                $pClasses = match($pStatus) {
                                                                    'traite' => 'bg-green-100 text-green-700 border-green-200',
                                                                    'decision_prise' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                                    default => 'bg-amber-100 text-amber-700 border-amber-200', // en_cours
                                                                };
                                                                $pLabel = match($pStatus) {
                                                                    'traiter' => 'Traité',
                                                                    'decision_prise' => 'Décidé/Refusé',
                                                                    'repondu' => 'repondu',
                                                                    default => 'En cours',
                                                                };
                                                            @endphp
                                                            
                                                            <span class="px-1.5 py-0.5 rounded border {{ $pClasses }} font-medium">
                                                                {{ $pLabel }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <!-- Affichage si le mémo n'est pas encore transmis -->
                                                <div class="flex items-center gap-1 text-gray-300 italic text-[10px]">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Attente transmission
                                                </div>
                                            @endif
                                        </td>

                                        <!-- COLONNE PIÈCES JOINTES -->
                                        <td class="px-6 py-4 whitespace-nowrap" x-data="{ openFiles: false }">
                                            @php
                                                $pj = $memo->pieces_jointes;
                                                if (is_string($pj)) { $pj = json_decode($pj, true); }
                                                $pj = is_array($pj) ? $pj : [];
                                                $countPj = count($pj);
                                            @endphp

                                            @if($countPj > 0)
                                                <!-- 1. Le bouton Trombone (Déclencheur) -->
                                                <button 
                                                    @click="openFiles = true" 
                                                    type="button"
                                                    class="flex items-center space-x-1 text-gray-600 hover:text-blue-600 transition-colors focus:outline-none">
                                                    
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                    <span class="text-sm font-bold">{{ $countPj }}</span>
                                                </button>

                                                <!-- 2. Le Mini-Modal (S'affiche par dessus TOUT le site) -->
                                                <div 
                                                    x-show="openFiles" 
                                                    style="display: none;"
                                                    class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm"
                                                    @click.self="openFiles = false"
                                                    x-transition:enter="transition ease-out duration-200"
                                                    x-transition:enter-start="opacity-0"
                                                    x-transition:enter-end="opacity-100"
                                                    x-transition:leave="transition ease-in duration-150"
                                                    x-transition:leave-start="opacity-100"
                                                    x-transition:leave-end="opacity-0">
                                                    
                                                    <!-- Contenu du Popup -->
                                                    <div class="bg-white rounded-lg shadow-2xl w-80 max-w-sm mx-4 overflow-hidden border border-gray-100">
                                                        
                                                        <!-- En-tête Popup -->
                                                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                                                            <h3 class="text-sm font-bold text-gray-700">Pièces Jointes ({{ $countPj }})</h3>
                                                            <button @click="openFiles = false" class="text-gray-400 hover:text-gray-600">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                            </button>
                                                        </div>

                                                        <!-- Liste des fichiers (Scrollable) -->
                                                        <div class="max-h-64 overflow-y-auto p-2">
                                                            <ul class="space-y-1">
                                                                @foreach($pj as $file)
                                                                    @php
                                                                        $filePath = is_string($file) ? $file : ($file['path'] ?? $file['url'] ?? $file[0] ?? '');
                                                                        $fileName = is_string($file) ? basename($file) : ($file['original_name'] ?? $file['name'] ?? basename($filePath));
                                                                    @endphp

                                                                    @if($filePath)
                                                                        <li>
                                                                            <a href="{{ Storage::url($filePath) }}" target="_blank" class="flex items-center p-2 hover:bg-blue-50 rounded-md text-sm text-gray-700 transition-colors group">
                                                                                <div class="bg-blue-100 p-1.5 rounded-md mr-3 text-blue-600 group-hover:bg-blue-200">
                                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                                </div>
                                                                                <div class="flex-1 min-w-0">
                                                                                    <p class="truncate font-medium">{{ Str::limit($fileName, 30) }}</p>
                                                                                    <p class="text-[10px] text-gray-400">Cliquez pour ouvrir</p>
                                                                                </div>
                                                                                <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                                            </a>
                                                                        </li>
                                                                    @endif
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-300 text-xs">-</span>
                                            @endif
                                        </td>

                                       <!-- 4. ACTIONS (Boutons Icones) -->
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <div class="flex items-center justify-center space-x-2">
                                                
                                                <!-- 1. APERÇU (Bleu) -->
                                                <button wire:click="viewMemo({{ $memo->id }})" 
                                                        class="group p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 transition-all duration-200 focus:ring-2 focus:ring-blue-500/20" 
                                                        title="Aperçu">
                                                    <svg class="w-5 h-5 transform group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </button>

                                                <!-- 2. HISTORIQUE (Violet) -->
                                                <button wire:click="viewHistory({{ $memo->id }})" 
                                                        class="group p-2 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 hover:text-purple-700 transition-all duration-200 focus:ring-2 focus:ring-purple-500/20" 
                                                        title="Historique & Workflow">
                                                    <svg class="w-5 h-5 transform group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </button>

                                                @if($memo->status == 'retourner')
                                                
                                                    <!-- 3. TRANSMETTRE (Émeraude / Vert) -->
                                                    <button wire:click="assignMemo({{ $memo->id }})" 
                                                            class="group p-2 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 transition-all duration-200 focus:ring-2 focus:ring-emerald-500/20" 
                                                            title="Attribuer & Envoyer">
                                                        <svg class="w-5 h-5 transform group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                                        </svg>
                                                    </button>

                                                    <!-- 4. MODIFIER (Ambre / Jaune) -->
                                                    <button wire:click="editMemo({{ $memo->id }})" 
                                                            class="group p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 transition-all duration-200 focus:ring-2 focus:ring-amber-500/20" 
                                                            title="Modifier">
                                                        <svg class="w-5 h-5 transform group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                        </svg>
                                                    </button>

                                                @endif

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <!-- Icône Avion en papier (Symbole d'envoi) -->
                                                <div class="bg-yellow-50 p-4 rounded-full mb-4">
                                                    <svg class="h-12 w-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                                    </svg>
                                                </div>
                                                
                                                <p class="text-lg font-bold text-gray-900">Aucun Mémo Envoyé</p>
                                                <p class="text-sm text-gray-500 max-w-xs mx-auto mt-1">
                                                    Vous n'avez pas encore rediger de mémo. Les mémos envoyés aux autres entités s'afficheront ici.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $memos->links() }} 
                    </div>
                </div>

            </div>
        
        @else
        
            <!-- ========================================== -->
            <!-- VUE : ÉDITION DU MÉMO (Style Papier A4) -->
            <!-- ========================================== -->
            <div class="max-w-5xl mx-auto animate-fade-in-up">
                
                <!-- Barre d'actions supérieure -->
                <div class="mb-8 bg-white border border-gray-100 rounded-xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <button wire:click="cancelEdit" type="button" class="group flex items-center text-gray-500 hover:text-black transition-colors">
                        <div class="mr-3 h-10 w-10 rounded-full bg-gray-100 group-hover:bg-yellow-100 group-hover:text-yellow-700 flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        </div>
                        <div class="flex flex-col items-start text-left">
                            <span class="font-bold text-base text-black">Retour</span>
                            <span class="text-xs text-gray-400">Annuler les modifications</span>
                        </div>
                    </button>
                    
                    <div class="flex items-center space-x-3">
                        <button wire:click="cancelEdit" type="button" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Annuler
                        </button>
                        
                        <button wire:click="save" wire:loading.attr="disabled" type="button" 
                            class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-lg shadow-md text-white bg-yellow-500 hover:bg-yellow-600 transition-all">
                            <span wire:loading.remove wire:target="save">Mettre à jour le dossier</span>
                            <span wire:loading wire:target="save">Traitement...</span>
                        </button>
                    </div>
                </div>

                <!-- LE FORMULAIRE STYLE MÉMORANDUM -->
                <div class="bg-white rounded-lg shadow-2xl overflow-hidden border border-gray-200">
                    
                    <!-- Entête Papier -->
                    <div class="px-8 py-6 flex justify-between items-center bg-gray-900 text-white border-b-4 border-yellow-500">
                        <div>
                            <h2 class="text-2xl font-bold uppercase" style="font-family: 'Times New Roman', serif;">Mémorandum</h2>
                            <p class="text-xs font-bold tracking-widest mt-1 text-yellow-500">MODE ÉDITION - RÉF: {{ $memo_id }}</p>
                        </div>
                        <div class="text-right opacity-80 text-sm">
                            Date initiale : {{ $date }}
                        </div>
                    </div>

                    <div class="p-8 md:p-12 space-y-10">
                        <!-- Concern & Objet -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div class="space-y-1">
                                <label class="block text-xs font-bold uppercase text-gray-400">Pour (Concerne)</label>
                                <input type="text" wire:model="concern" class="w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-black focus:ring-0 focus:border-yellow-500 sm:text-lg transition-colors">
                                @error('concern') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="block text-xs font-bold uppercase text-gray-400">Objet</label>
                                <input type="text" wire:model="object" class="w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-black font-bold focus:ring-0 focus:border-yellow-500 sm:text-lg transition-colors">
                                @error('object') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Liste de Distribution -->
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <h3 class="text-sm font-bold uppercase mb-4 text-gray-700 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                Liste de Distribution
                            </h3>
                            <div class="flex flex-col md:flex-row gap-3 mb-4">
                                <select wire:model="newRecipientEntity" class="flex-1 rounded-md border-gray-300 text-sm focus:ring-yellow-500">
                                    <option value="">-- Choisir Entité --</option>
                                    @foreach($entities as $entity) <option value="{{ $entity->id }}">{{ $entity->name }}</option> @endforeach
                                </select>
                                <select wire:model="newRecipientAction" class="flex-1 rounded-md border-gray-300 text-sm focus:ring-yellow-500">
                                    <option value="">-- Choisir Action --</option>
                                    @foreach($actionsList as $act) <option value="{{ $act }}">{{ $act }}</option> @endforeach
                                </select>
                                <button wire:click="addRecipient" type="button" class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm font-bold">Ajouter</button>
                            </div>

                            @if(count($recipients) > 0)
                                <div class="bg-white border border-gray-200 rounded-md overflow-hidden shadow-sm">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($recipients as $idx => $r)
                                                <tr>
                                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $r['entity_name'] }}</td>
                                                    <td class="px-4 py-2 text-sm text-yellow-700 font-bold italic">{{ $r['action'] }}</td>
                                                    <td class="px-4 py-2 text-right">
                                                        <button wire:click="removeRecipient({{ $idx }})" class="text-gray-400 hover:text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>


                        <!-- ÉDITEUR QUILL (Type Word) -->
                        <div class="pt-2">
                            <!-- Label avec info utilisateur -->
                            <label class="block text-xs font-bold uppercase tracking-wide mb-3 flex justify-between items-center text-gray-500">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Corps du Document
                                </span>
                                <span class="text-[10px] font-normal normal-case italic opacity-60">
                                    Les lignes rouges indiquent les sauts de page (Format A4)
                                </span>
                            </label>

                            <div wire:ignore 
                                class="flex flex-col items-center bg-gray-100 rounded-xl p-4 border border-gray-200 shadow-inner"
                                x-data="{
                                    content: @entangle('content'),
                                    quill: null,
                                    initQuill() {
                                        // Enregistrement des Polices
                                        const Font = Quill.import('formats/font');
                                        Font.whitelist = ['helvetica', 'arial', 'roboto', 'tahoma', 'timesnewroman', 'georgia', 'inter'];
                                        Quill.register(Font, true);

                                        // Configuration Tailles
                                        const Size = Quill.import('attributors/style/size');
                                        Size.whitelist = ['10px', '12px', '14px', '16px', '18px', '20px', '24px'];
                                        Quill.register(Size, true);

                                        this.quill = new Quill(this.$refs.quillEditor, {
                                            theme: 'snow',
                                            placeholder: 'Commencez à rédiger votre mémorandum...',
                                            modules: { toolbar: '#toolbar-container' }
                                        });

                                        if (this.content) { this.quill.root.innerHTML = this.content; }
                                        this.quill.on('text-change', () => { this.content = this.quill.root.innerHTML; });
                                    }
                                }"
                                x-init="initQuill()">
                                
                                <!-- BARRE D'OUTILS TAILWIND -->
                                <div id="toolbar-container" class="w-full max-w-4xl mb-6 bg-white rounded-t-lg shadow-sm flex flex-wrap items-center justify-center gap-1 border border-gray-200 p-2 z-20 sticky top-0">
                                    <span class="ql-formats">
                                        <select class="ql-font">
                                            <option value="tahoma" selected>Tahoma</option>
                                            <option value="arial">Arial</option>
                                            <option value="timesnewroman">Times New Roman</option>
                                        </select>
                                        <select class="ql-size">
                                            <option value="12px">12px</option>
                                            <option value="14px" selected>14px</option>
                                            <option value="16px">16px</option>
                                            <option value="18px">18px</option>
                                        </select>
                                    </span>
                                    <span class="ql-formats border-l border-gray-200 pl-2">
                                        <button class="ql-bold"></button>
                                        <button class="ql-italic"></button>
                                        <button class="ql-underline"></button>
                                        <select class="ql-color"></select>
                                    </span>
                                    <span class="ql-formats border-l border-gray-200 pl-2">
                                        <button class="ql-align" value=""></button>
                                        <button class="ql-align" value="center"></button>
                                        <button class="ql-align" value="justify"></button>
                                    </span>
                                    <span class="ql-formats border-l border-gray-200 pl-2">
                                        <button class="ql-list" value="ordered"></button>
                                        <button class="ql-list" value="bullet"></button>
                                    </span>
                                </div>

                                <!-- ZONE D'ÉDITION (FEUILLE A4) -->
                                <div class="relative w-full max-w-[21cm] bg-white shadow-2xl border border-gray-300 transition-all duration-300">
                                    
                                    <!-- CALQUE DES LIGNES DE SAUT DE PAGE (Tailwind) -->
                                    <!-- 1080px est une estimation de la hauteur utile A4 en tenant compte des marges -->
                                    <div class="absolute inset-0 pointer-events-none z-10 overflow-hidden" aria-hidden="true">
                                        
                                        <!-- Page 1 -> 2 -->
                                        <div class="absolute w-full border-t-2 border-dashed border-red-400 flex justify-end items-start" style="top: 1080px;">
                                            <span class="bg-red-500 text-white text-[9px] px-2 py-0.5 font-bold uppercase tracking-wider shadow-md rounded-bl-md">
                                                Début Page 2
                                            </span>
                                        </div>

                                        <!-- Page 2 -> 3 -->
                                        <div class="absolute w-full border-t-2 border-dashed border-red-400 flex justify-end items-start" style="top: 2160px;">
                                            <span class="bg-red-500 text-white text-[9px] px-2 py-0.5 font-bold uppercase shadow-md rounded-bl-md">
                                                Début Page 3
                                            </span>
                                        </div>

                                        <!-- Page 3 -> 4 -->
                                        <div class="absolute w-full border-t-2 border-dashed border-red-400 flex justify-end items-start" style="top: 3240px;">
                                            <span class="bg-red-500 text-white text-[9px] px-2 py-0.5 font-bold uppercase shadow-md rounded-bl-md">
                                                Début Page 4
                                            </span>
                                        </div>
                                    </div>

                                    <!-- ÉDITEUR RÉEL -->
                                    <div x-ref="quillEditor" class="text-gray-900 min-h-[29.7cm]"></div>
                                </div>

                                <!-- Footer Info -->
                                <div class="w-full max-w-[21cm] mt-4 flex justify-between text-[10px] text-gray-400 font-medium px-2">
                                    <span>Format : A4 Portrait (210 x 297 mm)</span>
                                    <span>Police recommandée : Tahoma / 11-12pt</span>
                                </div>
                            </div>
                        </div>

                        <style>
                            /* Intégration fine des styles Quill non gérables par Tailwind */
                            .ql-container.ql-snow {
                                border: none !important;
                            }
                            
                            .ql-editor {
                                padding: 50px 70px !important; /* Marges type Word */
                                min-height: 29.7cm;
                                font-family: 'Tahoma', sans-serif;
                                line-height: 1.5;
                                font-size: 14px;
                            }

                            /* Support des polices personnalisées dans la barre Quill */
                            .ql-font-tahoma { font-family: 'Tahoma', sans-serif !important; }
                            .ql-font-timesnewroman { font-family: 'Times New Roman', serif !important; }
                            .ql-font-arial { font-family: 'Arial', sans-serif !important; }

                            /* Forcer l'affichage des noms de tailles dans la barre d'outils */
                            .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="12px"]::before,
                            .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="12px"]::before { content: '12px'; }
                            .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="14px"]::before,
                            .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="14px"]::before { content: '14px'; }
                            .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="16px"]::before,
                            .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="16px"]::before { content: '16px'; }
                            .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="18px"]::before,
                            .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="18px"]::before { content: '18px'; }
                        </style>

                        

                        <!-- ATTACHMENTS SECTION -->
                        <div class="mt-8 border-t border-gray-100 pt-6">
                            <label class="block text-xs font-bold uppercase tracking-wide mb-3 flex items-center gap-2" style="color: var(--c-grey);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                Pièces Jointes (P.J.)
                            </label>

                            <div class="space-y-4">
                                <!-- Zone de Drop/Upload -->
                                <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-yellow-50/20 hover:border-[#daaf2c] transition-colors relative">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-bold text-charte-gold focus-within:ring-2 focus-within:ring-[#daaf2c]">
                                                <span>Téléverser des fichiers</span>
                                                <input id="file-upload" wire:model="attachments" type="file" class="sr-only" multiple>
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-500">PDF, DOCX, PNG, JPG jusqu'à 10MB</p>
                                    </div>

                                    <div wire:loading wire:target="attachments" class="absolute inset-0 bg-white/80 flex items-center justify-center z-10">
                                        <div class="flex items-center font-semibold text-charte-gold">
                                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Upload en cours...
                                        </div>
                                    </div>
                                </div>
                                @error('attachments.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                                <!-- LISTE DES FICHIERS -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    
                                    <!-- 1. AFFICHAGE DES FICHIERS DÉJÀ ENREGISTRÉS (Base de données) -->
                                    @foreach($existingAttachments as $index => $item)
                                        @php
                                            // On extrait le chemin (string) que l'item soit une chaîne ou un tableau
                                            $filePath = is_string($item) ? $item : ($item['path'] ?? '');
                                            // On récupère l'extension de manière sécurisée
                                            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                                            // On récupère le nom du fichier pour l'affichage
                                            $fileName = basename($filePath);
                                        @endphp
                                        
                                        <div class="relative flex items-center p-3 border border-blue-200 rounded-lg bg-blue-50/30 group hover:border-blue-400 transition-colors">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center bg-white border border-blue-100 text-blue-500 font-bold text-[10px] uppercase">
                                                {{ $extension ?: '?' }}
                                            </div>
                                            <div class="ml-4 flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate" title="{{ $fileName }}">
                                                    {{ $fileName }}
                                                </p>
                                                <p class="text-[10px] text-blue-600 font-bold uppercase tracking-tighter">Fichier déjà enregistré</p>
                                            </div>
                                            
                                            <button type="button" 
                                                wire:click="removeExistingAttachment({{ $index }})" 
                                                class="ml-2 inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-gray-400 hover:bg-red-500 transition-colors">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach

                                    <!-- 2. AFFICHAGE DES NOUVEAUX FICHIERS (En cours d'upload) -->
                                    @if($attachments)
                                        @foreach($attachments as $index => $file)
                                            <div class="relative flex items-center p-3 border border-yellow-200 rounded-lg bg-yellow-50/30 group hover:border-[#daaf2c] transition-colors">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-500 font-bold text-[10px] uppercase">
                                                    {{ $file->extension() }}
                                                </div>
                                                <div class="ml-4 flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $file->getClientOriginalName() }}</p>
                                                    <p class="text-[10px] text-green-600 font-bold uppercase">Nouveau fichier</p>
                                                </div>
                                                <button type="button" wire:click="removeAttachment({{ $index }})" class="ml-2 inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-gray-300 hover:bg-red-500 transition-colors">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif    

     

    @if($isOpen2)
       <!-- Modal Édition (Grand Format) -->
       <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" wire:click="closeModalDeux"></div>
            
            <!-- Conteneur Scrollable -->
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    
                    <!-- Le Modal Lui-même -->
                    <div class="relative transform overflow-hidden rounded-xl bg-gray-50 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-5xl border border-gray-200">
                        
                        <!-- Header / Toolbar du modal -->
                        <div class="bg-white border-b border-gray-200 px-4 py-4 sm:px-6 flex justify-between items-center sticky top-0 z-20 shadow-sm">
                            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                                <span class="bg-yellow-100 text-yellow-700 p-2 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </span>
                                Modifier 
                            </h3>
                            
                            <div class="flex items-center space-x-3">
                                <button type="button" wire:click="closeModalDeux" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Annuler
                                </button>
                                
                                <button type="button" wire:click="save" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-bold rounded-lg text-white bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 shadow-sm disabled:opacity-50">
                                    <svg wire:loading.remove wire:target="save" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Enregistrer
                                </button>
                            </div>
                        </div>

                        <!-- Corps du formulaire (Similaire au design fourni) -->
                        <div class="p-6 sm:p-8 space-y-8 bg-white min-h-[500px]">
                            
                            <!-- 1. Méta-données (Concern & Objet) -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="relative group">
                                    <label for="concern" class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Pour (Concerne)</label>
                                    <input type="text" wire:model="concern" id="concern" 
                                        class="block w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-gray-900 placeholder-gray-300 focus:border-yellow-500 focus:ring-0 sm:text-lg transition-colors" 
                                        placeholder="Ex: Direction Générale...">
                                    @error('concern') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="relative group">
                                    <label for="object" class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Objet</label>
                                    <input type="text" wire:model="object" id="object" 
                                        class="block w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-gray-900 font-semibold placeholder-gray-300 focus:border-yellow-500 focus:ring-0 sm:text-lg transition-colors" 
                                        placeholder="Sujet principal...">
                                    @error('object') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- 2. Gestion des Destinataires -->
                            <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    Destinataires
                                </h3>

                                <div class="flex flex-col md:flex-row gap-3 mb-4">
                                    <div class="flex-1">
                                        <select wire:model="newRecipientEntity" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 text-sm py-2">
                                            <option value="">-- Sélectionner --</option>
                                            @foreach($entities as $entity)
                                                <option value="{{ $entity->id }}">{{ $entity->title ?? $entity->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('newRecipientEntity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="flex-1">
                                        <select wire:model="newRecipientAction" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 text-sm py-2">
                                            <option value="">-- Action --</option>
                                            @foreach($actionsList as $act)
                                                <option value="{{ $act }}">{{ $act }}</option>
                                            @endforeach
                                        </select>
                                        @error('newRecipientAction') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <button wire:click="addRecipient" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-800 hover:bg-gray-900">
                                        Ajouter
                                    </button>
                                </div>

                                <!-- Tableau Destinataires -->
                                @if(count($recipients) > 0)
                                    <div class="bg-white rounded-md shadow-sm border border-gray-200 overflow-hidden">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Entité</th>
                                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Action</th>
                                                    <th class="px-4 py-2"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                @foreach($recipients as $index => $recipient)
                                                    <tr>
                                                        <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $recipient['entity_name'] }}</td>
                                                        <td class="px-4 py-2 text-sm">
                                                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                                {{ $recipient['action'] }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-2 text-right">
                                                            <button wire:click="removeRecipient({{ $index }})" class="text-red-500 hover:text-red-700">
                                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-400 italic text-center py-2">Aucun destinataire.</p>
                                @endif
                            </div>

                            <!-- 3. Éditeur Quill -->
                            <div class="pt-2">
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Contenu</label>
                                <div wire:ignore 
                                     class="flex flex-col bg-white rounded-lg border border-gray-300 shadow-sm"
                                     x-data="{
                                        content: @entangle('content'),
                                        quill: null,
                                        initQuill() {
                                            if(this.quill) return;
                                            this.quill = new Quill(this.$refs.quillEditor, {
                                                theme: 'snow',
                                                placeholder: 'Rédigez votre mémo...',
                                                modules: {
                                                    toolbar: [
                                                        [{ 'header': [1, 2, 3, false] }],
                                                        ['bold', 'italic', 'underline', 'strike'],
                                                        [{ 'color': [] }, { 'background': [] }],
                                                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                                        [{ 'align': [] }],
                                                        ['clean']
                                                    ]
                                                }
                                            });
                                            // Set initial content
                                            if (this.content) { this.quill.root.innerHTML = this.content; }
                                            
                                            // Sync changes
                                            this.quill.on('text-change', () => {
                                                this.content = this.quill.root.innerHTML;
                                            });
                                        }
                                     }"
                                     x-init="setTimeout(() => initQuill(), 50)"
                                >
                                    <div x-ref="quillEditor" class="h-64 sm:h-80 font-serif text-base"></div>
                                </div>
                                @error('content') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- 4. Pièces Jointes -->
                            <div class="pt-4 border-t border-gray-100">
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Pièces Jointes</label>
                                
                                <div class="space-y-4">
                                    <!-- Zone d'upload -->
                                    <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-gray-50 relative">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-10 w-10 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600 justify-center">
                                                <label for="file-upload-edit" class="relative cursor-pointer bg-white rounded-md font-medium text-yellow-600 hover:text-yellow-500">
                                                    <span>Téléverser</span>
                                                    <input id="file-upload-edit" wire:model="newAttachments" type="file" class="sr-only" multiple>
                                                </label>
                                                <p class="pl-1">ou glisser-déposer</p>
                                            </div>
                                        </div>
                                        <!-- Loading -->
                                        <div wire:loading wire:target="newAttachments" class="absolute inset-0 bg-white/80 flex items-center justify-center">
                                            <span class="text-yellow-600 font-bold flex items-center gap-2">
                                                <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Upload...
                                            </span>
                                        </div>
                                    </div>
                                    @error('newAttachments.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                                    <!-- Liste combinée (Existants + Nouveaux) -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <!-- Existants -->
                                        @foreach($existingAttachments as $idx => $fileData)
                                            @php
                                                // Logique défensive pour récupérer le nom du fichier
                                                // Si $fileData est un tableau, on cherche la clé 'path', 'url' ou on prend le premier élément
                                                if (is_array($fileData)) {
                                                    $filePath = $fileData['path'] ?? $fileData['url'] ?? reset($fileData) ?? '';
                                                } else {
                                                    // Sinon c'est une simple chaîne de caractères
                                                    $filePath = $fileData;
                                                }
                                                
                                                // On extrait le nom pour l'affichage
                                                $fileName = $filePath ? basename($filePath) : 'Fichier inconnu';
                                            @endphp

                                            <div class="flex items-center p-2 bg-gray-50 border border-gray-200 rounded-lg">
                                                <div class="bg-blue-100 p-1.5 rounded text-blue-600 mr-3">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <!-- Utilisation de la variable calculée $fileName -->
                                                    <p class="text-xs font-medium truncate" title="{{ $fileName }}">
                                                        {{ Str::limit($fileName, 25) }}
                                                    </p>
                                                    <p class="text-[10px] text-gray-500">Existant</p>
                                                </div>
                                                <button wire:click="removeExistingAttachment({{ $idx }})" type="button" class="text-gray-400 hover:text-red-500">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach

                                        <!-- Nouveaux -->
                                        @foreach($newAttachments as $idx => $file)
                                            <div class="flex items-center p-2 bg-yellow-50 border border-yellow-200 rounded-lg">
                                                <div class="bg-yellow-100 p-1.5 rounded text-yellow-600 mr-3"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg></div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-medium truncate">{{ $file->getClientOriginalName() }}</p>
                                                    <p class="text-[10px] text-gray-500">Nouveau</p>
                                                </div>
                                                <button wire:click="removeNewAttachment({{ $idx }})" class="text-gray-400 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
       </div>
    @endif

    {{-- MODAL HISTORIQUE --}}
    @if($isOpenHistory)
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" wire:click="closeHistoryModal"></div>
            
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-200">
                        
                        <div class="bg-gray-50 px-4 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Fil de discussion & Historique global
                            </h3>
                            <button type="button" wire:click="closeHistoryModal" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                        </div>

                        <div class="px-6 py-6 bg-white max-h-[70vh] overflow-y-auto">
                            @forelse($memoHistory as $log)
                                @php
                                    // On vérifie si ce log appartient au mémo d'origine ou à une réponse
                                    $isReply = ($log->memo_id != $this->memo_id);
                                    
                                    // Logique de couleur pour les points de la timeline
                                    $dotColor = 'bg-blue-500'; // Défaut
                                    $visa = Str::lower($log->visa);
                                    if(Str::contains($visa, 'accord') || Str::contains($visa, 'signé')) $dotColor = 'bg-green-500';
                                    if(Str::contains($visa, 'rejeter') || Str::contains($visa, 'clôturé')) $dotColor = 'bg-red-500';
                                    if(Str::contains($visa, 'réponse')) $dotColor = 'bg-purple-600';
                                @endphp

                                <div class="relative pl-8 pb-8 group last:pb-0 border-l-2 border-gray-100 ml-2">
                                    
                                    <!-- Point sur la timeline -->
                                    <div class="absolute -left-[9px] top-0 h-4 w-4 rounded-full border-2 border-white {{ $dotColor }} shadow-sm"></div>

                                    <div class="flex flex-col mb-1">
                                        <div class="flex justify-between items-center">
                                            <!-- Indicateur : Original vs Réponse -->
                                            @if($isReply)
                                                <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded border border-purple-200">
                                                    ↪ Élément de Réponse
                                                </span>
                                            @else
                                                <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded border border-gray-200">
                                                    Mémo Principal
                                                </span>
                                            @endif
                                            <span class="text-[11px] text-gray-400 font-mono">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                        </div>

                                        <h4 class="text-sm font-bold text-gray-900 mt-1">
                                            {{ $log->user->first_name }} {{ $log->user->last_name }}
                                            <span class="text-xs font-normal text-gray-500"> — {{ $log->user->poste }}</span>
                                        </h4>
                                    </div>

                                    <!-- Action / Visa -->
                                    <div class="mb-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold {{ $dotColor }} text-white">
                                            {{ $log->visa }}
                                        </span>
                                        
                                        @if($isReply)
                                            <span class="ml-2 text-[10px] text-gray-400 italic">
                                                (Sur mémo réponse ID #{{ $log->memo_id }})
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Commentaire -->
                                    @if($log->workflow_comment && $log->workflow_comment !== 'R.A.S')
                                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-sm text-gray-600 leading-relaxed shadow-sm">
                                            <p>{{ $log->workflow_comment }}</p>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-10">
                                    <p class="text-gray-400 italic">Aucun historique trouvé pour ce dossier.</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                            <button type="button" wire:click="closeHistoryModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:w-auto sm:text-sm">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isOpen3)
       <!-- Modal Envoi & Workflow -->
       <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" wire:click="closeModalTrois"></div>
            
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    
                    <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-200">
                        <form wire:submit.prevent="sendMemo">
                            
                            <!-- Header -->
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                    Transmission du Mémo
                                </h3>
                                <button type="button" wire:click="closeModalTrois" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            <div class="px-6 py-6 space-y-6">

                                <!-- 1. CHOIX DU TYPE DE CIRCUIT -->
                                <div>
                                    <label class="text-sm font-bold text-gray-700 block mb-2">Type de circuit</label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Option Standard -->
                                        <label class="cursor-pointer relative">
                                            <input type="radio" wire:model.live="memo_type" value="standard" class="peer sr-only">
                                            <div class="p-4 rounded-lg border-2 border-gray-200 hover:border-blue-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition-all text-center h-full flex flex-col items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400 peer-checked:text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                                <span class="block text-sm font-bold text-gray-700 peer-checked:text-blue-800">Standard</span>
                                                <span class="block text-[10px] text-gray-500 mt-1">Vers N+1 uniquement</span>
                                            </div>
                                        </label>

                                        <!-- Option Projet -->
                                        <label class="cursor-pointer relative">
                                            <input type="radio" wire:model.live="memo_type" value="projet" class="peer sr-only">
                                            <div class="p-4 rounded-lg border-2 border-gray-200 hover:border-purple-300 peer-checked:border-purple-600 peer-checked:bg-purple-50 transition-all text-center h-full flex flex-col items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400 peer-checked:text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                <span class="block text-sm font-bold text-gray-700 peer-checked:text-purple-800">Mode Projet</span>
                                                <span class="block text-[10px] text-gray-500 mt-1">Multi-collaborateurs (hors N+1)</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <hr class="border-gray-100">

                                <!-- ZONE D'AFFICHAGE DYNAMIQUE SELON LE TYPE -->
                                <div class="min-h-[100px]">
                                    
                                    <!-- A. AFFICHAGE STANDARD (N+1) -->
                                @if($memo_type === 'standard')
                                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-100 animate-fade-in-down">
                                        <p class="text-xs font-bold text-blue-500 uppercase mb-3">
                                            Destinataire Final
                                        </p>
                                        
                                        @if($managerData)
                                            <div class="flex items-start">
                                                
                                                <!-- 1. AVATAR (Celui qui reçoit vraiment) -->
                                                <div class="flex-shrink-0">
                                                    <div class="h-10 w-10 rounded-full {{ $managerData['is_replaced'] ? 'bg-orange-200 text-orange-700' : 'bg-blue-200 text-blue-700' }} flex items-center justify-center font-bold shadow-sm">
                                                        {{ substr($managerData['effective']->first_name, 0, 1) }}{{ substr($managerData['effective']->last_name, 0, 1) }}
                                                    </div>
                                                </div>
                                                
                                                <div class="ml-3 flex-1">
                                                    <!-- 2. NOM (Celui qui reçoit vraiment) -->
                                                    <p class="text-sm font-bold text-gray-900">
                                                        {{ $managerData['effective']->first_name }} {{ $managerData['effective']->last_name }}
                                                    </p>
                                                    
                                                    <!-- 3. CONTEXTE -->
                                                    @if($managerData['is_replaced'])
                                                        <!-- Cas : Remplacement Actif -->
                                                        <div class="flex flex-col mt-1">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 w-fit mb-1">
                                                                Intérimaire / Remplaçant
                                                            </span>
                                                            <p class="text-xs text-gray-500 flex items-center gap-1">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                                                Remplace <span class="font-semibold">{{ $managerData['original']->first_name }} {{ $managerData['original']->last_name }}</span> (Absent)
                                                            </p>
                                                        </div>
                                                    @else
                                                        <!-- Cas : Normal -->
                                                        <p class="text-xs text-gray-500">{{ $managerData['original']->poste ?? 'Supérieur Hiérarchique' }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <!-- Cas : Pas de manager configuré -->
                                            <div class="flex items-center gap-2 text-red-500 bg-red-50 p-3 rounded border border-red-100">
                                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <div class="text-sm">
                                                    <span class="font-bold">Erreur :</span> Aucun manager n'est associé à votre compte.
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                    <!-- B. AFFICHAGE PROJET (LISTE SANS N+1) -->
                                    @if($memo_type === 'projet')
                                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-100 animate-fade-in-down">
                                            <p class="text-xs font-bold text-purple-600 uppercase mb-2">Collaborateurs du projet</p>
                                            
                                            <label class="block text-xs text-gray-500 mb-1">Sélectionnez les destinataires (Maintenez Ctrl pour plusieurs)</label>
                                            
                                            <select wire:model.live="selected_project_users" multiple class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm h-32">
                                                @foreach($projectUsersList as $userData)
                                                    <option value="{{ $userData['original']->id }}">
                                                        {{ $userData['original']->first_name }} {{ $userData['original']->last_name }} 
                                                        ({{ $userData['original']->departement ?? 'N/A' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('selected_project_users') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                                            <!-- Récapitulatif avec alertes remplacements -->
                                            @if(!empty($selected_project_users))
                                                <div class="mt-3 space-y-2 max-h-32 overflow-y-auto">
                                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Destinataires réels :</p>
                                                    
                                                    @foreach($selected_project_users as $selectedId)
                                                        @php
                                                            // On retrouve les données dans la collection préparée
                                                            $uInfo = $projectUsersList->first(function($item) use ($selectedId) {
                                                                return $item['original']->id == $selectedId;
                                                            });
                                                        @endphp

                                                        @if($uInfo)
                                                            <div class="flex items-center justify-between bg-white p-2 rounded border {{ $uInfo['is_replaced'] ? 'border-yellow-300 bg-yellow-50' : 'border-gray-200' }}">
                                                                <div class="flex items-center gap-2">
                                                                    <div class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold">
                                                                        {{ substr($uInfo['original']->first_name, 0, 1) }}
                                                                    </div>
                                                                    <span class="text-xs font-medium text-gray-700">
                                                                        {{ $uInfo['original']->first_name }} {{ $uInfo['original']->last_name }}
                                                                    </span>
                                                                </div>

                                                                @if($uInfo['is_replaced'])
                                                                    <div class="text-[10px] text-right">
                                                                        <span class="text-yellow-600 font-bold flex items-center gap-1">
                                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                                                            Remplacé par
                                                                        </span>
                                                                        <span class="text-gray-900">{{ $uInfo['effective']->first_name }} {{ $uInfo['effective']->last_name }}</span>
                                                                    </div>
                                                                @else
                                                                    <span class="text-[10px] text-green-600 bg-green-50 px-1.5 py-0.5 rounded">Dispo</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                </div>

                                <!-- VISA & COMMENTAIRE (Commun) -->
                                <div class="space-y-4 pt-2">
                                    <hr class="border-gray-100">
                                    
                                    <!-- Visa -->
                                    

                                    <!-- Commentaire -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire</label>
                                        <textarea wire:model="workflow_comment" rows="2" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md placeholder-gray-400" placeholder="Note optionnelle..."></textarea>
                                        @error('workflow_comment') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Erreur Générale -->
                                @error('general')
                                    <div class="bg-red-50 border-l-4 border-red-500 p-4">
                                        <p class="text-sm text-red-700">{{ $message }}</p>
                                    </div>
                                @enderror

                            </div>

                            <!-- Footer Actions -->
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                                <button type="submit" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm">
                                    <span wire:loading.remove>Transmettre</span>
                                    <span wire:loading>Envoi...</span>
                                </button>
                                <button type="button" wire:click="closeModalTrois" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Annuler
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
       </div>
    @endif

</div>
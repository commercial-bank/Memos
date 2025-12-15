<div class="min-h-screen bg-gray-50 py-8 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- EN-TÊTE & RECHERCHE -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Mes Mémos</h2>
                <p class="text-sm text-gray-500">Gérez vos mémos en attente d'envoi.</p>
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
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Objet & Concerne</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destinataires</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pièces Jointes</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
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
                                    <div class="flex items-center justify-center space-x-3">
                                        
                                        <!-- VOIR -->
                                        <button wire:click="viewMemo({{ $memo->id }})" class="text-gray-400 hover:text-blue-600 transition-colors" title="Aperçu">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>

                                        <!-- MODIFIER -->
                                        <button wire:click="editMemo({{ $memo->id }})" class="text-gray-400 hover:text-yellow-600 transition-colors" title="Modifier">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>

                                        <!-- ENVOYER / ASSIGNER -->
                                        <button wire:click="assignMemo({{ $memo->id }})" class="text-gray-400 hover:text-green-600 transition-colors" title="Attribuer & Envoyer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                        </button>

                                        <!-- SUPPRIMER -->
                                        <button wire:click="deleteMemo({{ $memo->id }})" class="text-gray-400 hover:text-red-600 transition-colors" title="Supprimer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-lg font-medium text-gray-900">Aucun Mémo trouvé</p>
                                        <p class="text-sm">Commencez par créer un nouveau mémorandum.</p>
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

    {{-- MODALS --}}
    
    @if($isOpen)
        <!-- Modal Aperçu -->
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            <!-- Overlay -->
            <div wire:click="closeModal" class="fixed inset-0 bg-gray-900 bg-opacity-90 transition-opacity backdrop-blur-sm cursor-pointer"></div>

            <!-- Toolbar -->
            <div class="fixed top-0 left-0 w-full z-50 pointer-events-none p-4 flex justify-between items-start print:hidden">
                <button wire:click="closeModal" class="pointer-events-auto bg-gray-800 text-white hover:bg-gray-700 px-6 py-2 rounded-full shadow-xl font-bold flex items-center gap-2 border border-gray-600">
                    <span>&larr; Retour</span>
                </button>
            </div>

            <!-- LOGIQUE BACKEND POUR RÉCUPÉRER LES DONNÉES MANQUANTES -->
            @php
                // Récupération du mémo complet avec les relations pour l'affichage
                $currentMemo = \App\Models\Memo::with('destinataires.entity')->find($memo_id);
                
                // Groupement des destinataires par leur Action
                $recipientsByAction = $currentMemo 
                    ? $currentMemo->destinataires->groupBy('action') 
                    : collect([]);

                // Helper pour formater la liste des entités (REF ou Nom)
                $formatRecipients = function($group) {
                    return $group->map(function($dest) {
                        // Utilise 'ref' (acronyme) sinon 'name'
                        return $dest->entity->ref ?? Str::limit($dest->entity->name, 15);
                    })->join(', ');
                };
            @endphp

            <!-- Conteneur Scrollable avec Alpine Data -->
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto pt-20 pb-10" x-data="memoPagination">
                <div class="flex min-h-full items-start justify-center p-4 text-center sm:p-0">
                    
                    <div class="relative flex flex-col items-center font-sans w-full max-w-[210mm]">

                        <!-- BOUTON TÉLÉCHARGER -->
                        <button 
                            wire:click="downloadMemoPDF" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="mb-4 pointer-events-auto bg-red-600 text-white hover:bg-red-700 px-6 py-2.5 rounded-full shadow-lg font-bold flex items-center gap-3 border border-red-500 transition-transform transform hover:scale-105 disabled:opacity-50">
                            <span>Télécharger PDF</span>
                        </button>

                        <!-- 1. SOURCE DE CONTENU CACHÉE (Raw Content) -->
                        <div x-ref="rawContent" class="hidden">
                            {!! $content !!}
                        </div>

                        <!-- 2. CONTENEUR DES PAGES GÉNÉRÉES -->
                        <div id="export-container" x-ref="pagesContainer" class="flex flex-col gap-8">
                            <!-- Les pages vont être injectées ici par le JS -->
                        </div>

                        <!-- 3. TEMPLATE DE LA PAGE (Modèle utilisé par le JS) -->
                        <template id="page-template">
                            <!-- PAGE A4 -->
                            <div class="page-a4 bg-white w-[210mm] h-[297mm] shadow-2xl p-[10mm] text-black 
                                        font-['Times_New_Roman',_Times,_serif] text-base leading-snug 
                                        relative text-left mx-auto overflow-hidden">
                                
                                <!-- CADRE DORÉ -->
                                <div class="gold-frame border-[3px] border-[#D4AF37] rounded-tr-[60px] rounded-bl-[60px] p-8 h-full flex flex-col relative">

                                    <!-- EN-TÊTE -->
                                    <div class="header-section flex flex-col items-center justify-center mb-4 text-center shrink-0">
                                        <div class="mb-2">
                                            <div class="w-17 h-16 flex items-center justify-center mx-auto mb-1">
                                                <img src="{{ asset('images/logo.jpg') }}" alt="logo" class="w-full h-full object-contain">
                                            </div>
                                        </div>
                                        <!-- Le texte de l'entité reste text-xs (12px, plus petit que 12pt) -->
                                        <h2 class="font-bold text-xs uppercase text-gray-800">{{ $user_entity_name }}</h2>
                                        <!-- Le titre "Memorandum" reste en Arial 2xl -->
                                        <h1 class="font-['Arial'] font-extrabold text-2xl uppercase mt-2 italic inline-block">Memorandum</h1>
                                    </div>

                                    <!-- TABLEAU DESTINATAIRES (Classe 'recipient-section' ajoutée pour le masquage page 2) -->
                                    <div class="recipient-section mb-6 text-sm w-full shrink-0">
                                        
                                        <!-- LIGNE D'ALIGNEMENT -->
                                        <!-- Le texte ici reste text-[13px] et font-['Arial'] -->
                                        <div class="flex w-full text-[13px] font-bold font-['Arial'] pb-1 text-black">
                                            <div class="w-[35%]"></div>
                                            <div class="w-[30%] text-center">Prière de :</div>
                                            <div class="w-[35%] pl-8">Destinataires :</div>
                                        </div>
                                        
                                        <!-- TABLEAU COMPLET PHP -->
                                        <!-- Le tableau lui-même reste en text-[13px] et font-['Arial'] -->
                                        <table class="w-full border-collapse border border-black text-[13px] font-['Arial'] text-black">
                                            
                                            <!-- LIGNES DU TABLEAU -->
                                            @php $recipients1 = $recipientsByAction['Faire le nécessaire'] ?? collect([]); @endphp
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold w-[35%] align-top">
                                                    Date : {{ $date }}
                                                </td>
                                                <td class="border border-black p-1 pl-2 w-[30%]">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients1->count() > 0 ? 'bg-green-600' : '' }}"></span> 
                                                    <span class="{{ $recipients1->count() > 0 ? 'font-bold' : '' }}">Faire le nécessaire</span>
                                                </td>
                                                <td class="border border-black p-1 text-center w-[35%] {{ $recipients1->count() > 0 ? 'font-bold bg-gray-50' : '' }}">
                                                    {{ $recipients1->count() > 0 ? $formatRecipients($recipients1) : '' }}
                                                </td>
                                            </tr>

                                            @php $recipients2 = $recipientsByAction['Prendre connaissance'] ?? collect([]); @endphp
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold align-top">
                                                    N° : {{ $currentMemo->reference ?? 'En attente' }}
                                                </td>
                                                <td class="border border-black p-1 pl-2">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients2->count() > 0 ? 'bg-blue-600' : '' }}"></span> 
                                                    <span class="{{ $recipients2->count() > 0 ? 'font-bold' : '' }}">Prendre connaissance</span>
                                                </td>
                                                <td class="border border-black p-1 text-center {{ $recipients2->count() > 0 ? 'font-bold bg-gray-50' : '' }}">
                                                    {{ $recipients2->count() > 0 ? $formatRecipients($recipients2) : '' }}
                                                </td>
                                            </tr>

                                            @php $recipients3 = $recipientsByAction['Prendre position'] ?? collect([]); @endphp
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold align-top">
                                                    Emetteur : #
                                                </td>
                                                <td class="border border-black p-1 pl-2">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients3->count() > 0 ? 'bg-orange-500' : '' }}"></span> 
                                                    <span class="{{ $recipients3->count() > 0 ? 'font-bold' : '' }}">Prendre position</span>
                                                </td>
                                                <td class="border border-black p-1 text-center {{ $recipients3->count() > 0 ? 'font-bold bg-gray-50' : '' }}">
                                                    {{ $recipients3->count() > 0 ? $formatRecipients($recipients3) : '' }}
                                                </td>
                                            </tr>

                                            @php $recipients4 = $recipientsByAction['Décider'] ?? collect([]); @endphp
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold align-top">
                                                    Service : {{ $user_service }}
                                                </td>
                                                <td class="border border-black p-1 pl-2">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients4->count() > 0 ? 'bg-yellow-400' : '' }}"></span> 
                                                    <span class="{{ $recipients4->count() > 0 ? 'font-bold' : '' }}">Décider</span>
                                                </td>
                                                <td class="border border-black p-1 text-center {{ $recipients4->count() > 0 ? 'font-bold bg-gray-50' : '' }}">
                                                    {{ $recipients4->count() > 0 ? $formatRecipients($recipients4) : '' }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- OBJET & CONCERNE (Classe 'object-section' ajoutée pour le masquage page 2) -->
                                    <div class="object-section mb-4 shrink-0">
                                        <div class="mb-6">
                                            <!-- Le texte ici hérite Times New Roman, mais la taille reste 15px pour l'objet -->
                                            <p class="mb-1">
                                                <span class="font-bold text-[15px] underline">Objet :</span> 
                                                <span class="uppercase font-bold"> {{ $object }} </span>
                                            </p>
                                        </div>
                                        <div class="mb-6">
                                            <p class="mb-1">
                                                <span class="font-bold text-[15px] underline">Concerne :</span> 
                                                <span class="lowercase text-gray-800"> {{ $concern }} </span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- ZONE DE CONTENU CIBLE -->
                                    <!-- Cette div hérite désormais la police Times New Roman et la taille 12pt (text-base) du parent .page-a4 -->
                                    <div class="content-target flex-grow min-h-0 px-2 overflow-hidden mb-4 text-justify space-y-3 
                                                text-gray-900 break-words w-full">
                                        <!-- VIDE PAR DÉFAUT -->
                                    </div>

                                    <!-- PIED DE PAGE -->
                                    <div class="footer-section mt-auto shrink-0 w-full flex flex-col items-center justify-center pt-2">
                                        
                                        <!-- QR Code Section (Classe 'qr-section' pour le masquage page 2) -->
                                        <div class="qr-section">
                                            @if($currentMemo && $currentMemo->qr_code)
                                                <div class="bg-white p-0.5 border border-gray-200 inline-block mb-1">
                                                    {{-- QR Code en SVG Base64 --}}
                                                    <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(50)->generate(route('memo.verify', $currentMemo->qr_code))) }}" 
                                                        width="50" height="50" alt="QR">
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Le texte de référence reste en text-[10px] (plus petit) -->
                                        <div class="ref-text text-[10px] text-gray-500 italic">{{ $currentMemo->numero_ref }}</div>
                                    </div>

                                </div> 
                            </div>
                        </template>

                    </div>
                </div>
            </div>
        </div>
    @endif

   @if($isOpen2)
    <!-- Modal Édition (Positionné sous la Navbar) -->
    <!-- z-[100] garantit qu'il est AU-DESSUS de tout le reste de l'interface -->
    <div class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop (Fond sombre) -->
        <div 
            class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity" 
            wire:click="closeModalDeux">
        </div>

        <!-- Conteneur de positionnement -->
        <!-- Changements ici : 
             1. 'items-start' au lieu de 'items-center' (pour coller en haut)
             2. 'pt-24' (Padding Top) pour descendre la fenêtre sous la barre de titre 
             3. 'pb-10' pour laisser une marge en bas -->
        <div class="fixed inset-0 z-[100] w-screen h-screen overflow-hidden flex items-start justify-center px-4 sm:px-8 pt-24 pb-10">
            
            <!-- Le Modal -->
            <!-- max-h-full permet d'utiliser l'espace défini par les paddings du conteneur parent -->
            <div class="relative flex flex-col w-full max-w-6xl h-full max-h-full bg-white rounded-xl shadow-2xl ring-1 ring-black/5 overflow-hidden border border-gray-200">
                
                <!-- 1. HEADER (Toujours visible) -->
                <div class="shrink-0 flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white z-20">
                    <div class="flex items-center gap-4">
                        <span class="flex items-center justify-center w-10 h-10 bg-yellow-100 text-yellow-700 rounded-lg shadow-sm border border-yellow-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </span>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 leading-tight">Modifier le Brouillon</h3>
                            <p class="text-xs text-gray-500 font-medium">Mode édition</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="closeModalDeux" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none transition-colors">
                            Annuler
                        </button>
                        
                        <button type="button" wire:click="save" wire:loading.attr="disabled" class="inline-flex items-center px-5 py-2 border border-transparent text-sm font-bold rounded-lg text-white bg-[#daaf2c] hover:bg-yellow-600 shadow-md focus:outline-none focus:ring-2 focus:ring-yellow-500 disabled:opacity-50 transition-all">
                            <span wire:loading.remove class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Enregistrer
                            </span>
                            <span wire:loading class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                ...
                            </span>
                        </button>
                    </div>
                </div>

                <!-- 2. BODY (Scrollable) -->
                <div class="flex-1 overflow-y-auto bg-gray-50/50 custom-scrollbar p-6">
                    
                    <!-- Contenu centré -->
                    <div class="max-w-5xl mx-auto space-y-6 bg-white p-8 rounded-xl border border-gray-200 shadow-sm">
                        
                        <!-- A. Méta-données -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="relative group">
                                <label for="concern" class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Pour (Concerne)</label>
                                <input type="text" wire:model="concern" id="concern" 
                                    class="block w-full border-gray-300 rounded-lg bg-gray-50 focus:bg-white focus:border-yellow-500 focus:ring-yellow-500 sm:text-sm py-2.5 transition-colors" 
                                    placeholder="Ex: Direction Générale...">
                                @error('concern') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="relative group">
                                <label for="object" class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Objet</label>
                                <input type="text" wire:model="object" id="object" 
                                    class="block w-full border-gray-300 rounded-lg bg-gray-50 focus:bg-white focus:border-yellow-500 focus:ring-yellow-500 sm:text-sm py-2.5 font-semibold transition-colors" 
                                    placeholder="Sujet principal...">
                                @error('object') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- B. Gestion des Destinataires -->
                        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                Destinataires
                            </h3>

                            <div class="flex gap-2 mb-4">
                                <div class="flex-1">
                                    <select wire:model="newRecipientEntity" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 text-sm">
                                        <option value="">Entité...</option>
                                        @foreach($entities as $entity)
                                            <option value="{{ $entity->id }}">{{ $entity->title ?? $entity->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-32">
                                    <select wire:model="newRecipientAction" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 text-sm">
                                        <option value="">Action...</option>
                                        @foreach($actionsList as $act)
                                            <option value="{{ $act }}">{{ $act }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button wire:click="addRecipient" type="button" class="inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gray-800 hover:bg-gray-900 transition-colors">
                                    Ajouter
                                </button>
                            </div>

                            @if(count($recipients) > 0)
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            @foreach($recipients as $index => $recipient)
                                                <tr>
                                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $recipient['entity_name'] }}</td>
                                                    <td class="px-4 py-2 text-sm">
                                                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                                                            {{ $recipient['action'] }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-2 text-right">
                                                        <button wire:click="removeRecipient({{ $index }})" class="text-gray-400 hover:text-red-500">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <!-- C. Éditeur Quill -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Contenu</label>
                            <div wire:ignore 
                                    class="flex flex-col bg-white rounded-lg border border-gray-300 shadow-sm transition-all focus-within:ring-1 focus-within:ring-yellow-500"
                                    x-data="{
                                    content: @entangle('content'),
                                    quill: null,
                                    initQuill() {
                                        if(this.quill) return;
                                        this.quill = new Quill(this.$refs.quillEditor, {
                                            theme: 'snow',
                                            placeholder: 'Saisissez le corps du mémorandum ici...',
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
                                        if (this.content) { this.quill.root.innerHTML = this.content; }
                                        this.quill.on('text-change', () => {
                                            this.content = this.quill.root.innerHTML;
                                        });
                                    }
                                    }"
                                    x-init="setTimeout(() => initQuill(), 50)"
                            >
                                <div x-ref="quillEditor" class="min-h-[400px] font-serif text-base text-gray-800"></div>
                            </div>
                            @error('content') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- D. Pièces Jointes -->
                        <div class="pt-4 border-t border-gray-200">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Pièces Jointes</label>
                            
                            <div class="space-y-4">
                                <div class="group flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:bg-yellow-50/30 hover:border-yellow-400 transition-all relative cursor-pointer">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-10 w-10 text-gray-400 group-hover:text-yellow-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label class="relative cursor-pointer font-bold text-yellow-600 hover:text-yellow-500">
                                                <span>Cliquez pour téléverser</span>
                                                <input wire:model="newAttachments" type="file" class="sr-only" multiple>
                                            </label>
                                        </div>
                                    </div>
                                    <div wire:loading wire:target="newAttachments" class="absolute inset-0 bg-white/90 flex items-center justify-center rounded-lg">
                                        <span class="text-yellow-600 font-bold flex items-center gap-2">
                                            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            Traitement...
                                        </span>
                                    </div>
                                </div>

                                <!-- Liste des fichiers -->
                                @if(count($existingAttachments) > 0 || count($newAttachments) > 0)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                        <!-- Existants -->
                                        @foreach($existingAttachments as $idx => $fileData)
                                            @php
                                                $filePath = is_array($fileData) ? ($fileData['path'] ?? reset($fileData) ?? '') : $fileData;
                                                $fileName = $filePath ? basename($filePath) : 'Fichier';
                                            @endphp
                                            <div class="flex items-center p-2 bg-white border border-gray-200 rounded-lg shadow-sm">
                                                <div class="bg-blue-50 p-2 rounded text-blue-600 mr-2 shrink-0">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-bold text-gray-700 truncate">{{ Str::limit($fileName, 20) }}</p>
                                                    <p class="text-[10px] text-gray-400">Existant</p>
                                                </div>
                                                <button wire:click="removeExistingAttachment({{ $idx }})" class="text-gray-300 hover:text-red-500 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                            </div>
                                        @endforeach

                                        <!-- Nouveaux -->
                                        @foreach($newAttachments as $idx => $file)
                                            <div class="flex items-center p-2 bg-yellow-50 border border-yellow-200 rounded-lg shadow-sm">
                                                <div class="bg-yellow-100 p-2 rounded text-yellow-600 mr-2 shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg></div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-bold text-gray-900 truncate">{{ $file->getClientOriginalName() }}</p>
                                                    <p class="text-[10px] text-yellow-600">Nouveau</p>
                                                </div>
                                                <button wire:click="removeNewAttachment({{ $idx }})" class="text-gray-400 hover:text-red-500 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

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
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Votre Visa / Action</label>
                                        <div class="grid grid-cols-3 gap-3">
                                            @foreach(['Vu' => 'gray', 'Vu & Accord' => 'green', "Vu & Pas d'accord" => 'red'] as $visa => $color)
                                                <label class="cursor-pointer">
                                                    <input type="radio" wire:model="selected_visa" value="{{ $visa }}" class="peer sr-only">
                                                    <div class="rounded-md border border-gray-200 p-2 hover:bg-{{ $color }}-50 peer-checked:border-{{ $color }}-500 peer-checked:bg-{{ $color }}-50 peer-checked:ring-1 peer-checked:ring-{{ $color }}-500 transition-all text-center">
                                                        <span class="text-xs font-medium text-{{ $color }}-900">{{ $visa }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('selected_visa') <span class="text-red-500 text-xs mt-1">Le visa est obligatoire.</span> @enderror
                                    </div>

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

    @if($isOpen4)
       <!-- Modal Suppression -->
       <div class="relative z-50" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-red-600 mb-2">Supprimer le brouillon ?</h3>
                        <p class="text-gray-500 mb-6">Cette action est irréversible.</p>
                        <div class="flex justify-end gap-3">
                             <button wire:click="closeModalQuatre" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">Annuler</button>
                             <button wire:click="del" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Supprimer</button>
                        </div>
                    </div>
                </div>
            </div>
       </div>
    @endif

</div>

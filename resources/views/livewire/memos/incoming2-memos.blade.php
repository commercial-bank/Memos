<div class="min-h-screen bg-gray-50 py-8 font-sans">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

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

                                        

                                            <button wire:click="viewMemo({{ $memo->id }})" class="text-gray-400 hover:text-blue-600 transition-colors" title="Aperçu">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </button>

                                            <!-- FAVORIS -->
                                            <button 
                                                wire:click="toggleFavorite({{ $memo->id }})" 
                                                class="transition-colors duration-200 {{ $memo->is_favorited ? 'text-yellow-400 hover:text-yellow-500' : 'text-gray-300 hover:text-yellow-400' }}" 
                                                title="{{ $memo->is_favorited ? 'Retirer des favoris' : 'Ajouter aux favoris' }}">
                                                
                                                <!-- L'icône change selon l'état -->
                                                @if($memo->is_favorited)
                                                    <!-- Étoile PLEINE (Solid) -->
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                @else
                                                    <!-- Étoile VIDE (Outline) -->
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                                    </svg>
                                                @endif
                                            </button>

                                            <!-- ENREGISTRER ET TRANSMETTRE -->
                                            <button wire:click="transMemo({{ $memo->id }})" 
                                                    wire:loading.attr="disabled"
                                                    class="text-gray-400 hover:text-indigo-600 transition-colors" 
                                                    title="Enregistrer et transmettre">
                                                
                                                <!-- Icône Avion / Transmission -->
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                                </svg>
                                            </button>


                                           
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-lg font-medium text-gray-900">Aucun Mémo entrant dans votre entité pour le moment</p>
                                        <p class="text-sm">Les mémos entrants dans votre entité et qui vous ont été cotés s'afficheront ici..</p>
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

    <!-- ============================================== -->
    <!-- MODAL 1 : ENREGISTREMENT (Pour Secrétaires)    -->

    @if($isRegistrationModalOpen)
        <div class="fixed inset-0 z-[110] overflow-y-auto" aria-labelledby="modal-reg-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity" wire:click="closeRegistrationModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </div>
                            
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-reg-title">
                                    Enregistrement du Courrier
                                </h3>
                                <div class="mt-4 space-y-3">
                                    
                                    <!-- 1. RÉFÉRENCE & DATE (Côte à côte pour gagner de la place) -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Référence *</label>
                                            <input type="text" wire:model="reg_reference" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                                            @error('reg_reference') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Date Enreg. *</label>
                                            <input type="text" wire:model="reg_date" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                                            @error('reg_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <!-- 2. ENTITÉ EXPÉDITRICE -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Entité Expéditrice *</label>
                                        <input type="text" wire:model="reg_expediteur" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-50 focus:ring-purple-500 focus:border-purple-500" placeholder="Entité d'origine">
                                        <p class="text-[10px] text-gray-400 mt-0.5">Pré-rempli automatiquement selon l'expéditeur du mémo.</p>
                                        @error('reg_expediteur') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- 3. NATURE -->
                                    <div>
                                        
                                        <input type="hidden" wire:model="reg_nature" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500" placeholder="Ex: Note de service, Lettre...">
                                        @error('reg_nature') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- 4. OBJET -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Objet *</label>
                                        <textarea wire:model="reg_objet" rows="2" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500"></textarea>
                                        @error('reg_objet') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="saveRegistrationAndContinue" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            <span wire:loading.remove wire:target="saveRegistrationAndContinue">Enregistrer et Continuer &rarr;</span>
                            <span wire:loading wire:target="saveRegistrationAndContinue">...</span>
                        </button>
                        <button type="button" wire:click="closeRegistrationModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif


    <!-- ============================================== -->
    <!-- MODAL 2 : TRANSMISSION / COTATION (Pour tous)  -->
    <!-- ============================================== -->
    @if($isTransModalOpen)
        <div class="fixed inset-0 z-[120] overflow-y-auto" aria-labelledby="modal-trans-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <!-- Overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                    wire:click="closeTransModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Contenu du Modal -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            
                            <!-- Icone Avion Bleu -->
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </div>
                            
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-trans-title">
                                    Transmettre le mémo
                                </h3>
                                
                                <div class="mt-4">
                                    <!-- Message d'instruction -->
                                    <p class="text-sm text-gray-500 mb-4">
                                        Veuillez sélectionner le(s) destinataire(s) du groupe <span class="font-bold text-gray-800">{{ $targetRoleName }}</span>.
                                    </p>

                                    <!-- 1. CHAMP COMMENTAIRE / NOTE (Historique) -->
                                    <div class="mb-4">
                                        <label for="comment" class="block text-sm font-medium text-gray-700">Note / Annotation (Optionnel)</label>
                                        <textarea 
                                            wire:model="comment" 
                                            id="comment" 
                                            rows="2" 
                                            class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md placeholder-gray-400"
                                            placeholder="Ex: Pour attribution, Pour avis, Vu et validé..."></textarea>
                                    </div>

                                    <!-- 2. LISTE DES DESTINATAIRES (Checkboxes) -->
                                    <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-md bg-gray-50">
                                        <ul class="divide-y divide-gray-200">
                                            @foreach($targetRecipients as $recipient)
                                                <li class="relative flex items-start py-3 px-4 hover:bg-white transition cursor-pointer">
                                                    <div class="min-w-0 flex-1 text-sm">
                                                        <label for="recipient-{{ $recipient->id }}" class="font-medium text-gray-700 select-none cursor-pointer block w-full">
                                                            {{ $recipient->first_name }} {{ $recipient->last_name }}
                                                            <span class="block text-gray-500 text-xs mt-0.5">{{ $recipient->poste }}</span>
                                                        </label>
                                                    </div>
                                                    <div class="ml-3 flex items-center h-5">
                                                        <input id="recipient-{{ $recipient->id }}" 
                                                            value="{{ $recipient->id }}" 
                                                            wire:model="selectedRecipients" 
                                                            type="checkbox" 
                                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded cursor-pointer">
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    
                                    @error('selectedRecipients') 
                                        <span class="text-red-500 text-xs mt-2 block font-medium">
                                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer du Modal -->
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <!-- Bouton Valider -->
                        <button type="button" 
                                wire:click="confirmTransmission" 
                                wire:loading.attr="disabled"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            
                            <span wire:loading.remove wire:target="confirmTransmission">
                                Transmettre <span class="ml-1">&rarr;</span>
                            </span>
                            <span wire:loading wire:target="confirmTransmission" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Traitement...
                            </span>
                        </button>
                        
                        <!-- Bouton Annuler -->
                        <button type="button" 
                                wire:click="closeTransModal" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Annuler
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif



</div>
    
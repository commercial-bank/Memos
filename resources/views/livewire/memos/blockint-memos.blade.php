<div class="relative bg-[#fdfbf7] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.3)] rounded-lg overflow-hidden border border-gray-300 min-h-[600px] font-sans">
    
    <!-- Décoration : Reliure à gauche (Effet Classeur) -->
    <div class="absolute left-0 top-0 bottom-0 w-12 bg-gray-100 border-r border-gray-300 flex flex-col items-center pt-8 space-y-12 z-10">
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
    </div>

    <!-- Contenu du registre (Décalé vers la droite à cause de la reliure) -->
    <div class="ml-12 flex flex-col h-full min-h-[600px]">
        
        <!-- En-tête du registre -->
            <div class="bg-slate-800 text-white px-8 py-5 flex flex-col md:flex-row justify-between items-center gap-4 border-b-4 border-slate-600 shadow-md z-20 relative">
                
                <!-- TITRE -->
                <div class="flex-shrink-0">
                    <h2 class="text-2xl font-serif tracking-widest uppercase font-bold">Registre </h2>
                    <p class="text-slate-400 text-xs uppercase tracking-wide">Mémos Entrants</p>
                </div>

                <!-- BARRE DE RECHERCHE CENTRALE (NOUVEAU) -->
                <div class="flex-grow max-w-xl w-full mx-4">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <!-- Icone Loupe -->
                            <svg class="h-5 w-5 text-slate-400 group-focus-within:text-yellow-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        
                        <input 
                            wire:model.live.debounce.300ms="search" 
                            type="text" 
                            class="block w-full pl-10 pr-10 py-2 border border-slate-600 rounded-lg leading-5 bg-slate-900/50 text-slate-200 placeholder-slate-500 focus:outline-none focus:bg-slate-900 focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm transition duration-150 ease-in-out shadow-inner" 
                            placeholder="Rechercher par référence, objet, contenu..."
                        >

                        <!-- Bouton Reset (X) s'affiche si recherche active -->
                        @if(!empty($search))
                            <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-white cursor-pointer">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        @endif
                    </div>
                </div>
                
                <!-- SÉLECTEUR D'ANNÉE INTEGRÉ -->
                <div class="flex-shrink-0 flex items-center gap-3 bg-slate-700/50 p-1.5 rounded-lg border border-slate-600">
                    <label for="yearSelect" class="text-xs text-slate-300 uppercase font-bold pl-2">Année :</label>
                    <div class="relative group">
                        <select 
                            wire:model.live="selectedYear" 
                            id="yearSelect"
                            class="appearance-none bg-slate-900 border border-slate-500 text-white text-sm font-mono font-bold py-1 pl-3 pr-8 rounded cursor-pointer hover:bg-slate-800 hover:border-yellow-500 focus:outline-none focus:ring-1 focus:ring-yellow-500 transition-all"
                        >
                            @for($y = date('Y'); $y >= 2023; $y--) 
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                        <!-- Flèche custom -->
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400 group-hover:text-yellow-500 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Tableau "Grille Papier" -->
        <div class="overflow-x-auto flex-grow">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs uppercase text-slate-500 border-b-2 border-slate-800 bg-slate-100 sticky top-0 z-10 shadow-sm">
                        <th class="px-4 py-3 border-r border-slate-300 w-28 text-center font-bold">Date Entrer</th>
                        <th class="px-4 py-3 border-r border-slate-300 w-32 font-bold">N° Reference</th>
                        <th class="px-4 py-3 border-r border-slate-300 w-48 font-bold">Concerne</th>
                        <th class="px-4 py-3 font-bold">Objet</th>
                        <th class="px-2 py-3 w-10"></th> 
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700 bg-[url('https://www.transparenttextures.com/patterns/lined-paper.png')]">
    
                    @forelse($references as $ref)
                        <tr class="border-b border-slate-300 hover:bg-yellow-50 transition-colors duration-150 group h-14 bg-white/50">
                            
                            <!-- Date (Champ date_enreg de la table bloc) -->
                            <td class="px-4 py-2 border-r border-slate-300 font-mono text-slate-600 text-xs text-center">
                                {{ $ref->date_enreg }} 
                            </td>

                            <!-- N° Ordre (Extrait de la référence : tout ce qui est avant le premier /) -->
                            <td class="px-4 py-2 border-r border-slate-300">
                                <span class="bg-slate-100 text-slate-900 px-2 py-1 rounded text-xs font-bold font-mono border border-slate-300">
                                    {{ $ref->reference}}
                                </span>
                            </td>

                            <!-- Concerne (Via la relation memo) -->
                            <td class="px-4 py-2 border-r border-slate-300 font-semibold text-slate-800">
                                {{ $ref->memo->concern ?? 'N/A' }}
                            </td>

                            <!-- Objet (Via la relation memo) -->
                            <td class="px-4 py-2 text-slate-700 leading-snug">
                                {{ Str::limit($ref->memo->object ?? 'Objet non disponible', 60) }} 
                            </td>
                            
                            <!-- Actions -->
                            <td class="px-2 text-center">
                                <!-- On passe l'ID du mémo car c'est ce que attend viewReference -->
                                <button 
                                    wire:click="viewMemo({{ $ref->memo_id }})"
                                    class="text-slate-400 hover:text-blue-600 opacity-0 group-hover:opacity-100 transition transform hover:scale-110"
                                    title="Voir les détails"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                        <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                        <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic font-serif">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                
                                @if(!empty($search))
                                    <span>Aucun résultat ne correspond à votre recherche "<strong>{{ $search }}</strong>" pour l'année {{ $selectedYear }}.</span>
                                    <button wire:click="$set('search', '')" class="mt-2 text-blue-500 hover:underline text-sm font-sans">Réinitialiser la recherche</button>
                                @else
                                    <span>Aucune référence trouvée pour l'année <strong>{{ $selectedYear }}</strong>.</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                    
                    <!-- ... (reste du code de remplissage identique) ... -->

                </tbody>
            </table>
        </div>
        
        <!-- Pied de page -->
        <div class="p-4 border-t-2 border-slate-800 bg-slate-50 flex justify-between items-center mt-auto">
            <div class="text-xs text-slate-500 font-serif italic flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                Registre synchronisé • Année {{ $selectedYear }}
            </div>
            <div class="text-xs font-bold text-slate-700 bg-slate-200 px-3 py-1 rounded-full">
                TOTAL : {{ count($references) }}
            </div>
        </div>

    </div>

    <!-- MODAL (Votre code modal existant) -->
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
                                        <div class="ref-text text-[10px] text-gray-500 italic">{{ $ref_number }}</div>
                                    </div>

                                </div> 
                            </div>
                        </template>

                    </div>
                </div>
            </div>
        </div>
    @endif @if($isOpen)
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
    
</div>
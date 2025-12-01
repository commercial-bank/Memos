<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    @forelse($groupedMemos as $document)

        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 flex flex-col justify-between relative overflow-hidden h-full">

            <!-- BADGE DE STATUT (Positionné en absolu en haut à gauche) -->
            <div class="absolute top-0 left-0 z-10">
                @if($document->status == 'pending')
                    <span class="bg-orange-100 text-orange-600 text-xs font-bold px-3 py-1 rounded-br-lg shadow-sm">En traitement</span>
                @elseif($document->status == 'distributed')
                    <span class="bg-green-100 text-green-600 text-xs font-bold px-3 py-1 rounded-br-lg shadow-sm">Diffusé</span>
                @elseif($document->status == 'rejected')
                    <span class="bg-red-100 text-red-600 text-xs font-bold px-3 py-1 rounded-br-lg shadow-sm">Rejeté</span>
                @else
                    <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-br-lg shadow-sm">Document</span>
                @endif
            </div>

            <!-- Décoration coin (conservée mais réduite) -->
            <div class="absolute top-0 right-0 w-16 h-16 bg-blue-50 opacity-50 transform rotate-45 translate-x-8 -translate-y-8 pointer-events-none"></div>

            <div class="mt-6"> <!-- Marge ajoutée car le badge est en absolute -->
                
                <!-- EN-TÊTE : Le Document -->
                <div class="mb-3 relative">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">
                                Réf: {{ $document->reference }}
                            </span>
                            <h3 class="text-lg font-bold text-gray-800 leading-tight mt-1 line-clamp-2" title="{{ $document->object }}">
                                {{ $document->object }}
                            </h3>
                            <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ $document->created_at->format('d/m/Y à H:i') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- MOTIF DU REJET -->
                @if($document->status == 'rejected' && $document->workflow_comment)
                    <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-2.5 rounded-r-md">
                        <div class="flex">
                            <div class="ml-1">
                                <p class="text-xs text-red-700 font-medium italic">"{{ $document->workflow_comment }}"</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- LISTE DES DESTINATAIRES -->
                <div class="bg-gray-50 rounded-lg p-3 mb-3 max-h-32 overflow-y-auto custom-scrollbar border border-gray-100">
                    <h4 class="text-[10px] font-bold text-gray-400 uppercase mb-2">Destinataires</h4>
                    <ul class="space-y-2">
                        @foreach($document->destinataires as $destinataire)
                            <li class="flex justify-between items-center text-xs">
                                <span class="font-medium text-gray-700 truncate w-2/3" title="{{ $destinataire->title }}">
                                    {{ $destinataire->title->acronym ?? Str::limit($destinataire->title, 20) }}
                                </span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-white border border-gray-200 text-purple-600 font-semibold whitespace-nowrap">
                                    {{ $destinataire->pivot->action }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

          

            

            <!-- PIED DE LA CARTE : ACTIONS -->
            <div class="flex justify-between items-center pt-3 border-t border-gray-100 mt-auto">
                <div class="text-xs text-gray-400 font-medium">
                    {{ $document->destinataires->count() }} acteur(s)
                </div>

                <div class="flex space-x-1.5">

                @if($document->status == 'document')
                    <button wire:click="viewDocument({{ $document->id }})" class="p-1.5 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 transition border border-transparent hover:border-blue-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>

                    <button wire:click="editMemo({{ $document->id }})" class="p-2 rounded-full bg-yellow-100 text-yellow-600 hover:bg-yellow-200 transition duration-150 ease-in-out" title="Modifier">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </button>

                     <button wire:click="openSendModal({{ $document->id }})" class="p-2 rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition" title="Transmettre / Valider">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </button>
                    
                    <button wire:click="openHistoryModal({{ $document->id }})" class="p-1.5 rounded-md bg-gray-50 text-gray-600 hover:bg-gray-100 transition border border-transparent hover:border-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </button>
                @elseif($document->status == 'pending')
                    <button wire:click="viewDocument({{ $document->id }})" class="p-1.5 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 transition border border-transparent hover:border-blue-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>

                    <button wire:click="openHistoryModal({{ $document->id }})" class="p-1.5 rounded-md bg-gray-50 text-gray-600 hover:bg-gray-100 transition border border-transparent hover:border-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </button>
                @elseif($document->status == 'rejected')
                    <button wire:click="viewDocument({{ $document->id }})" class="p-1.5 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 transition border border-transparent hover:border-blue-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>

                    <button wire:click="editMemo({{ $document->id }})" class="p-2 rounded-full bg-yellow-100 text-yellow-600 hover:bg-yellow-200 transition duration-150 ease-in-out" title="Modifier">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </button>

                    <button wire:click="openSendModal({{ $document->id }})" class="p-2 rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition" title="Transmettre / Valider">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </button>

                    <button wire:click="openHistoryModal({{ $document->id }})" class="p-1.5 rounded-md bg-gray-50 text-gray-600 hover:bg-gray-100 transition border border-transparent hover:border-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </button>
                @else
                    <button wire:click="viewDocument({{ $document->id }})" class="p-1.5 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 transition border border-transparent hover:border-blue-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                @endif




                </div>
            </div>
        </div>

    @empty
        <div class="col-span-3 text-center py-10 text-gray-500">
            Aucun mémo envoyé trouvé.
        </div>
    @endforelse

    @if($isOpen)
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            <!-- Overlay -->
            <div wire:click="closeModal" class="fixed inset-0 bg-gray-900 bg-opacity-90 transition-opacity backdrop-blur-sm cursor-pointer"></div>

            <!-- Toolbar -->
            <div class="fixed top-0 left-0 w-full z-50 pointer-events-none p-4 flex justify-between items-start print:hidden">
                <button wire:click="closeModal" class="pointer-events-auto bg-gray-800 text-white hover:bg-gray-700 px-6 py-2 rounded-full shadow-xl font-bold flex items-center gap-2 border border-gray-600">
                    <span>&larr; Retour</span>
                </button>
            </div>

            <!-- Conteneur Scrollable -->
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto pt-20 pb-10">
                <div class="flex min-h-full items-start justify-center p-4 text-center sm:p-0">
                    
                    <div class="relative flex flex-col items-center font-sans w-full max-w-[210mm]">

                        <!-- BOUTON TÉLÉCHARGER -->
                        <button onclick="prepareAndDownloadPDF()" type="button" class="mb-4 pointer-events-auto bg-red-600 text-white hover:bg-red-700 px-6 py-2.5 rounded-full shadow-lg font-bold flex items-center gap-3 border border-red-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Télécharger PDF</span>
                        </button>

                        <!-- ID GLOBAL POUR HTML2PDF -->
                        <div id="export-container">

                            <!-- PAGE 1 (VOTRE DESIGN ORIGINAL) -->
                            <div id="page-1" class="page-a4 bg-white w-[210mm] h-[297mm] shadow-2xl p-[10mm] text-black text-[13px] leading-snug relative text-left mx-auto mb-8">
                                
                                <!-- CADRE DORÉ -->
                                <div class="gold-frame border-[3px] border-[#D4AF37] rounded-tr-[60px] rounded-bl-[60px] p-8 h-full flex flex-col relative">

                                    <!-- EN-TÊTE LOGO + MEMORANDUM -->
                                    <div class="header-section flex flex-col items-center justify-center mb-6 text-center">
                                        <div class="mb-2">
                                            <div class="w-17 h-16 flex items-center justify-center mx-auto mb-1">
                                                <img src="{{ asset('images/logo.jpg') }}" alt="logo" class="w-full h-full object-contain">
                                            </div>
                                        </div>
                                        <h2 class="font-bold text-xs uppercase text-gray-800">{{ $user_entity_name }}</h2>
                                        <h1 class="font-['Arial'] font-extrabold text-2xl uppercase mt-2 italic inline-block">
                                            Memorandum
                                        </h1>
                                    </div>

                                    <!-- VOTRE TABLEAU ORIGINAL (INTACT) -->
                                    <div id="recipient-table" class="mb-6 text-sm w-full">
                                        <style>
                                            .checkbox-square { display: inline-block; width: 12px; height: 12px; border: 1px solid black; margin-right: 6px; vertical-align: middle; }
                                        </style>
                                        
                                        <!-- LIGNE D'ALIGNEMENT -->
                                        <div class="flex w-full text-[13px] font-bold font-['Arial'] pb-1 text-black">
                                            <div class="w-[35%]"></div>
                                            <div class="w-[30%] text-center">Prière de :</div>
                                            <div class="w-[35%] pl-8">Destinataires :</div>
                                        </div>
                                        
                                        <!-- TABLEAU COMPLET -->
                                        <table class="w-full border-collapse border border-black text-[13px] font-['Arial'] text-black">
                                            <!-- LIGNE 1 : Faire le nécessaire (VERT) -->
                                            @php $recipients1 = collect($recipientsByAction['Faire le nécessaire'] ?? []); @endphp
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold w-[35%] align-top">Date : {{ $date }}</td>
                                                <td class="border border-black p-1 pl-2 w-[30%]">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients1->count() > 0 ? 'bg-green-600' : '' }}"></span> 
                                                    <span class="{{ $recipients1->count() > 0 ? 'font-bold' : '' }}">Faire le nécessaire</span>
                                                </td>
                                                <td class="border border-black p-1 text-center w-[35%] {{ $recipients1->count() > 0 ? 'font-bold bg-gray-50' : '' }}">
                                                    @if($recipients1->count() > 0)
                                                        {{-- CORRECTION : On retire ['entity'] car $m est déjà l'entité --}}
                                                        {{ $recipients1->map(fn($m) => $m['acronym'] ?? $m['title'])->join(', ') }}
                                                    @else
                                                        &nbsp;
                                                    @endif
                                                </td>
                                            </tr>
                                            <!-- LIGNE 2 : Prendre connaissance (BLEU) -->
                                            @php $recipients2 = collect($recipientsByAction['Prendre connaissance'] ?? []); @endphp
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold align-top">N° : #</td>
                                                <td class="border border-black p-1 pl-2">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients2->count() > 0 ? 'bg-blue-600' : '' }}"></span> 
                                                    <span class="{{ $recipients2->count() > 0 ? 'font-bold' : '' }}">Prendre connaissance</span>
                                                </td>
                                                <td class="border border-black p-1 text-center {{ count($recipients2) > 0 ? 'font-bold bg-gray-50' : '' }}">
                                                    @if(count($recipients2) > 0)
                                                        {{-- On utilise collect() pour transformer le tableau en collection et faciliter le map/join --}}
                                                        {{ collect($recipients2)->map(fn($m) => $m['acronym'] ?? $m['title'])->join(', ') }}
                                                    @else 
                                                        &nbsp; 
                                                    @endif
                                                </td>
                                            </tr>
                                            <!-- LIGNE 3 : Prendre position (ORANGE) -->
                                            @php $recipients3 = collect($recipientsByAction['Prendre position'] ?? []); @endphp
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold align-top">Emetteur : {{ $user_entity_name_acronym }} </td>
                                                <td class="border border-black p-1 pl-2">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients3->count() > 0 ? 'bg-orange-500' : '' }}"></span> 
                                                    <span class="{{ $recipients3->count() > 0 ? 'font-bold' : '' }}">Prendre position</span>
                                                </td>
                                                <td class="border border-black p-1 text-center {{ count($recipients3) > 0 ? 'font-bold bg-gray-50' : '' }}">
                                                    @if(count($recipients3) > 0) 
                                                        {{-- On utilise collect() pour pouvoir utiliser map() et join() sur le tableau --}}
                                                        {{ collect($recipients3)->map(fn($m) => $m['acronym'] ?? $m['title'])->join(', ') }} 
                                                    @else 
                                                        &nbsp; 
                                                    @endif
                                                </td>
                                            </tr>
                                            <!-- LIGNE 4 : Décider (JAUNE) -->
                                            @php $recipients4 = collect($recipientsByAction['Décider'] ?? []); @endphp
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold align-top">Service : {{ $user_service }}</td>
                                                <td class="border border-black p-1 pl-2">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients4->count() > 0 ? 'bg-yellow-400' : '' }}"></span> 
                                                    <span class="{{ $recipients4->count() > 0 ? 'font-bold' : '' }}">Décider</span>
                                                </td>
                                                <td class="border border-black p-1 text-center {{ count($recipients4) > 0 ? 'font-bold bg-gray-50' : '' }}">
                                                    @if(count($recipients4) > 0) 
                                                        {{ collect($recipients4)->map(fn($m) => $m['acronym'] ?? $m['title'])->join(', ') }} 
                                                    @else 
                                                        &nbsp; 
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- OBJET & CONCERNE -->
                                    <div class="mb-4">
                                        <div class="mb-6">
                                            <p class="mb-1"><span class="font-bold text-[15px] underline">Objet :</span> <span class="uppercase font-bold"> {{$object}} </span></p>
                                        </div>
                                        <div class="mb-6">
                                            <p class="mb-1"><span class="font-bold text-[15px] underline">Concerne :</span> <span class="lowercase">{{ $concern }} </span></p>
                                        </div>
                                    </div>

                                    <!-- CORPS DU TEXTE (Identifié pour le JS) -->
                                    <div id="content-area" class="flex-grow px-2">
                                        <div class="text-justify space-y-3 text-[14px] leading-relaxed font-serif text-gray-900">
                                            {!! $content !!}
                                        </div>
                                    </div>

                                 
                                            
                                            {{-- AJOUT ICI : PIED DE PAGE AVEC QR CODE CENTRÉ               --}}
                                            <div class="absolute bottom-4 left-0 w-full flex flex-col items-center justify-center">

                                                    <!-- Le QR Code (Taille réduite à 50) -->
                                                @if(isset($qr_code) && !empty($qr_code))
                                                    <div class="bg-white p-0.5 border border-gray-200 inline-block">
                                                        {{ QrCode::size(50)->generate(route('memo.verify', $qr_code)) }}
                                                    </div>
                                                @endif

                                            </div>

                                        </div>
                                        <div class="text-right text-[10px] text-gray-500 italic mt-4">FOR-ME-07-V1</div>
                                        
                                 

                                </div> 
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isOpen2)
            <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
                <!-- L'arrière-plan sombre (Overlay) -->
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

                <!-- Le conteneur de positionnement (Scrollable) -->
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    
                    <!-- CORRECTION 1 : 'items-center' pour centrer verticalement -->
                    <!-- 'min-h-full' assure que le conteneur prend toute la hauteur pour permettre le centrage -->
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        
                        <!-- Le panneau du contenu -->
                        <!-- CORRECTION 2 : J'ai retiré 'mt-1000' qui cassait tout -->
                        <!-- J'ai gardé 'sm:my-8' pour une petite marge en haut/bas sur ordinateur -->
                        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">
                    
                            <form wire:submit.prevent="save">
                                <!-- Corps du modal -->
                                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">
                                            Modifier Votre Memo
                                        </h3>
                                        <!-- Bouton croix pour fermer (Optionnel mais recommandé) -->
                                        <button type="button" wire:click="closeModalDeux()" class="text-gray-400 hover:text-gray-500">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <!-- Champ Object -->
                                    <div class="mb-4">
                                        <label for="object" class="block text-sm font-medium text-gray-700 mb-1">Objet</label>
                                        <input type="text"  wire:model="object" id="object" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 py-2 px-3 text-gray-900">
                                        @error('object') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Champ Concern -->
                                    <div class="mb-4">
                                        <label for="object" class="block text-sm font-medium text-gray-700 mb-1">Concerne</label>
                                        <input type="text"  wire:model="concern" id="concern" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 py-2 px-3 text-gray-900">
                                        @error('concern') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    

                                    <!-- Champ Contenu (Éditeur Riche) -->
                                    <div class="mb-4">
                                        <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Contenu</label>
                                        
                                        <div wire:ignore 
                                            class="rounded-md shadow-sm"
                                            x-data="{
                                                content: @entangle('content'),
                                                initQuill() {
                                                    const quill = new Quill(this.$refs.quillEditor, {
                                                        theme: 'snow',
                                                        placeholder: 'Rédigez votre mémo ici...',
                                                        modules: {
                                                            toolbar: [
                                                                // GROUPE 1 : Titres et Polices
                                                                [{ 'header': [1, 2, 3, false] }], // H1, H2, H3, Normal

                                                                // GROUPE 2 : Formatage de base
                                                                ['bold', 'italic', 'underline', 'strike'], // Gras, Italique, Souligné, Barré
                                                                
                                                                // GROUPE 3 : Couleurs
                                                                [{ 'color': [] }, { 'background': [] }], // Couleur du texte & Surlignage

                                                                // GROUPE 4 : Listes et Indentation
                                                                [{ 'list': 'ordered'}, { 'list': 'bullet' }], 
                                                                [{ 'indent': '-1'}, { 'indent': '+1' }], // Diminuer/Augmenter le retrait

                                                                // GROUPE 5 : Alignement
                                                                [{ 'align': [] }], 

                                                                // GROUPE 6 : Insertion (Lien)
                                                                ['link'], // Outil pour insérer un lien hypertexte

                                                                // GROUPE 7 : Nettoyage
                                                                ['clean'] // Effacer le formatage
                                                            ]
                                                        }
                                                    });

                                                    // Charger le contenu initial s'il existe (édition)
                                                    if (this.content) {
                                                        quill.root.innerHTML = this.content;
                                                    }

                                                    // Synchroniser Quill vers Livewire quand on tape
                                                    quill.on('text-change', () => {
                                                        this.content = quill.root.innerHTML;
                                                    });
                                                }
                                            }"
                                            x-init="initQuill()"
                                        >
                                            <!-- Le conteneur visuel de l'éditeur -->
                                            <div class="bg-white border border-gray-300 rounded-md overflow-hidden focus-within:border-yellow-500 focus-within:ring-1 focus-within:ring-yellow-500 transition-all duration-200">
                                                <!-- La zone de saisie Quill -->
                                                 <div x-ref="quillEditor" class="min-h-[150px] max-h-[300px] overflow-y-auto text-gray-800 text-base font-sans"></div>
                                            </div>
                                        </div>

                                        @error('content') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- CSS Personnalisé pour harmoniser la barre d'outils avec votre thème -->
                                    <style>
                                        /* Ajustement de la barre d'outils Quill pour qu'elle soit jolie */
                                        .ql-toolbar.ql-snow {
                                            border: none;
                                            border-bottom: 1px solid #e5e7eb;
                                            background-color: #f9fafb;
                                        }
                                        .ql-container.ql-snow {
                                            border: none;
                                        }
                                        /* Police et taille dans l'éditeur */
                                        .ql-editor {
                                            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                                            font-size: 0.95rem; 
                                            line-height: 1.5;
                                        }
                                    </style>
                                </div>
                                
                                <!-- Pied du modal (Boutons) -->
                                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-100">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 sm:ml-3 sm:w-auto">
                                        Modifier
                                    </button>
                                    <button type="button" wire:click="closeModalDeux()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                        Annuler
                                    </button>
                                </div>
                            </form>

                        </div>
                        
                    </div>
                </div>
            </div>
         @endif

    @if($isHistoryOpen)
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div wire:click="closeHistoryModal" class="fixed inset-0 bg-gray-900 bg-opacity-90 transition-opacity backdrop-blur-sm"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    
                    <!-- Largeur augmentée pour la timeline (max-w-4xl) -->
                    <div class="relative transform overflow-hidden rounded-lg bg-slate-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-slate-700">
                        
                        <!-- Header du Modal -->
                        <div class="bg-slate-800 px-4 py-4 border-b border-slate-700 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-white">Circuit de traitement</h3>
                            <button wire:click="closeHistoryModal" class="text-slate-400 hover:text-white">Fermer [X]</button>
                        </div>

                        <div class="p-6 bg-slate-900">
                            
                            <!-- DÉBUT DU COMPOSANT CIRCUIT TIMELINE (Adapté) -->
                            <div class="w-full mx-auto relative font-sans text-slate-200">

                                <!-- Ligne Verticale Centrale -->
                                <div class="absolute left-8 md:left-1/2 top-0 bottom-0 w-0.5 bg-gradient-to-b from-slate-700 via-slate-600 to-slate-800 md:-translate-x-1/2"></div>

                                @foreach($memoHistory as $index => $step)
                                    <!-- 
                                        Logique d'alternance : 
                                        Si index pair ($index % 2 == 0) -> Droite (flex-row)
                                        Si index impair -> Gauche (flex-row-reverse)
                                    -->
                                    <div class="relative flex flex-col md:{{ $index % 2 == 0 ? 'flex-row' : 'flex-row-reverse' }} items-center justify-between mb-12 group">
                                        
                                        <!-- Espaceur -->
                                        <div class="hidden md:block w-5/12"></div>
                                        
                                        <!-- Point de connexion -->
                                        <div class="absolute left-8 md:left-1/2 -translate-x-1/2 w-4 h-4 rounded-full border-4 border-slate-900 z-10 
                                            {{ $step->action_type == 'rejection' ? 'bg-red-500 shadow-[0_0_10px_#ef4444]' : 'bg-emerald-500 shadow-[0_0_10px_#10b981]' }}">
                                        </div>

                                        <!-- Contenu -->
                                        <div class="w-[calc(100%-4rem)] ml-16 md:ml-0 md:w-5/12 relative">
                                            
                                            <!-- Trait de liaison -->
                                            <div class="absolute top-4 -left-8 w-8 md:w-10 h-0.5 
                                                {{ $step->action_type == 'rejection' ? 'bg-red-500/50' : 'bg-emerald-500/50' }}
                                                md:{{ $index % 2 == 0 ? '-left-10' : 'left-auto -right-10' }}">
                                            </div>
                                            
                                            <!-- Carte -->
                                            <div class="p-5 rounded-xl backdrop-blur-sm transition-colors border-l-4 shadow-lg
                                                {{ $step->action_type == 'rejection' 
                                                    ? 'bg-red-900/20 border-red-500 hover:bg-red-900/30' 
                                                    : 'bg-slate-800/80 border-emerald-500 hover:bg-slate-800' }}">
                                                
                                                <div class="flex justify-between items-start mb-2">
                                                    <span class="{{ $step->action_type == 'rejection' ? 'text-red-400' : 'text-emerald-400' }} text-xs font-bold tracking-widest uppercase">
                                                        {{ $step->created_at->format('d/m/Y à H:i') }}
                                                    </span>
                                                    
                                                    <!-- Badge Action -->
                                                    @if($step->action_type == 'rejection')
                                                        <span class="px-2 py-0.5 rounded bg-red-500/20 text-red-300 text-[10px] border border-red-500/50">Rejeté</span>
                                                    @elseif($step->action_type == 'validation')
                                                        <span class="px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 text-[10px] border border-purple-500/50">Diffusé</span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 text-[10px] border border-emerald-500/50">Transmis</span>
                                                    @endif
                                                </div>

                                                <h3 class="text-lg font-bold text-white mt-1">
                                                    {{ $step->actor->first_name }} {{ $step->actor->last_name }}
                                                </h3>
                                                <p class="text-xs text-slate-500 uppercase mb-3">{{ $step->actor->poste ?? 'Utilisateur' }}</p>
                                                
                                                @if($step->comment)
                                                    <div class="bg-slate-900/50 p-3 rounded border border-slate-700/50">
                                                        <p class="text-slate-300 text-sm italic">"{{ $step->comment }}"</p>
                                                    </div>
                                                @else
                                                    <p class="text-slate-500 text-xs italic">Aucun commentaire.</p>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                            <!-- FIN DU COMPOSANT -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isSendOpen)
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        
                        <form wire:submit.prevent="sendMemo">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Transmettre le Mémo</h3>

                                <!-- Sélection du destinataire -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Envoyer à :</label>
                                    <select wire:model="next_user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 py-2 px-3 border">
                                        
                                        <option value="">-- Choisir un destinataire --</option>
                                        @foreach($usersList as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->first_name }} {{ $user->last_name }} 
                                                ({{ $user->poste ?? 'Employé' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('next_user_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                        

                                <!-- Commentaire -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Note / Commentaire (Optionnel)</label>
                                    <textarea wire:model="workflow_comment" rows="3" class="w-full rounded-md border-gray-300 shadow-sm border p-2"></textarea>
                                </div>
                                
                                @if($author_memo == Auth::id())


                                @else
                                    
                                    <!-- Visa -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">VISE</label>
                                        
                                        <div class="flex items-center space-x-6">
                                            <!-- Option Favorable -->
                                            <div class="flex items-center">
                                                <input wire:model="action" 
                                                    id="visa_favorable" 
                                                    name="action" 
                                                    type="radio" 
                                                    value="Vue" 
                                                    class="h-4 w-4 text-yellow-600 border-gray-300 focus:ring-yellow-500 cursor-pointer">
                                                <label for="visa_favorable" class="ml-2 block text-sm text-gray-700 cursor-pointer">
                                                    Vue
                                                </label>
                                            </div>

                                            <!-- Option Défavorable -->
                                            <div class="flex items-center">
                                                <input wire:model="action" 
                                                    id="visa_defavorable" 
                                                    name="action" 
                                                    type="radio" 
                                                    value="Vue & D'accord" 
                                                    class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500 cursor-pointer">
                                                <label for="visa_defavorable" class="ml-2 block text-sm text-gray-700 cursor-pointer">
                                                    Vue & D'accord
                                                </label>
                                            </div>
                                        </div>
                                        @error('action') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                @endif
                                

                                

                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit" class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:ml-3 sm:w-auto">
                                    
                                       Transmettre
                                  
                                </button>
                                <button type="button" wire:click="closeSendModal" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
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


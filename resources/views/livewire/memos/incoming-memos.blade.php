<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    @forelse($memos as $document)

        @if($document->status == 'rejected')
        

            <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-12">
                <div class="text-gray-400 mb-2">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <p class="text-gray-500 text-lg">Votre boîte de réception est vide.</p>
                <p class="text-gray-400 text-sm">Aucun mémo en attente de traitement.</p>
            </div>

        @else
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 flex flex-col justify-between relative overflow-hidden h-full">
            
            <!-- Décoration coin -->
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 opacity-50 transform rotate-45 translate-x-12 -translate-y-12 flex items-center justify-center"></div>
            <button 
                wire:click="toggleFavorite({{ $document->id }})" 
                class="absolute top-2 right-2 z-10 p-1.5 rounded-full hover:bg-gray-50 transition-colors duration-200 focus:outline-none group"
                title="{{ $document->is_favorited ? 'Retirer des favoris' : 'Ajouter aux favoris' }}">
                
                
                    <!-- Étoile PLEINE (Jaune) -->
                    <svg class="w-6 h-6 text-yellow-400 fill-current" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
            
                    <!-- Étoile VIDE (Grise, devient jaune au survol) >
                    <svg class="w-6 h-6 text-gray-300 group-hover:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg -->
            </button>

            <div>
                <!-- EN-TÊTE DE LA CARTE : Le Document -->
                <div class="mb-4">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                        Réf: #
                    </span>
                    <h3 class="text-lg font-bold text-gray-800 leading-tight mt-1 truncate" title="{{ $document->object }}">
                        {{ $document->object }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Créé le {{ $document->created_at->format('d/m/Y à H:i') }}
                    </p>
                </div>

               

        
                <!-- CORPS DE LA CARTE : La liste des destinataires -->
                <div class="bg-gray-50 rounded-lg p-3 mb-4 max-h-40 overflow-y-auto custom-scrollbar">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2 border-b border-gray-200 pb-1">
                        Destinataires & Actions
                    </h4>
                    
                    <ul class="space-y-2">
                        @foreach($document->destinataires as $destinataire)
                            <li class="flex justify-between items-start text-sm">
                                <!-- Nom de l'entité -->
                                <span class="font-medium text-gray-700 w-1/2">
                                    {{ $destinataire->title  ?? 'Entité inconnue' }}
                                        <span class="text-xs text-gray-400">{{ $destinataire->acronym }}</span>
                                </span>
                                
                                <!-- Action demandée -->
                                <span class="text-xs bg-white border border-gray-200 px-2 py-0.5 rounded text-purple-600 font-semibold w-1/2 text-right">
                                    {{ $destinataire->pivot->action }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- PIED DE LA CARTE -->
            <div class="flex justify-between items-center pt-4 border-t border-gray-100 mt-auto">
                <div class="text-xs text-gray-400">
                    {{ $document->destinataires->count() }} destinataire(s)
                </div>

                <!-- Boutons d'action sur le document global -->
                <div class="flex space-x-2">

                    @if(auth()->user()->poste == "Sous-Directeur" || auth()->user()->poste == "Directeur")

                        @if(auth()->user()->poste == "Sous-Directeur")

                            @if($document->signature_sd == null)

                                <button wire:click="viewDocument({{ $document->id }})" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition" title="Voir le document">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>    
                                </button>

                                <button wire:click="signDocument({{ $document->id }}, '{{ auth()->user()->poste }}')"   class="p-2 rounded-full bg-purple-100 text-purple-600 hover:bg-purple-200 transition ring-2 ring-purple-300 ring-opacity-50" title="Signer numériquement (Générer QR)">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 17h4.01M16 3h5m-3 12v3m0 0h6m-6 0H9a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-9a2 2 0 00-2-2h-6zM5 12a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2v-6a2 2 0 00-2-2H5z" />
                                    </svg>
                                </button>
                            @else
                                <button wire:click="viewDocument({{ $document->id }})" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition" title="Voir le document">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>    
                                </button>

                                <button wire:click="openSendModal({{ $document->id }})" class="p-2 rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition" title="Transmettre / Valider">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                </button>
                    
                             @endif

                        @endif


                        @if(auth()->user()->poste == "Directeur")

                            @if($document->signature_dir == null)

                                <button wire:click="viewDocument({{ $document->id }})" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition" title="Voir le document">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>    
                                </button>

                                <button wire:click="signDocument({{ $document->id }}, '{{ auth()->user()->poste }}')"   class="p-2 rounded-full bg-purple-100 text-purple-600 hover:bg-purple-200 transition ring-2 ring-purple-300 ring-opacity-50" title="Signer numériquement (Générer QR)">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 17h4.01M16 3h5m-3 12v3m0 0h6m-6 0H9a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-9a2 2 0 00-2-2h-6zM5 12a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2v-6a2 2 0 00-2-2H5z" />
                                    </svg>
                                </button>
                            @else
                                <button wire:click="viewDocument({{ $document->id }})" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition" title="Voir le document">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>    
                                </button>

                                <button 
                                    wire:click="qrDocument({{ $document->id }})" 
                                    wire:confirm="Générer le QR Code de signature ?"
                                    class="p-2 rounded-full bg-purple-100 text-purple-600 hover:bg-purple-200 transition" 
                                    title="Générer QR Code">
                                    
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                </button>     
                    
                             @endif

                        @endif

                    @else

                        @if($document->user->manager_id == auth()->user()->id || $document->user->manager_replace_id == auth()->user()->id)
                            <button wire:click="viewDocument({{ $document->id }})" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition" title="Voir le document">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>    
                            </button>
                        
                            <button wire:click="openSendModal({{ $document->id }})" class="p-2 rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition" title="Transmettre / Valider">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </button>

                            <button wire:click="openRejectModal({{ $document->id }})" class="p-2 rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition" title="Rejeter">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        @else
                            <button wire:click="viewDocument({{ $document->id }})" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition" title="Voir le document">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>    
                            </button>

                            <button wire:click="openSendModal({{ $document->id }})" class="p-2 rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition" title="Transmettre / Valider">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </button>
                        @endif  
                     
                    @endif    
                        
                  
                </div>

                

                
                    
              

                <!-- BADGE DE STATUT (En haut à gauche par exemple) -->
                <div class="absolute top-0 left-0 p-2">
                    @if($document->status == 'rejected')
                        <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded">Retourné (Rejet)</span>
                    @else
                        <span class="bg-orange-100 text-orange-600 text-xs font-bold px-2 py-1 rounded">À traiter</span>
                    @endif
                </div>
            </div>

        </div>
     @endif
   
    @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-12">
                <div class="text-gray-400 mb-2">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <p class="text-gray-500 text-lg">Votre boîte de réception est vide.</p>
                <p class="text-gray-400 text-sm">Aucun mémo en attente de traitement.</p>
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
                                                <td class="border border-black p-1 pl-2 font-bold align-top">N° : 298/DGR/SDGR/WT</td>
                                                <td class="border border-black p-1 pl-2">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients2->count() > 0 ? 'bg-blue-600' : '' }}"></span> 
                                                    <span class="{{ $recipients2->count() > 0 ? 'font-bold' : '' }}">Prendre connaissance</span>
                                                </td>
                                                <td class="border border-black p-1 text-center {{ $recipients2->count() > 0 ? 'font-bold bg-gray-50' : '' }}">
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
                                                <td class="border border-black p-1 pl-2 font-bold align-top">Emetteur : {{ $user_entity_name_acronym }}</td>
                                                <td class="border border-black p-1 pl-2">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients3->count() > 0 ? 'bg-orange-500' : '' }}"></span> 
                                                    <span class="{{ $recipients3->count() > 0 ? 'font-bold' : '' }}">Prendre position</span>
                                                </td>
                                                <td class="border border-black p-1 text-center {{ $recipients3->count() > 0 ? 'font-bold bg-gray-50' : '' }}">
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
                                                <td class="border border-black p-1 text-center {{ $recipients4->count() > 0 ? 'font-bold bg-gray-50' : '' }}">
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
                                            <p class="mb-1"><span class="font-bold text-[15px] underline">Concerne :</span> <span class="lowercase">{{ $concern }}</span></p>
                                        </div>
                                    </div>

                                    <!-- CORPS DU TEXTE (Identifié pour le JS) -->
                                    <div id="content-area" class="flex-grow px-2">
                                        <div class="text-justify space-y-3 text-[14px] leading-relaxed font-serif text-gray-900">
                                            {!! $content !!}
                                        </div>
                                    </div>

                                    

                                </div> 
                            </div>

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
                                        <option value="">Sélectionner un collaborateur...</option>
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
                                    <textarea wire:model="comment" rows="3" class="w-full rounded-md border-gray-300 shadow-sm border p-2"></textarea>
                                </div>

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


    @if($isRejectOpen)
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border-2 border-red-100">
                        
                        <form wire:submit.prevent="confirmRejection">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                
                                <!-- En-tête rouge -->
                                <div class="sm:flex sm:items-start mb-4">
                                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                        <h3 class="text-base font-semibold leading-6 text-gray-900">Rejeter le mémo</h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500">Vous êtes sur le point de renvoyer ce mémo à son auteur. Veuillez indiquer le motif ci-dessous.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Champ Motif -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Motif du rejet <span class="text-red-500">*</span></label>
                                    <textarea 
                                        wire:model="rejection_comment" 
                                        rows="4" 
                                        class="w-full rounded-md border-red-300 shadow-sm focus:border-red-500 focus:ring-red-500 py-2 px-3 border text-gray-900 placeholder-gray-400"
                                        placeholder="Ex: Il manque la pièce jointe, veuillez corriger la date..."
                                    ></textarea>
                                    @error('rejection_comment') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                                </div>

                            </div>

                            <!-- Boutons -->
                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                                    Confirmer le rejet
                                </button>
                                <button type="button" wire:click="closeRejectModal" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
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
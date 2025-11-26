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
                        @foreach($document->memos as $attribution)
                            <li class="flex justify-between items-start text-sm">
                                <!-- Nom de l'entité -->
                                <span class="font-medium text-gray-700 w-1/2">
                                    {{ $attribution->entity->name ?? 'Entité inconnue' }}
                                    @if($attribution->entity->acronym)
                                        <span class="text-xs text-gray-400">({{ $attribution->entity->acronym }})</span>
                                    @endif
                                </span>
                                
                                <!-- Action demandée -->
                                <span class="text-xs bg-white border border-gray-200 px-2 py-0.5 rounded text-purple-600 font-semibold w-1/2 text-right">
                                    {{ $attribution->action }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- PIED DE LA CARTE -->
            <div class="flex justify-between items-center pt-4 border-t border-gray-100 mt-auto">
                <div class="text-xs text-gray-400">
                    {{ $document->memos->count() }} destinataire(s)
                </div>

                <!-- Boutons d'action sur le document global -->
                <div class="flex space-x-2">
                     <button wire:click="viewDocument({{ $document->id }})" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition" title="Voir le document">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                    
                    <!-- BOUTON ENVOYER / TRAITER -->
                    <!-- Visible seulement si JE SUIS le détenteur actuel OU si c'est un brouillon -->
                    @if($document->current_holder_id == Auth::id() || ($document->user_id == Auth::id() && $document->status == 'brouillon'))
                        <button wire:click="openSendModal({{ $document->id }})" class="p-2 rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition" title="Transmettre / Valider">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        </button>
                        
                        <!-- BOUTON REJETER (Si je ne suis pas l'auteur) -->
                        @if($document->user_id != Auth::id())
                            <button wire:click="openRejectModal({{ $document->id }})" class="p-2 rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition" title="Rejeter">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        @endif
                    @endif
                </div>

                {{-- ========================================================== --}}
                {{--  NOUVEAU : BOUTON SIGNER (Appose le QR Code)               --}}
                {{-- ========================================================== --}}

                @php
                    $user = Auth::user();
                    // Vérifie si l'utilisateur est Sous-Directeur et n'a pas encore signé
                    $canSignSD = ($user->poste === 'Sous-Directeur' && is_null($document->signature_sd));
                    // Vérifie si l'utilisateur est Directeur et n'a pas encore signé
                    $canSignDir = ($user->poste === 'Directeur' && is_null($document->signature_dir));
                    
                    // Il ne peut signer que s'il détient le dossier
                    $isHolder = ($document->current_holder_id == $user->id);
                @endphp

                @if($isHolder && ($canSignSD || $canSignDir))
                    <button 
                        wire:click="signDocument({{ $document->id }})"
                        wire:confirm="Êtes-vous sûr de vouloir signer numériquement ce document ? Cela générera un QR Code unique."
                        class="p-2 rounded-full bg-purple-100 text-purple-600 hover:bg-purple-200 transition ring-2 ring-purple-300 ring-opacity-50" 
                        title="Signer numériquement (Générer QR)">
                        <!-- Icône QR Code / Stylo -->
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 17h4.01M16 3h5m-3 12v3m0 0h6m-6 0H9a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-9a2 2 0 00-2-2h-6zM5 12a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2v-6a2 2 0 00-2-2H5z" />
                        </svg>
                    </button>
                @endif
                {{-- ========================================================== --}}

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
                                        <h2 class="font-bold text-xs uppercase text-gray-800">{{ $user_entity }}</h2>
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
                                                    @if($recipients1->count() > 0) {{ $recipients1->map(fn($m) => $m['entity']['acronym'] ?? $m['entity']['name'])->join(', ') }} @else &nbsp; @endif
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
                                                    @if($recipients2->count() > 0) {{ $recipients2->map(fn($m) => $m['entity']['acronym'] ?? $m['entity']['name'])->join(', ') }} @else &nbsp; @endif
                                                </td>
                                            </tr>
                                            <!-- LIGNE 3 : Prendre position (ORANGE) -->
                                            @php $recipients3 = collect($recipientsByAction['Prendre position'] ?? []); @endphp
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold align-top">Emetteur : {{ $user_first_name }} {{ $user_last_name }}</td>
                                                <td class="border border-black p-1 pl-2">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients3->count() > 0 ? 'bg-orange-500' : '' }}"></span> 
                                                    <span class="{{ $recipients3->count() > 0 ? 'font-bold' : '' }}">Prendre position</span>
                                                </td>
                                                <td class="border border-black p-1 text-center {{ $recipients3->count() > 0 ? 'font-bold bg-gray-50' : '' }}">
                                                    @if($recipients3->count() > 0) {{ $recipients3->map(fn($m) => $m['entity']['acronym'] ?? $m['entity']['name'])->join(', ') }} @else &nbsp; @endif
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
                                                    @if($recipients4->count() > 0) {{ $recipients4->map(fn($m) => $m['entity']['acronym'] ?? $m['entity']['name'])->join(', ') }} @else &nbsp; @endif
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
                                            <p class="mb-1"><span class="font-bold text-[15px] underline">Concerne :</span> <span class="lowercase">{{ $object }} </span></p>
                                        </div>
                                    </div>

                                    <!-- CORPS DU TEXTE (Identifié pour le JS) -->
                                    <div id="content-area" class="flex-grow px-2">
                                        <div class="text-justify space-y-3 text-[14px] leading-relaxed font-serif text-gray-900">
                                            {!! $content !!}
                                        </div>
                                    </div>

                                    <!-- SIGNATURES (Identifié pour le JS) -->
                                    <div id="signatures-section" class="mt-8 pt-4">
                                        <div class="flex justify-between items-end px-8 mb-2">
                                            <!-- Signature SD -->
                                            <div class="relative text-center w-1/3">
                                                <div class="absolute inset-0 flex items-center justify-center opacity-10 pointer-events-none">
                                                    <div class="border-4 border-gray-800 w-24 h-24 rounded-full flex items-center justify-center -rotate-12">
                                                        <span class="font-bold text-xs uppercase tracking-widest text-gray-800">Signé</span>
                                                    </div>
                                                </div>
                                                <div class="h-16 flex items-end justify-center pb-2">
                                                    <span class="font-bold text-lg text-gray-800 font-serif italic">{{$signature_sd}}</span>
                                                </div>
                                                <div class="text-[10px] font-bold text-gray-600 uppercase tracking-wider border-t border-gray-400 pt-2">Sous Directeur</div>
                                            </div>
                                            
                                            <!-- Signature DIR -->
                                            <div class="relative text-center w-1/3">
                                                <div class="absolute inset-0 flex items-center justify-center opacity-80 pointer-events-none">
                                                    <div class="border-[3px] border-blue-900 w-32 h-16 rounded flex flex-col items-center justify-center -rotate-6 bg-white/50 backdrop-blur-[1px]">
                                                        <span class="font-bold text-[10px] text-blue-900 uppercase">Commercial Bank</span>
                                                        <span class="font-extrabold text-sm text-blue-900 uppercase tracking-widest">APPROUVÉ</span>
                                                        <span class="text-[8px] text-blue-900"> date('d/m/Y') </span>
                                                    </div>
                                                </div>
                                                <div class="h-16 flex items-end justify-center pb-2">
                                                    <span class="font-bold text-lg text-black z-10">{{$signature_dir}}</span>
                                                </div>
                                                <div class="text-[10px] font-bold text-gray-600 uppercase tracking-wider border-t border-gray-400 pt-2">Directeur</div>
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

                                
                                <!-- CAS SPÉCIAL : SECRÉTAIRE -->
                                @if(strtolower(Auth::user()->poste) === 'secretaire')
                                    <div class="mb-4 bg-yellow-50 p-3 rounded border border-yellow-200">
                                        <label class="block text-sm font-bold text-yellow-800 mb-1">Numéro de Référence (Enregistrement)</label>
                                        <input type="text" wire:model="reference_input" placeholder="Ex: 2024/001/DG" class="w-full rounded-md border-gray-300 shadow-sm">
                                        @error('reference_input') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                @endif

                                <!-- Commentaire -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Note / Commentaire (Optionnel)</label>
                                    <textarea wire:model="comment" rows="3" class="w-full rounded-md border-gray-300 shadow-sm border p-2"></textarea>
                                </div>

                                

                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit" class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:ml-3 sm:w-auto">
                                    @if(strtolower(Auth::user()->poste) === 'secretaire')
                                        Enregistrer & Diffuser
                                    @else
                                        Transmettre
                                    @endif
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
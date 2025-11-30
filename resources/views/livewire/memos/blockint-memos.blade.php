<!-- Conteneur principal avec effet de profondeur et reliure -->
<div class="relative bg-[#fdfbf7] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.3)] rounded-lg overflow-hidden border border-gray-300 min-h-[600px] font-sans">
    
    <!-- Décoration : Reliure à gauche (Effet Classeur) -->
    <div class="absolute left-0 top-0 bottom-0 w-12 bg-gray-100 border-r border-gray-300 flex flex-col items-center pt-8 space-y-12 z-10">
        <!-- Trous du classeur -->
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
    </div>

    <!-- Contenu du registre (Décalé vers la droite à cause de la reliure) -->
    <div class="ml-12">
        
        <!-- En-tête du registre -->
        <div class="bg-slate-800 text-white px-8 py-5 flex justify-between items-center border-b-4 border-slate-600">
            <div>
                <h2 class="text-2xl font-serif tracking-widest uppercase font-bold">Registre Chrono</h2>
                <p class="text-slate-400 text-xs uppercase tracking-wide">Arriver Courrier / Mémos Entrants</p>
            </div>
            <!-- Petit effet "Page X" -->
            <div class="border border-slate-500 px-3 py-1 rounded text-sm text-slate-300 font-mono">
                REF: {{ date('Y') }}
            </div>
        </div>

        <!-- Tableau "Grille Papier" -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs uppercase text-slate-500 border-b-2 border-slate-800 bg-slate-100">
                        <th class="px-4 py-3 border-r border-slate-300 w-24 text-center font-bold">Date</th>
                        <th class="px-4 py-3 border-r border-slate-300 w-32 font-bold">N° Ordre</th>
                        <th class="px-4 py-3 border-r border-slate-300 w-48 font-bold">Concerne</th>
                        <th class="px-4 py-3 font-bold">Objet</th>
                        <th class="px-4 py-3 font-bold">Entite Expeditrice</th>
                        <th class="px-2 py-3 w-10"></th> <!-- Actions -->
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700">
                    <!-- Boucle sur vos données (Exemple statique + dynamique) -->
                    @forelse($references as $ref)
                    <tr class="border-b border-slate-300 hover:bg-yellow-50 transition-colors duration-150 group h-14">
                        
                        <!-- Date : Police Mono pour aspect technique -->
                        <td class="px-4 py-2 border-r border-slate-300 font-mono text-slate-600 text-xs">
                            {{ $ref->date }} 
                        </td>

                        <!-- N° Ordre : Aspect "Tampon" -->
                        <td class="px-4 py-2 border-r border-slate-300">
                            <span class="bg-slate-200 text-slate-800 px-2 py-1 rounded text-xs font-bold font-mono border border-slate-300 shadow-sm">
                                {{ $ref->numero_ordre_path }}
                            </span>
                        </td>


                        <!-- Concerne -->
                        <td class="px-4 py-2 border-r border-slate-300 font-semibold text-slate-800">
                            {{ $ref->concerne }}
                        </td>

                        <!-- Objet -->
                        <td class="px-4 py-2 text-slate-700 leading-snug">
                            {{ Str::limit($ref->object, 60) }} 
                        </td>

                        <!-- Entite  -->
                        <td class="px-4 py-2 text-slate-700 leading-snug">
                            {{ $ref->entity_exp }}
                        </td>
                        
                        <!-- Actions (visible au survol) -->
                        <td class="px-2 text-center">
                             <button 
                                wire:click="viewReference({{ $ref->memo_id }})"
                                class="text-slate-400 hover:text-blue-600 opacity-0 group-hover:opacity-100 transition"
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
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic font-serif">
                            Aucune référence enregistrée dans ce registre.
                        </td>
                    </tr>
                    @endforelse
                    
                    
                    <!-- Lignes vides pour remplir la page (Esthétique) -->
                    @for($i = 0; $i < 5; $i++)
                    <tr class="border-b border-slate-200 h-14 bg-transparent opacity-50 pointer-events-none">
                        <td class="border-r border-slate-200"></td>
                        <td class="border-r border-slate-200"></td>
                        <td class="border-r border-slate-200"></td>
                        <td class="border-r border-slate-200"></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endfor

                </tbody>
            </table>
        </div>
        
        <!-- Pied de page du registre -->
        <div class="p-4 border-t-2 border-slate-800 mt-auto bg-slate-50 flex justify-between items-center">
            <div class="text-xs text-slate-500 font-serif italic">
                Registre généré numériquement - Authentifié par le système.
            </div>
            <!-- Pagination ou totaux -->
            <div class="text-xs font-bold text-slate-700">
                TOTAL: {{ isset($references) ? count($references) : 0 }} ENTRÉES
            </div>
        </div>

    </div>

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
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif


</div>
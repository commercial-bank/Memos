<div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        @forelse ($memos as $memo)

            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 flex flex-col justify-between relative overflow-hidden h-full">
                
                <!-- Décoration coin -->
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-100 opacity-50 transform rotate-45 translate-x-12 -translate-y-12 flex items-center justify-center">
                    <svg class="w-12 h-12 text-blue-400 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>

                <!-- Badge de provenance/Statut -->
                <div class="absolute top-0 left-0 p-2">
                    @if($memo->status == 'rejected')
                        <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded">Retourné (Rejet)</span>
                    @else
                        <span class="bg-orange-100 text-orange-600 text-xs font-bold px-2 py-1 rounded">À traiter</span>
                    @endif
                </div>

                <div class="mt-4">
                    <!-- Provenance (L'auteur initial du mémo) -->
                    <div class="flex items-center text-gray-700 mb-2">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span class="text-sm font-medium">De : <span class="font-semibold">{{ $memo->user->first_name }} {{ $memo->user->last_name }}</span></span>
                    </div>

                    <!-- Service / Entité de l'auteur -->
                    <div class="flex items-center text-gray-700 mb-2">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span class="text-sm font-medium">Service : <span class="font-semibold">{{ $memo->user->service ?? 'N/A' }}</span></span>
                    </div>

                    <!-- Objet -->
                    <h3 class="text-lg font-semibold text-gray-800 mt-4 mb-2 truncate" title="{{ $memo->object }}">
                        {{ $memo->object }}
                    </h3>
                    
                    <!-- Extrait du contenu (Optionnel) -->
                    <p class="text-xs text-gray-500 line-clamp-2">
                        {{ strip_tags($memo->content) }}
                    </p>
                </div>

                <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
                    <div class="flex items-center text-gray-500 text-sm">
                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Reçu le : {{ $memo->updated_at->format('d/m/Y') }}
                    </div>

                    <!-- Boutons d'action -->
                    <!-- Note: Ici tu devras réintégrer tes boutons d'action (Voir, Traiter, Rejeter) 
                         en copiant la logique qu'on a fait dans DocsMemos -->
                    <div class="flex space-x-2">
                        <!-- Voir -->
                        <button wire:click="" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition" title="Voir">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                        
                        <!-- Traiter / Transmettre (Bouton Vert) -->
                        <button class="p-2 rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition" title="Traiter / Transmettre">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

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
                
                <!-- 1. OVERLAY (Fond sombre) -->
                <div 
                    wire:click="closeModal" 
                    class="fixed inset-0 bg-gray-900 bg-opacity-90 transition-opacity backdrop-blur-sm cursor-pointer"
                ></div>

                <!-- 2. BARRE D'OUTILS FLOTTANTE (Fixe à l'écran) -->
                <!-- Je l'ai sortie du scroll pour qu'elle soit toujours visible en haut -->
                <div class="fixed top-0 left-0 w-full z-50 pointer-events-none p-4 flex justify-between items-start print:hidden">
                    
                    <!-- Bouton Retour -->
                    <button wire:click="closeModal" class="pointer-events-auto bg-gray-800 text-white hover:bg-gray-700 px-6 py-2 rounded-full shadow-xl font-bold flex items-center gap-2 transition transform hover:scale-105 border border-gray-600">
                        <span>&larr; Retour</span>
                    </button>

                    <!-- Bouton Imprimer -->
                    <button onclick="window.print()" class="pointer-events-auto bg-white text-gray-900 hover:bg-gray-100 px-6 py-2 rounded-full shadow-xl font-bold flex items-center gap-2 transition transform hover:scale-105 border border-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span>Imprimer</span>
                    </button>
                </div>

                <!-- 3. CONTENEUR DE LA FEUILLE (Scrollable) -->
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto pt-20 pb-10"> <!-- pt-20 pour ne pas cacher le haut de la feuille sous les boutons -->
                    <div class="flex min-h-full items-start justify-center p-4 text-center sm:p-0">
                        
                        <!-- Wrapper de la feuille (J'ai retiré 'transform') -->
                        <div class="relative flex flex-col items-center font-sans w-full max-w-[210mm]">

                            <!-- FEUILLE A4 -->
                            <div class="bg-white w-[210mm] min-h-[297mm] shadow-2xl p-[10mm] text-black text-[13px] leading-snug relative text-left mx-auto">

                                <!-- LE GRAND CADRE DORÉ -->
                                <div class="border-[3px] border-[#D4AF37] rounded-tr-[60px] rounded-bl-[60px] p-8 h-full flex flex-col justify-between relative min-h-[calc(297mm-20mm)]">

                                    <!-- EN-TÊTE -->
                                    <div class="flex flex-col items-center justify-center mb-6 text-center">
                                        <div class="mb-2">
                                            <div class="w-16 h-16 flex items-center justify-center mx-auto mb-1">
                                                <img src="{{ asset('images/log.jpg') }}" alt="logo" class="w-full h-full object-contain">
                                            </div>
                                        </div>

                                        <div class="font-bold text-xl tracking-tight text-gray-900 font-sans">CommercialBank</div>
                                        <div class="text-[9px] text-gray-600 uppercase tracking-widest mb-4">Let's build the future</div>
                                        
                                        <h2 class="font-bold text-xs uppercase text-gray-800"> {{$user_entity}} </h2>
                                        <h1 class="font-extrabold text-2xl uppercase mt-2 italic border-b-2 border-black pb-1 px-4 inline-block">Memorandum</h1>
                                    </div>

                                    <!-- TABLEAU -->
                                    
                                    <!-- DÉBUT DU TABLEAU REDESIGNÉ -->
                                        <div class="mb-8 w-full">
                                            
                                            <!-- Style spécifique pour les cases à cocher -->
                                            <style>
                                                .custom-checkbox {
                                                    display: inline-flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                    width: 16px;
                                                    height: 16px;
                                                    border: 1.5px solid #000;
                                                    margin-right: 8px;
                                                    background-color: white;
                                                    flex-shrink: 0; /* Empêche l'écrasement */
                                                }
                                                /* Le carré noir intérieur quand c'est coché */
                                                .custom-checked::after {
                                                    content: '';
                                                    width: 10px;
                                                    height: 10px;
                                                    background-color: #000;
                                                    display: block;
                                                }
                                                /* Cellule active (quand il y a des destinataires) */
                                                .active-cell {
                                                    background-color: #f0fdf4; /* Vert très pâle */
                                                    color: #166534; /* Vert foncé */
                                                }
                                            </style>

                                            <table class="w-full border-collapse border border-black text-[13px] font-sans text-black leading-relaxed">
                                                
                                                <!-- ================= LIGNE 1 ================= -->
                                                @php $recipients1 = collect($recipientsByAction['Faire le nécessaire'] ?? []); @endphp
                                                <tr class="border-b border-black">
                                                    <!-- Col 1 : Info -->
                                                    <td class="border-r border-black p-3 w-[30%] align-top bg-gray-50">
                                                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider">Date</span>
                                                        <span class="font-bold text-base">{{ $date }}</span>
                                                    </td>
                                                    
                                                    <!-- Col 2 : Action -->
                                                    <td class="border-r border-black p-3 w-[30%] align-top">
                                                        <div class="flex items-center h-full">
                                                            <span class="custom-checkbox {{ $recipients1->count() > 0 ? 'custom-checked' : '' }}"></span> 
                                                            <span class="font-medium {{ $recipients1->count() > 0 ? 'font-bold' : '' }}">Faire le nécessaire</span>
                                                        </div>
                                                    </td>
                                                    
                                                    <!-- Col 3 : Destinataires -->
                                                    <td class="p-3 w-[40%] align-top {{ $recipients1->count() > 0 ? 'active-cell font-bold' : '' }}">
                                                        @if($recipients1->count() > 0)
                                                            {{ $recipients1->map(fn($m) => $m['entity']['acronym'] ?? $m['entity']['name'])->join(', ') }}
                                                        @else
                                                            <span class="text-gray-300 italic text-[11px]">Aucun destinataire</span>
                                                        @endif
                                                    </td>
                                                </tr>

                                                <!-- ================= LIGNE 2 ================= -->
                                                @php $recipients2 = collect($recipientsByAction['Prendre connaissance'] ?? []); @endphp
                                                <tr class="border-b border-black">
                                                    <!-- Col 1 : Info -->
                                                    <td class="border-r border-black p-3 align-top bg-gray-50">
                                                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider">Référence N°</span>
                                                        <span class="font-bold">#</span>
                                                    </td>
                                                    
                                                    <!-- Col 2 : Action -->
                                                    <td class="border-r border-black p-3 align-top">
                                                        <div class="flex items-center h-full">
                                                            <span class="custom-checkbox {{ $recipients2->count() > 0 ? 'custom-checked' : '' }}"></span> 
                                                            <span class="font-medium {{ $recipients2->count() > 0 ? 'font-bold' : '' }}">Prendre connaissance</span>
                                                        </div>
                                                    </td>
                                                    
                                                    <!-- Col 3 : Destinataires -->
                                                    <td class="p-3 align-top {{ $recipients2->count() > 0 ? 'active-cell font-bold' : '' }}">
                                                        @if($recipients2->count() > 0)
                                                            {{ $recipients2->map(fn($m) => $m['entity']['acronym'] ?? $m['entity']['name'])->join(', ') }}
                                                        @else
                                                            <span class="text-gray-300 italic text-[11px]">&nbsp;</span>
                                                        @endif
                                                    </td>
                                                </tr>

                                                <!-- ================= LIGNE 3 ================= -->
                                                @php $recipients3 = collect($recipientsByAction['Prendre position'] ?? []); @endphp
                                                <tr class="border-b border-black">
                                                    <!-- Col 1 : Info -->
                                                    <td class="border-r border-black p-3 align-top bg-gray-50">
                                                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider">Émetteur</span>
                                                        <span class="font-bold">{{ $user_first_name }} {{ $user_last_name }}</span>
                                                    </td>
                                                    
                                                    <!-- Col 2 : Action -->
                                                    <td class="border-r border-black p-3 align-top">
                                                        <div class="flex items-center h-full">
                                                            <span class="custom-checkbox {{ $recipients3->count() > 0 ? 'custom-checked' : '' }}"></span> 
                                                            <span class="font-medium {{ $recipients3->count() > 0 ? 'font-bold' : '' }}">Prendre position</span>
                                                        </div>
                                                    </td>
                                                    
                                                    <!-- Col 3 : Destinataires -->
                                                    <td class="p-3 align-top {{ $recipients3->count() > 0 ? 'active-cell font-bold' : '' }}">
                                                        @if($recipients3->count() > 0)
                                                            {{ $recipients3->map(fn($m) => $m['entity']['acronym'] ?? $m['entity']['name'])->join(', ') }}
                                                        @else
                                                            <span class="text-gray-300 italic text-[11px]">&nbsp;</span>
                                                        @endif
                                                    </td>
                                                </tr>

                                                <!-- ================= LIGNE 4 ================= -->
                                                @php $recipients4 = collect($recipientsByAction['Décider'] ?? []); @endphp
                                                <tr>
                                                    <!-- Col 1 : Info -->
                                                    <td class="border-r border-black p-3 align-top bg-gray-50">
                                                        <span class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider">Service</span>
                                                        <span class="font-bold">{{ $user_service }}</span>
                                                    </td>
                                                    
                                                    <!-- Col 2 : Action -->
                                                    <td class="border-r border-black p-3 align-top">
                                                        <div class="flex items-center h-full">
                                                            <span class="custom-checkbox {{ $recipients4->count() > 0 ? 'custom-checked' : '' }}"></span> 
                                                            <span class="font-medium {{ $recipients4->count() > 0 ? 'font-bold' : '' }}">Décider</span>
                                                        </div>
                                                    </td>
                                                    
                                                    <!-- Col 3 : Destinataires -->
                                                    <td class="p-3 align-top {{ $recipients4->count() > 0 ? 'active-cell font-bold' : '' }}">
                                                        @if($recipients4->count() > 0)
                                                            {{ $recipients4->map(fn($m) => $m['entity']['acronym'] ?? $m['entity']['name'])->join(', ') }}
                                                        @else
                                                            <span class="text-gray-300 italic text-[11px]">&nbsp;</span>
                                                        @endif
                                                    </td>
                                                </tr>

                                            </table>
                                        </div>
                                        <!-- FIN DU TABLEAU -->




                                    <!-- CORPS DU TEXTE -->
                                    <div class="mb-4 flex-grow px-2">
                                        <div class="mb-6">
                                            <p class="mb-1"><span class="font-bold text-[15px] underline">Objet :</span> <span class="uppercase font-bold"> {{$object}}  </span></p>
                                        </div>

                                        <div class="text-justify space-y-3 text-[14px] leading-relaxed font-serif text-gray-900">
                                            {{$content}}
                                        </div>
                                    </div>

                                    

                                </div> 
                            </div>
                            
                            <!-- Bouton Fermer Bas (Optionnel) -->
                            <!-- Bouton PDF / Imprimer -->
                             <br>
                            <button 
                                onclick="printMemo()" 
                                class="pointer-events-auto bg-red-600 text-white hover:bg-red-700 px-6 py-2 rounded-full shadow-xl font-bold flex items-center gap-2 transition transform hover:scale-105 border border-red-500"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Télécharger PDF</span>
                            </button>

                        </div>
                
                    </div>
                </div>
            </div>
            
    @endif

    </div>
</div>
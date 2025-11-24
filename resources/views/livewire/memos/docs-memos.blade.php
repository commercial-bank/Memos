<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    @forelse($groupedMemos as $document)

        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 flex flex-col justify-between relative overflow-hidden h-full">
            
            <!-- Décoration coin -->
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 opacity-50 transform rotate-45 translate-x-12 -translate-y-12 flex items-center justify-center"></div>

            <div>
                <!-- EN-TÊTE DE LA CARTE : Le Document -->
                <div class="mb-4">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                        Réf: #{{ $document->id }}
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
                            <button wire:click="rejectMemo({{ $document->id }})" class="p-2 rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition" title="Rejeter">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        @endif
                    @endif
                </div>

                <!-- BADGE DE STATUT (En haut à gauche par exemple) -->
                <div class="absolute top-0 left-0 p-2">
                    @if($document->status == 'pending')
                        <span class="bg-orange-100 text-orange-600 text-xs font-bold px-2 py-1 rounded">En traitement</span>
                    @elseif($document->status == 'distributed')
                        <span class="bg-green-100 text-green-600 text-xs font-bold px-2 py-1 rounded">Diffusé (Réf: {{ $document->reference_number }})</span>
                    @elseif($document->status == 'rejected')
                        <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded">Rejeté</span>
                    @else
                        <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded">Brouillon</span>
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

                                <!-- Info Signature -->
                                @if(in_array(strtolower(Auth::user()->poste), ['sous-directeur', 'directeur']))
                                    <div class="flex items-center gap-2 text-green-600 text-sm bg-green-50 p-2 rounded mb-4">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>Votre signature numérique sera apposée automatiquement.</span>
                                    </div>
                                @endif

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

</div>
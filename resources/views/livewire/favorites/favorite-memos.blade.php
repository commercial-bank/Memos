<div class="p-6">
    <!-- En-tête de la page -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-8 h-8 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                Mes Favoris
            </h2>
            <p class="text-gray-500 text-sm mt-1">Gérez vos documents marqués comme importants.</p>
        </div>
        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded mt-2 md:mt-0">
            {{ $favorites->total() }} document(s)
        </span>
    </div>

    <!-- Grille de cartes -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        
        @forelse($favorites as $document)
            <!-- 
                IMPORTANT POUR LIVEWIRE : 
                wire:key est indispensable dans une boucle, surtout si on supprime des éléments (toggle favori) 
            -->
            <div wire:key="fav-{{ $document->id }}" class="bg-white rounded-lg shadow-md p-6 border border-gray-200 flex flex-col justify-between relative overflow-hidden h-full">
            
                <!-- Décoration coin -->
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 opacity-50 transform rotate-45 translate-x-12 -translate-y-12 flex items-center justify-center"></div>
                
                <button 
                    wire:click="toggleFavorite({{ $document->id }})" 
                    class="absolute top-2 right-2 z-10 p-1.5 rounded-full hover:bg-gray-50 transition-colors duration-200 focus:outline-none group"
                    title="Retirer des favoris">
                    
                    <!-- 
                        Dans cette vue, puisque ce sont DÉJÀ des favoris, 
                        on affiche directement l'étoile pleine. 
                        Au clic, l'élément disparaîtra de la liste.
                    -->
                    <svg class="w-6 h-6 text-yellow-400 fill-current hover:text-gray-300 transition-colors" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </button>
    
                <div>
                    <!-- EN-TÊTE DE LA CARTE : Le Document -->
                    <div class="mb-4">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                            Réf: {{ $document->numero_ref ?? '#' }}
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
                                    <span class="font-medium text-gray-700 w-1/2 truncate" title="{{ $destinataire->title }}">
                                        {{ $destinataire->title  ?? 'Entité inconnue' }}
                                        <span class="text-xs text-gray-400 block">{{ $destinataire->acronym }}</span>
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
                                    @if($document->qr_code == null)
                                        <button wire:click="viewDocument({{ $document->id }})" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition" title="Voir le document">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>    
                                        </button>
    
                                        <button 
                                            wire:click="qrDocument({{ $document->id }})" 
                                            class="p-2 rounded-full bg-purple-100 text-purple-600 hover:bg-purple-200 transition" 
                                            title="Générer QR Code">
                                            
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                            </svg>
                                        </button>  
                                    @else
                                        <button wire:click="viewDocument({{ $document->id }})" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition" title="Voir le document">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>    
                                        </button>
                                        <button 
                                            wire:click="openSendAssistModal({{ $document->id }})" 
                                            class="p-2 rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition" 
                                            title="Transmettre à Votre Assistante">
                                            
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 12h14"></path>
                                            </svg>
                                        </button> 
                                    @endif
                        
                                 @endif
    
                            @endif
    
                        @elseif(auth()->user()->poste == "Secretaire")
    
                            @if($document->archive_status_sec == false)
                                <button wire:click="viewDocument({{ $document->id }})" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition" title="Voir le document">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>    
                                </button>
                                <button 
                                    wire:click="EnregistrerDocument({{ $document->id }})" 
                                    class="p-2 rounded-full bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition" 
                                    title="Enregistrer">
                                    
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
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
    
    
                    <!-- BADGE DE STATUT -->
                    <div class="absolute top-0 left-0 p-2">
                        @if($document->status == 'rejected')
                            <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded">Retourné (Rejet)</span>
                        @elseif($document->status == 'valide' || $document->status == 'signed')
                             <span class="bg-green-100 text-green-600 text-xs font-bold px-2 py-1 rounded">Validé</span>
                        @else
                            <span class="bg-orange-100 text-orange-600 text-xs font-bold px-2 py-1 rounded">À traiter</span>
                        @endif
                    </div>
                </div>
    
            </div>
        
        @empty
            <!-- Message si aucun favori -->
            <div class="col-span-1 md:col-span-2 xl:col-span-3 flex flex-col items-center justify-center py-16 text-gray-400">
                <div class="bg-gray-100 p-6 rounded-full mb-4">
                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Aucun favori pour le moment</h3>
                <p class="text-sm mt-1">Marquez des documents avec l'étoile pour les retrouver ici.</p>
            </div>
        @endforelse

    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $favorites->links() }}
    </div>
</div>
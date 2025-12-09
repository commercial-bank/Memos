<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- HEADER : Titre & Actions Globales -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Centre de Notifications</h1>
            <p class="text-slate-500 text-sm mt-1">Gérez vos alertes, mémos et suivis de workflow.</p>
        </div>
        
        <div class="flex gap-3">
            @if($counts['unread'] > 0)
                <button wire:click="markAllAsRead" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50 hover:text-[#b8962f] transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Tout marquer comme lu
                </button>
            @endif
            
            @if($counts['read'] > 0)
                <button wire:click="deleteAllRead" wire:confirm="Voulez-vous vraiment supprimer l'historique des notifications lues ?" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 shadow-sm hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Nettoyer l'historique
                </button>
            @endif
        </div>
    </div>

    <!-- TABS : Filtres -->
    <div class="mb-6 border-b border-slate-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            
            <!-- Onglet TOUS -->
            <button wire:click="setFilter('all')" 
                class="{{ $filter === 'all' ? 'border-[#b8962f] text-[#b8962f]' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} 
                       whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                Toutes
                <span class="{{ $filter === 'all' ? 'bg-[#b8962f]/10 text-[#b8962f]' : 'bg-slate-100 text-slate-600' }} ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block">
                    {{ $counts['all'] }}
                </span>
            </button>

            <!-- Onglet NON LUS -->
            <button wire:click="setFilter('unread')" 
                class="{{ $filter === 'unread' ? 'border-[#b8962f] text-[#b8962f]' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} 
                       whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                Non lues
                @if($counts['unread'] > 0)
                    <span class="ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium bg-red-100 text-red-600 animate-pulse">
                        {{ $counts['unread'] }}
                    </span>
                @else
                    <span class="ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">0</span>
                @endif
            </button>

            <!-- Onglet LUS -->
            <button wire:click="setFilter('read')" 
                class="{{ $filter === 'read' ? 'border-[#b8962f] text-[#b8962f]' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} 
                       whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors">
                Archivées / Lues
                <span class="{{ $filter === 'read' ? 'bg-[#b8962f]/10 text-[#b8962f]' : 'bg-slate-100 text-slate-600' }} ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block">
                    {{ $counts['read'] }}
                </span>
            </button>
        </nav>
    </div>

    <!-- LISTE DES NOTIFICATIONS -->
    <div class="space-y-4">
        
        @forelse($notifications as $notif)
            <!-- CARD NOTIFICATION -->
            <div wire:key="notif-{{ $notif->id }}" 
                 class="relative group rounded-xl border transition-all duration-200 overflow-hidden
                        {{ is_null($notif->read_at) 
                           ? 'bg-white border-slate-200 shadow-md ring-1 ring-slate-200/50' 
                           : 'bg-slate-50/50 border-slate-100 opacity-90 hover:opacity-100' }}">
                
                <!-- Bande latérale de couleur selon le statut -->
                <div class="absolute left-0 top-0 bottom-0 w-1 
                            {{ is_null($notif->read_at) ? ($notif->data['icon_color'] === 'text-red-600' ? 'bg-red-500' : 'bg-[#b8962f]') : 'bg-slate-300' }}">
                </div>

                <div class="p-5 flex items-start gap-4">
                    
                    <!-- 1. ICONE -->
                    <div class="flex-shrink-0 mt-1">
                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full 
                                     {{ is_null($notif->read_at) ? ($notif->data['icon_bg'] ?? 'bg-blue-50') : 'bg-slate-200' }}">
                            <svg class="h-6 w-6 {{ is_null($notif->read_at) ? ($notif->data['icon_color'] ?? 'text-blue-600') : 'text-slate-500' }}" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $notif->data['icon_path'] ?? '' !!}
                            </svg>
                        </span>
                    </div>

                    <!-- 2. CONTENU -->
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <h4 class="text-sm font-bold {{ is_null($notif->read_at) ? 'text-slate-900' : 'text-slate-600' }}">
                                {{ $notif->data['message'] ?? 'Notification' }}
                            </h4>
                            <span class="text-xs text-slate-400 whitespace-nowrap ml-2 font-mono">
                                {{ $notif->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-slate-600 line-clamp-2">
                            <span class="font-semibold text-slate-700">Objet :</span> {{ $notif->data['object'] ?? 'Aucun objet' }}
                        </p>
                        
                        @if(isset($notif->data['details']))
                            <p class="mt-1 text-xs text-slate-500 italic">
                                {{ $notif->data['details'] }}
                            </p>
                        @endif
                        
                        <!-- Actions & Lien -->
                        <div class="mt-4 flex items-center gap-4">
                            <!-- Bouton Voir -->
                            @if(isset($notif->data['link']) && $notif->data['link'] !== '#')
                                <a href="{{ $notif->data['link'] }}" class="inline-flex items-center text-xs font-semibold text-[#b8962f] hover:text-[#967d2b] transition-colors">
                                    Ouvrir le dossier
                                    <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                            @endif

                            <!-- Bouton Marquer comme lu (si non lu) -->
                            @if(is_null($notif->read_at))
                                <button wire:click="markAsRead('{{ $notif->id }}')" class="text-xs text-slate-400 hover:text-blue-600 transition-colors flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Marquer comme lu
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- 3. ACTION SUPPRIMER (Apparaît au hover) -->
                    <div class="flex-shrink-0 self-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <button wire:click="delete('{{ $notif->id }}')" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-full transition-colors" title="Supprimer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

        @empty
            <!-- EMPTY STATE -->
            <div class="text-center py-20 bg-white rounded-xl border border-dashed border-slate-300">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <h3 class="text-lg font-medium text-slate-900">Aucune notification</h3>
                <p class="text-slate-500 mt-1 max-w-sm mx-auto">
                    @if($filter === 'unread')
                        Vous êtes à jour ! Aucun nouveau message à traiter.
                    @else
                        Votre historique de notifications est vide pour le moment.
                    @endif
                </p>
                <div class="mt-6">
                    <button wire:click="setFilter('all')" class="text-sm text-[#b8962f] font-medium hover:underline">
                        Réinitialiser les filtres
                    </button>
                </div>
            </div>
        @endforelse

        <!-- PAGINATION -->
        <div class="mt-6">
            {{ $notifications->links() }} 
        </div>
    </div>
</div>
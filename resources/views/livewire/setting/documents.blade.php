<div class="min-h-screen bg-gray-50/50 p-4 lg:p-8 font-sans">
    
    <!-- HEADER & FILTRES -->
    <div class="max-w-6xl mx-auto mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Mes Documents</h1>
                <p class="text-sm text-gray-500 font-medium">Suivi de vos mémos et fils de discussion</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <div class="flex bg-gray-100 p-1 rounded-xl">
                    <select wire:model.live="selectedMonth" class="bg-transparent border-none text-xs font-bold uppercase cursor-pointer focus:ring-0">
                        @foreach($months as $key => $name) <option value="{{ $key }}">{{ $name }}</option> @endforeach
                    </select>
                    <select wire:model.live="selectedYear" class="bg-transparent border-none text-xs font-bold uppercase cursor-pointer focus:ring-0">
                        @foreach($years as $year) <option value="{{ $year }}">{{ $year }}</option> @endforeach
                    </select>
                </div>
                <div class="relative">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher un dossier..." 
                           class="w-64 pl-10 pr-4 py-2.5 bg-gray-100 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#daaf2c] transition-all">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- STATS GRID -->
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-[#daaf2c] p-6 rounded-2xl shadow-lg shadow-[#daaf2c]/20 flex items-center justify-between text-white">
            <div>
                <p class="text-white/80 text-xs font-bold uppercase tracking-wider mb-1">Mémos Initiés</p>
                <h3 class="text-3xl font-black">{{ $stats['envoyes'] }}</h3>
            </div>
            <div class="p-3 bg-white/20 rounded-xl"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg></div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Réponses Reçues</p>
                <h3 class="text-3xl font-black text-gray-900">{{ $stats['reponses_recues'] }}</h3>
            </div>
            <div class="p-3 bg-[#daaf2c]/10 text-[#daaf2c] rounded-xl"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg></div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Visas Apposés</p>
                <h3 class="text-3xl font-black text-gray-900">{{ $stats['vises'] }}</h3>
            </div>
            <div class="p-3 bg-[#daaf2c]/10 text-[#daaf2c] rounded-xl"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
        </div>
    </div>

    <!-- NAVIGATION PAR ONGLETS -->
    <div class="max-w-6xl mx-auto mb-6">
        <div class="flex gap-8 border-b border-gray-200">
            <button wire:click="$set('activeTab', 'created')" 
                class="pb-4 text-sm font-bold uppercase tracking-widest transition-all relative {{ $activeTab === 'created' ? 'text-[#daaf2c]' : 'text-gray-400 hover:text-gray-600' }}">
                Mes Mémos Initiés
                @if($activeTab === 'created') <div class="absolute bottom-0 left-0 w-full h-1 bg-[#daaf2c] rounded-t-full"></div> @endif
            </button>
            <button wire:click="$set('activeTab', 'signed')" 
                class="pb-4 text-sm font-bold uppercase tracking-widest transition-all relative {{ $activeTab === 'signed' ? 'text-[#daaf2c]' : 'text-gray-400 hover:text-gray-600' }}">
                Mémos Visés / Signés
                @if($activeTab === 'signed') <div class="absolute bottom-0 left-0 w-full h-1 bg-[#daaf2c] rounded-t-full"></div> @endif
            </button>
        </div>
    </div>

    <!-- LISTE DES DOCUMENTS -->
    <div class="max-w-6xl mx-auto space-y-6">
        @forelse($memos as $memo)
            <div x-data="{ open: false }" class="relative">
                
                <!-- CARTE PRINCIPALE -->
                <div class="relative z-10 bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <!-- Couleur d'icône basée sur #daaf2c -->
                            <div class="p-3 bg-[#daaf2c]/10 text-[#daaf2c] rounded-xl flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-gray-900 line-clamp-1 uppercase">{{ $memo->object }}</h4>
                                <div class="flex items-center gap-3 mt-1 text-xs font-medium text-gray-400">
                                    <span class="bg-gray-100 px-2 py-0.5 rounded text-[#daaf2c] font-mono">{{ $memo->reference ?? 'NON-REF' }}</span>
                                    <span>•</span>
                                    @if($activeTab === 'signed')
                                        <span class="text-[#daaf2c] font-bold">Initié par : {{ $memo->user->first_name }} {{ $memo->user->last_name }}</span>
                                        <span>•</span>
                                    @endif
                                    <span>{{ $memo->created_at->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-4 ml-auto md:ml-0">
                            @if($memo->replies_count > 0)
                                <button @click="open = !open" 
                                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-[#daaf2c]/10 text-[#daaf2c] text-xs font-bold hover:bg-[#daaf2c]/20 transition-all">
                                    <svg class="w-4 h-4" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"></path></svg>
                                    {{ $memo->replies_count }} Réponses
                                </button>
                            @endif
                            <div class="flex gap-1">
                                <button title="Aperçu" class="p-2 text-gray-400 hover:text-[#daaf2c] hover:bg-[#daaf2c]/10 rounded-lg transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SOUS-SECTION RÉPONSES -->
                <div x-show="open" x-collapse class="relative ml-8 md:ml-12 mt-2 space-y-3">
                    <div class="absolute -left-6 top-0 bottom-6 w-0.5 bg-[#daaf2c]/20"></div>
                    @foreach($memo->replies as $reply)
                        <div class="relative bg-white rounded-2xl border border-gray-100 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="absolute -left-6 top-1/2 w-6 h-0.5 bg-[#daaf2c]/20"></div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#daaf2c]/10 text-[#daaf2c] flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                </div>
                                <div>
                                    <h5 class="text-xs font-bold text-gray-800">RE: {{ $reply->object }}</h5>
                                    <p class="text-[10px] text-gray-400 font-medium">Répondu par <span class="text-gray-600">{{ $reply->user->first_name }} {{ $reply->user->last_name }}</span> ({{ $reply->user->entity->ref ?? 'N/A' }}) • {{ $reply->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <button class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-[#daaf2c] hover:bg-[#daaf2c]/10 rounded-lg transition-all">Consulter</button>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl p-12 text-center border-2 border-dashed border-gray-200">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Aucun document</h3>
                <p class="text-sm text-gray-400">Rien à afficher pour cette catégorie sur la période sélectionnée.</p>
            </div>
        @endforelse

        <div class="mt-8">
            {{ $memos->links() }}
        </div>
    </div>
</div>
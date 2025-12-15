<div class="flex flex-col h-full">
    <!-- Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 bg-white p-3 rounded-xl shadow-sm border border-gray-100 gap-4">
        <div class="flex items-center text-sm text-gray-500 ml-2">
            <span class="hover:text-[#daaf2c] cursor-pointer">Mes Archives</span>
            <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="font-bold text-gray-900">Activité Mensuelle</span>
        </div>
        
        <div class="flex flex-wrap gap-3 items-center">
            <select wire:model.live="selectedMonth" class="bg-gray-50 border-none text-sm rounded-lg focus:ring-[#daaf2c] py-2 pl-3 pr-8 cursor-pointer">
                @foreach($months as $key => $name)
                    <option value="{{ $key }}">{{ $name }}</option>
                @endforeach
            </select>
            <select wire:model.live="selectedYear" class="bg-gray-50 border-none text-sm rounded-lg focus:ring-[#daaf2c] py-2 pl-3 pr-8 cursor-pointer">
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
            <div class="relative">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher..." class="pl-9 pr-4 py-2 bg-gray-50 border-none rounded-lg text-sm focus:ring-2 focus:ring-[#daaf2c] w-48 transition-all">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Stats Dossiers -->
    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">
        Dossiers de {{ $months[$selectedMonth] }} {{ $selectedYear }}
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-8">
        
        <!-- NOUVEAU : Dossier Envoyés -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-[#daaf2c] hover:shadow-md transition-all cursor-pointer flex flex-col items-center justify-center gap-2 group">
            <div class="w-12 h-12 rounded-full bg-yellow-50 flex items-center justify-center group-hover:bg-yellow-100 transition-colors">
                 <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
            </div>
            <div class="text-center">
                <span class="block font-medium text-gray-700 text-sm group-hover:text-black">Envoyés / Créés</span>
                <span class="block text-xs text-gray-400 font-bold mt-1">{{ $stats['envoyes'] }} mémos</span>
            </div>
        </div>

        <!-- Dossier Signatures -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-[#daaf2c] hover:shadow-md transition-all cursor-pointer flex flex-col items-center justify-center gap-2 group">
             <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition-colors">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
             </div>
            <div class="text-center">
                <span class="block font-medium text-gray-700 text-sm group-hover:text-black">Signés</span>
                <span class="block text-xs text-gray-400 font-bold mt-1">{{ $stats['signes'] }} mémos</span>
            </div>
        </div>
        
        <!-- Dossier Visas -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-[#daaf2c] hover:shadow-md transition-all cursor-pointer flex flex-col items-center justify-center gap-2 group">
             <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
             </div>
            <div class="text-center">
                <span class="block font-medium text-gray-700 text-sm group-hover:text-black">Visés</span>
                <span class="block text-xs text-gray-400 font-bold mt-1">{{ $stats['vises'] }} mémos</span>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Historique détaillé</h3>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="p-4 w-1/3">Objet du Mémo</th>
                    <th class="p-4">Action réalisée</th>
                    <th class="p-4">Date</th>
                    <th class="p-4 w-1/4">Commentaire</th>
                    <th class="p-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($memos as $memo)
                    @php
                        $history = $memo->historiques->first(); 
                        $isCreator = $memo->user_id === auth()->id();
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-800 line-clamp-1" title="{{ $memo->object }}">{{ $memo->object }}</span>
                                <span class="text-xs text-gray-400">Ref: {{ $memo->reference ?? 'En attente' }}</span>
                            </div>
                        </td>
                        <td class="p-4">
                            @if($history)
                                {{-- Cas où il y a une action dans l'historique --}}
                                @if(str_contains(strtolower($history->visa), 'sign'))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-pen-nib mr-1"></i> {{ $history->visa }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-check mr-1"></i> {{ $history->visa }}
                                    </span>
                                @endif
                            @elseif($isCreator)
                                {{-- Cas où c'est un mémo envoyé (créé) sans encore de visa perso --}}
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-paper-plane mr-1"></i> Initié / Envoyé
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-gray-500">
                            {{-- Si historique existe, on prend sa date, sinon la date de création du mémo --}}
                            {{ $history ? $history->created_at->format('d/m/Y H:i') : $memo->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="p-4 text-gray-500 italic text-xs max-w-xs truncate">
                            {{ $history->workflow_comment ?? 'Aucun commentaire' }}
                        </td>
                        <td class="p-4 text-right">
                            <button class="text-gray-400 hover:text-[#daaf2c]"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-400">Aucun document trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">
            {{ $memos->links() }}
        </div>
    </div>
</div>
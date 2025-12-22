<div class="min-h-screen bg-gray-50/50 py-8 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- HEADER -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="bg-blue-50 p-2 rounded-lg text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Mes Dossiers Finalisés</h2>
                    <p class="text-xs text-gray-500">Mémos en cours de traitement global, mais terminés pour votre entité</p>
                </div>
            </div>

            <div class="relative w-full md:w-96">
                <input wire:model.live.debounce.300ms="search" type="text" 
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 placeholder-gray-400 focus:ring-1 focus:ring-blue-400 sm:text-sm transition" 
                       placeholder="Rechercher dans vos archives...">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- TABLEAU -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Finalisé le</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence / Objet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Votre Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expéditeur</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($archives as $memo)
                            @php
                                $myDest = $memo->destinataires->where('entity_id', Auth::user()->entity_id)->first();
                                $statusLabel = [
                                    'traiter' => 'Traité',
                                    'decision_prise' => 'Décidé',
                                    'repondu' => 'Répondu'
                                ][$myDest->processing_status] ?? 'Terminé';
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $myDest->completed_at ? \Carbon\Carbon::parse($myDest->completed_at)->format('d/m/Y H:i') : $memo->updated_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-[10px] font-mono font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded uppercase">{{ $memo->reference ?? 'SANS-REF' }}</span>
                                    <p class="text-sm font-bold text-gray-800 truncate mt-1">{{ $memo->object }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $memo->user->first_name }} {{ $memo->user->last_name }}
                                    <div class="text-[10px] text-gray-400 uppercase">{{ $memo->user->entity->ref }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <button wire:click="viewMemo({{ $memo->id }})" class="p-1.5 text-gray-500 hover:text-blue-600 border rounded-md hover:bg-blue-50 transition" title="Consulter">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                        <button wire:click="downloadPdf({{ $memo->id }})" class="p-1.5 text-gray-500 hover:text-red-600 border rounded-md hover:bg-red-50 transition" title="Télécharger PDF">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Aucun dossier archivé trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t bg-gray-50">{{ $archives->links() }}</div>
        </div>
    </div>

    <!-- MODAL DE VISUALISATION -->
    @if($isOpen && $selectedMemo)
        <div class="fixed inset-0 z-[200] overflow-y-auto">
            <div wire:click="closeModal" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white w-full max-w-[210mm] shadow-2xl rounded-sm p-[10mm] animate-fade-in">
                    
                    <!-- TAMPON D'ARCHIVAGE (Selon le statut) -->
                    @php
                        $stampText = [
                            'traiter' => 'TRAITÉ',
                            'decision_prise' => 'DÉCIDÉ',
                            'repondu' => 'RÉPONDU'
                        ][$myStatusInfo->processing_status] ?? 'TERMINÉ';
                        $finishDate = $myStatusInfo->completed_at ? \Carbon\Carbon::parse($myStatusInfo->completed_at)->format('d/m/Y') : $selectedMemo->updated_at->format('d/m/Y');
                    @endphp

                    <div class="absolute top-12 right-12 opacity-60 pointer-events-none z-50" style="transform: rotate(-12deg);">
                        <div class="border-4 border-green-700 text-green-700 px-4 py-1 rounded font-black text-3xl tracking-tighter uppercase font-mono">
                            {{ $stampText }}
                        </div>
                        <div class="text-green-700 text-[10px] font-bold text-center mt-1 font-mono">LE {{ $finishDate }}</div>
                    </div>

                    <!-- DESIGN PAPIER (Cadre doré) -->
                    <div class="border-[3px] border-[#D4AF37] rounded-tr-[50px] rounded-bl-[50px] p-8 min-h-[260mm] flex flex-col relative">
                        <div class="text-center mb-6">
                            <img src="{{ asset('images/logo.jpg') }}" class="h-14 mx-auto mb-2 object-contain">
                            <h2 class="text-[10px] uppercase font-bold tracking-widest text-gray-500 border-b border-gray-100 pb-2">{{ $selectedMemo->user->entity->name }}</h2>
                            <h1 class="font-extrabold text-2xl uppercase mt-2 italic text-black font-sans">Memorandum</h1>
                        </div>

                        <!-- INFO TABLE -->
                        <div class="mb-6">
                            <table class="w-full border border-black text-[11px] font-sans">
                                <tr>
                                    <td class="border border-black p-1.5 bg-gray-50 font-bold w-1/4">Référence</td>
                                    <td class="border border-black p-1.5 font-bold">{{ $selectedMemo->reference }}</td>
                                    <td class="border border-black p-1.5 bg-gray-50 font-bold w-1/4">Date Émission</td>
                                    <td class="border border-black p-1.5">{{ $selectedMemo->created_at->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-black p-1.5 bg-gray-50 font-bold">Émetteur</td>
                                    <td class="border border-black p-1.5 uppercase">{{ $selectedMemo->user->first_name }} {{ $selectedMemo->user->last_name }}</td>
                                    <td class="border border-black p-1.5 bg-gray-50 font-bold">Statut Global</td>
                                    <td class="border border-black p-1.5 text-blue-600 font-bold">{{ $selectedMemo->workflow_direction == 'terminer' ? 'Clôturé' : 'En cours ailleurs' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- OBJET / CONCERNE -->
                        <div class="mb-6 space-y-1">
                            <div class="flex gap-2"><span class="font-bold underline text-sm">OBJET :</span> <span class="font-bold uppercase text-sm">{{ $selectedMemo->object }}</span></div>
                            <div class="flex gap-2"><span class="font-bold underline text-sm">Concerne :</span> <span class="italic text-sm text-gray-700">{{ $selectedMemo->concern }}</span></div>
                        </div>

                        <!-- CONTENU -->
                        <div class="flex-grow text-justify leading-relaxed text-black text-[13px] font-serif mb-8">
                            {!! $selectedMemo->content !!}
                        </div>

                        <!-- FOOTER -->
                        <div class="mt-auto flex justify-between items-end border-t border-gray-100 pt-4">
                            <div class="text-[9px] text-gray-400 italic">
                                Document archivé numériquement.<br>ID: #{{ $selectedMemo->id }}
                            </div>
                            @if($selectedMemo->qr_code)
                                <div class="p-1 border border-gray-200 bg-white">
                                    {{ QrCode::size(35)->generate(route('memo.verify', $selectedMemo->qr_code)) }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
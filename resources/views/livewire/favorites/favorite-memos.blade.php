<div class="min-h-screen bg-gray-50 py-8 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @if($isViewingPdf)
                  
                    <!-- ========================================== -->
                    <!-- VUE 3 : APERÇU RÉEL PDF (DOMPDF)           -->
                    <!-- ========================================== -->
                    <div class="animate-fade-in">
                        <!-- BARRE D'ACTIONS (Header style original) -->
                        <div class="mb-8 bg-white border border-gray-100 rounded-xl shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-all duration-300">
                            <button wire:click="closePdfView" type="button" class="group flex items-center text-gray-500 hover:text-black transition-colors focus:outline-none">
                                <div class="mr-3 h-10 w-10 rounded-full bg-gray-100 group-hover:bg-[#daaf2c]/20 group-hover:text-[#daaf2c] flex items-center justify-center transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                </div>
                                <div class="flex flex-col items-start text-left">
                                    <span class="font-bold text-base text-black">Retour</span>
                                    <span class="text-xs font-normal text-gray-400">Revenir à la liste</span>
                                </div>
                            </button>
                            
                            <div class="flex items-center justify-end space-x-3 w-full sm:w-auto">
                                <button wire:click="downloadMemoPDF" type="button" 
                                    class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-lg shadow-md text-white transform hover:-translate-y-0.5 transition-all duration-200"
                                    style="background-color: #ef4444;">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Télécharger PDF
                                </button>
                            </div>
                        </div>

                        <!-- ZONE VISIONNEUSE (Style Papier) -->
                        <div class="bg-gray-200 rounded-lg shadow-inner p-4 md:p-8 flex justify-center min-h-[80vh]">
                            <div class="w-full max-w-5xl bg-white shadow-2xl">
                                @if($pdfBase64)
                                    <iframe 
                                        src="data:application/pdf;base64,{{ $pdfBase64 }}#view=FitH" 
                                        class="w-full h-[100vh] border-none">
                                    </iframe>
                                @else
                                    <div class="flex flex-col items-center justify-center py-20">
                                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#daaf2c]"></div>
                                        <p class="mt-4 text-gray-500 font-bold">Génération du rendu final DomPDF...</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

             

                @else

                        <!-- EN-TÊTE & RECHERCHE -->
                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                                    <svg class="w-8 h-8 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    Mes Favoris
                                </h2>
                                <p class="text-sm text-gray-500">Retrouvez ici tous les mémos que vous avez épinglés.</p>
                            </div>

                            <div class="relative w-full md:w-96">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm transition duration-150 ease-in-out shadow-sm" placeholder="Rechercher dans les favoris...">
                                <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg class="animate-spin h-5 w-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- TABLEAU -->
                        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="w-10 px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"><span class="sr-only">Favori</span></th>
                                            <th scope="col" class="w-24 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Objet & Concerne</th>
                                            <th scope="col" class="w-48 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destinataires</th>
                                            <th scope="col" class="w-32 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut & Note</th>
                                            <th scope="col" class="w-16 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P.J.</th>
                                            <th scope="col" class="w-32 px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                        
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse ($memos as $memo)
                                            <tr class="hover:bg-gray-50 transition-colors duration-150 group">
                                                
                                                <!-- 1. FAVORIS -->
                                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                                    <button wire:click="toggleFavorite({{ $memo->id }})" class="transition-transform transform active:scale-95 hover:scale-110 focus:outline-none" title="Retirer des favoris">
                                                        <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                                        </svg>
                                                    </button>
                                                </td>

                                                <!-- 2. DATE -->
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <div class="flex flex-col">
                                                        <span class="font-medium text-gray-900">{{ $memo->created_at->format('d/m/Y') }}</span>
                                                        <span class="text-xs">{{ $memo->created_at->format('H:i') }}</span>
                                                    </div>
                                                </td>

                                                <!-- 3. OBJET -->
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-col max-w-xs sm:max-w-sm md:max-w-md">
                                                        <span class="text-sm font-bold text-gray-800 truncate" title="{{ $memo->object }}">{{ $memo->object }}</span>
                                                        <span class="text-xs text-gray-500 truncate mt-1">Concerne: {{ $memo->concern }}</span>
                                                    </div>
                                                </td>

                                                <!-- 4. DESTINATAIRES -->
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-wrap gap-2">
                                                        @php 
                                                            $destinataires = $memo->destinataires; 
                                                            $count = $destinataires->count();
                                                        @endphp
                                                        @if($count > 0)
                                                            @foreach($destinataires->take(3) as $dest)
                                                                <div class="inline-flex flex-col items-start justify-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                                    <span class="font-bold">{{ $dest->entity->ref ?? Str::limit($dest->entity->name, 15) }}</span>
                                                                    <span class="text-[10px] opacity-80 leading-tight">{{ Str::limit($dest->action, 20) }}</span>
                                                                </div>
                                                            @endforeach
                                                            @if($count > 3) <span class="text-xs text-gray-500">+{{ $count - 3 }}</span> @endif
                                                        @else
                                                            <span class="text-xs text-gray-400 italic">Non assigné</span>
                                                        @endif
                                                    </div>
                                                </td>

                                                <!-- 5. STATUT -->
                                                <td class="px-6 py-4" x-data="{ openReason: false }">
                                                    @php
                                                        $statusRaw = Str::lower($memo->status);
                                                        $hasComment = !empty($memo->workflow_comment) && $memo->workflow_comment !== 'R.A.S';
                                                        
                                                        $colors = [
                                                            'envoyer' => 'bg-green-100 text-green-800 border-green-200',
                                                            'rejeter' => 'bg-red-100 text-red-800 border-red-200',
                                                            'transmit' => 'bg-purple-100 text-purple-800 border-purple-200',
                                                            'brouillon' => 'bg-gray-100 text-gray-800 border-gray-200',
                                                        ];
                                                        $colorClass = $colors[$statusRaw] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                                    @endphp

                                                    <button 
                                                        @if($hasComment) @click="openReason = true" @endif
                                                        type="button"
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $colorClass }} {{ $hasComment ? 'cursor-pointer hover:shadow-md' : 'cursor-default' }}">
                                                        {{ ucfirst($memo->status) }}
                                                        @if($hasComment) <svg class="w-3 h-3 ml-1.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg> @endif
                                                    </button>

                                                    <!-- Modal Note -->
                                                    @if($hasComment)
                                                        <div x-show="openReason" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm" @click.self="openReason = false">
                                                            <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 p-5">
                                                                <h3 class="text-sm font-bold mb-2">Note du Workflow</h3>
                                                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $memo->workflow_comment }}</p>
                                                                <button @click="openReason = false" class="mt-4 w-full bg-gray-100 py-2 rounded text-sm font-medium">Fermer</button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>

                                                <!-- 6. P.J. -->
                                                <td class="px-6 py-4 whitespace-nowrap" x-data="{ openFiles: false }">
                                                    @php
                                                        // CORRECTION : On vérifie le type avant de décoder
                                                        $pj = $memo->pieces_jointes;
                                                        
                                                        // Si c'est encore une chaîne (JSON), on décode. 
                                                        // Si c'est déjà un tableau (grâce aux casts Laravel), on ne fait rien.
                                                        if (is_string($pj)) { 
                                                            $pj = json_decode($pj, true); 
                                                        }
                                                        
                                                        // On s'assure que c'est bien un tableau pour éviter les erreurs au count()
                                                        $pj = is_array($pj) ? $pj : [];
                                                        $countPj = count($pj);
                                                    @endphp

                                                    @if($countPj > 0)
                                                        <button @click="openFiles = true" class="flex items-center space-x-1 text-gray-600 hover:text-blue-600 font-bold text-sm transition-colors focus:outline-none">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                            <span>{{ $countPj }}</span>
                                                        </button>

                                                        <!-- Modal PJ -->
                                                        <div x-show="openFiles" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm" @click.self="openFiles = false">
                                                            <div class="bg-white rounded-lg shadow-2xl w-80 max-w-sm mx-4 overflow-hidden border border-gray-100">
                                                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                                                                    <h3 class="text-sm font-bold text-gray-700">Pièces Jointes ({{ $countPj }})</h3>
                                                                    <button @click="openFiles = false" class="text-gray-400 hover:text-gray-600">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                                    </button>
                                                                </div>
                                                                <div class="max-h-64 overflow-y-auto p-2">
                                                                    <ul class="space-y-1">
                                                                        @foreach($pj as $file)
                                                                            @php
                                                                                $filePath = is_string($file) ? $file : ($file['path'] ?? $file['url'] ?? $file[0] ?? '');
                                                                                $fileName = is_string($file) ? basename($file) : ($file['original_name'] ?? $file['name'] ?? basename($filePath));
                                                                            @endphp

                                                                            @if($filePath)
                                                                                <li>
                                                                                    <a href="{{ Storage::url($filePath) }}" target="_blank" class="flex items-center p-2 hover:bg-blue-50 rounded-md text-sm text-gray-700 transition-colors group">
                                                                                        <div class="bg-blue-100 p-1.5 rounded-md mr-3 text-blue-600 group-hover:bg-blue-200">
                                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                                        </div>
                                                                                        <span class="truncate font-medium flex-1">{{ Str::limit($fileName, 25) }}</span>
                                                                                    </a>
                                                                                </li>
                                                                            @endif
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-gray-300 text-xs">-</span>
                                                    @endif
                                                </td>

                                                <!-- 7. ACTIONS -->
                                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                    <div class="flex items-center justify-center space-x-3">
                                                        
                                                        <!-- 1. ACTION : VOIR (Bleu) -->
                                                        <button wire:click="viewMemo({{ $memo->id }})" 
                                                                class="group p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 transition-all duration-200 focus:ring-2 focus:ring-blue-500/20" 
                                                                title="Aperçu">
                                                            <svg class="w-5 h-5 transform group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                            </svg>
                                                        </button>

                                                        <!-- HISTORIQUE (Violet) - Affiché si on n'a pas la main -->
                                                        <button wire:click="viewHistory({{ $memo->id }})" 
                                                                class="group p-2 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 hover:text-purple-700 transition-all duration-200 focus:ring-2 focus:ring-purple-500/20" 
                                                                title="Historique & Workflow">
                                                            <svg class="w-5 h-5 transform group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                        </button>

                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-6 py-16 text-center">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <!-- Icône Étoile (Favoris) -->
                                                        <div class="bg-yellow-50 p-4 rounded-full mb-4">
                                                            <svg class="h-12 w-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                            </svg>
                                                        </div>
                                                        
                                                        <p class="text-lg font-bold text-gray-800">Aucun favori trouvé</p>
                                                        <p class="text-sm text-gray-500 max-w-xs mx-auto mt-1">
                                                            Cliquez sur l'icône d'étoile à côté d'un mémo pour l'ajouter à vos favoris et le retrouver rapidement.
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="px-6 py-4 border-t border-gray-200">
                                {{ $memos->links() }} 
                            </div>
                        </div>
                    </div>
                @endif
    <!-- ========================================================== -->
    <!-- 1. MODAL APERÇU (STYLE PAPIER / A4) -->
    <!-- ========================================================== -->
    

    <!-- ========================================================== -->
    <!-- 2. MODAL HISTORIQUE -->
    <!-- ========================================================== -->
    @if($isOpenHistory)
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" wire:click="closeHistoryModal"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">
                        <div class="bg-gray-50 px-4 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Historique du Workflow
                            </h3>
                            <button type="button" wire:click="closeHistoryModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div class="px-6 py-6 bg-white max-h-[60vh] overflow-y-auto">
                            @forelse($memoHistory as $log)
                                <div class="relative pl-8 pb-8 group last:pb-0 border-l-2 border-gray-200 ml-2">
                                    @php
                                        $dotColor = 'bg-blue-500';
                                        if(str_contains(strtolower($log->visa), 'accord') && !str_contains(strtolower($log->visa), 'pas')) $dotColor = 'bg-green-500';
                                        elseif(str_contains(strtolower($log->visa), 'pas')) $dotColor = 'bg-red-500';
                                    @endphp
                                    <div class="absolute -left-[9px] top-0 h-5 w-5 rounded-full border-4 border-white {{ $dotColor }} shadow-sm"></div>
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-baseline mb-1">
                                        <h4 class="text-sm font-bold text-gray-900">{{ $log->user->first_name ?? '' }} {{ $log->user->last_name ?? 'Utilisateur' }}</h4>
                                        <span class="text-xs text-gray-400 font-mono">{{ $log->created_at->format('d/m/Y à H:i') }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $dotColor == 'bg-green-500' ? 'bg-green-100 text-green-800' : ($dotColor == 'bg-red-500' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                            {{ $log->visa }}
                                        </span>
                                    </div>
                                    @if(!empty($log->workflow_comment) && $log->workflow_comment !== 'R.A.S')
                                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-sm text-gray-600 italic relative">
                                            <span class="pl-4">{{ $log->workflow_comment }}</span>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500"><p>Aucun historique disponible.</p></div>
                            @endforelse
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" wire:click="closeHistoryModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:w-auto sm:text-sm">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
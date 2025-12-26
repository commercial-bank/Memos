<div class="min-h-screen bg-gray-50 py-8 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

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
                                        
                                        <!-- VOIR (Déclenche le nouveau modal) -->
                                        <button wire:click="viewMemo({{ $memo->id }})" class="text-gray-400 hover:text-blue-600 transition-colors" title="Aperçu">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>

                                        <!-- HISTORIQUE -->
                                        <button wire:click="viewHistory({{ $memo->id }})" class="text-gray-400 hover:text-purple-600 transition-colors" title="Historique">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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

    <!-- ========================================================== -->
    <!-- 1. MODAL APERÇU (STYLE PAPIER / A4) -->
    <!-- ========================================================== -->
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

            <!-- LOGIQUE BACKEND POUR RÉCUPÉRER LES DONNÉES MANQUANTES -->
            @php
                // Récupération du mémo complet avec les relations pour l'affichage
                $currentMemo = \App\Models\Memo::with('destinataires.entity')->find($memo_id);
                
                // Groupement des destinataires par leur Action
                $recipientsByAction = $currentMemo 
                    ? $currentMemo->destinataires->groupBy('action') 
                    : collect([]);

                // Helper pour formater la liste des entités (REF ou Nom)
                $formatRecipients = function($group) {
                    return $group->map(function($dest) {
                        return $dest->entity->ref ?? Str::limit($dest->entity->name, 15);
                    })->join(', ');
                };
            @endphp

            <!-- Conteneur Scrollable -->
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto pt-20 pb-10">
                <div class="flex min-h-full items-start justify-center p-4 text-center sm:p-0">
                    
                    <div class="relative flex flex-col items-center font-sans w-full max-w-[210mm]">

                        <!-- BOUTON TÉLÉCHARGER -->
                        <button 
                            onclick="downloadMemoPDF()" 
                            type="button" 
                            class="mb-4 pointer-events-auto bg-red-600 text-white hover:bg-red-700 px-6 py-2.5 rounded-full shadow-lg font-bold flex items-center gap-3 border border-red-500 transition-transform transform hover:scale-105">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span>Télécharger PDF</span>
                        </button>

                        <!-- ID GLOBAL POUR HTML2PDF -->
                        <div id="export-container">

                            <!-- PAGE 1 -->
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

                                    <!-- TABLEAU DESTINATAIRES -->
                                    <div id="recipient-table" class="mb-6 text-sm w-full">
                                        
                                        <!-- LIGNE D'ALIGNEMENT -->
                                        <div class="flex w-full text-[13px] font-bold font-['Arial'] pb-1 text-black">
                                            <div class="w-[35%]"></div>
                                            <div class="w-[30%] text-center">Prière de :</div>
                                            <div class="w-[35%] pl-8">Destinataires :</div>
                                        </div>
                                        
                                        <!-- TABLEAU COMPLET -->
                                        <table class="w-full border-collapse border border-black text-[13px] font-['Arial'] text-black">
                                            
                                            <!-- LIGNE 1 : Faire le nécessaire -->
                                            @php $recipients1 = $recipientsByAction['Faire le nécessaire'] ?? collect([]); @endphp
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold w-[35%] align-top">
                                                    Date : {{ $date }}
                                                </td>
                                                <td class="border border-black p-1 pl-2 w-[30%]">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients1->count() > 0 ? 'bg-green-600' : '' }}"></span> 
                                                    <span class="{{ $recipients1->count() > 0 ? 'font-bold' : '' }}">Faire le nécessaire</span>
                                                </td>
                                                <td class="border border-black p-1 text-center w-[35%] {{ $recipients1->count() > 0 ? 'font-bold bg-gray-50' : '' }}">
                                                    {{ $recipients1->count() > 0 ? $formatRecipients($recipients1) : '' }}
                                                </td>
                                            </tr>

                                            <!-- LIGNE 2 : Prendre connaissance -->
                                            @php $recipients2 = $recipientsByAction['Prendre connaissance'] ?? collect([]); @endphp
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold align-top">
                                                    N°: {{ $currentMemo->reference ?? 'En attente' }}
                                                </td>
                                                <td class="border border-black p-1 pl-2">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients2->count() > 0 ? 'bg-blue-600' : '' }}"></span> 
                                                    <span class="{{ $recipients2->count() > 0 ? 'font-bold' : '' }}">Prendre connaissance</span>
                                                </td>
                                                <td class="border border-black p-1 text-center {{ $recipients2->count() > 0 ? 'font-bold bg-gray-50' : '' }}">
                                                    {{ $recipients2->count() > 0 ? $formatRecipients($recipients2) : '' }}
                                                </td>
                                            </tr>

                                            <!-- LIGNE 3 : Prendre position -->
                                            @php $recipients3 = $recipientsByAction['Prendre position'] ?? collect([]); @endphp
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold align-top">
                                                    Emetteur : {{ $currentMemo->reference ? Str::afterLast($currentMemo->reference, '/') : 'En attente' }}
                                                </td>
                                                <td class="border border-black p-1 pl-2">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients3->count() > 0 ? 'bg-orange-500' : '' }}"></span> 
                                                    <span class="{{ $recipients3->count() > 0 ? 'font-bold' : '' }}">Prendre position</span>
                                                </td>
                                                <td class="border border-black p-1 text-center {{ $recipients3->count() > 0 ? 'font-bold bg-gray-50' : '' }}">
                                                    {{ $recipients3->count() > 0 ? $formatRecipients($recipients3) : '' }}
                                                </td>
                                            </tr>

                                            <!-- LIGNE 4 : Décider -->
                                            @php $recipients4 = $recipientsByAction['Décider'] ?? collect([]); @endphp
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold align-top">
                                                    Service : {{ $user_service }}
                                                </td>
                                                <td class="border border-black p-1 pl-2">
                                                    <span class="inline-block w-3 h-3 border border-black mr-1 align-middle {{ $recipients4->count() > 0 ? 'bg-yellow-400' : '' }}"></span> 
                                                    <span class="{{ $recipients4->count() > 0 ? 'font-bold' : '' }}">Décider</span>
                                                </td>
                                                <td class="border border-black p-1 text-center {{ $recipients4->count() > 0 ? 'font-bold bg-gray-50' : '' }}">
                                                    {{ $recipients4->count() > 0 ? $formatRecipients($recipients4) : '' }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- OBJET & CONCERNE -->
                                    <div class="mb-4">
                                        <div class="mb-6">
                                            <p class="mb-1">
                                                <span class="font-bold text-[15px] underline">Objet :</span> 
                                                <span class="uppercase font-bold"> {{ $object }} </span>
                                            </p>
                                        </div>
                                        <div class="mb-6">
                                            <p class="mb-1">
                                                <span class="font-bold text-[15px] underline">Concerne :</span> 
                                                <span class="lowercase text-gray-800"> {{ $concern }} </span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- CORPS DU TEXTE -->
                                    <div id="content-area" class="flex-grow px-2">
                                        <div class="text-justify space-y-3 text-[14px] leading-relaxed font-serif text-gray-900">
                                            {!! $content !!}
                                        </div>
                                    </div>

                                    <!-- PIED DE PAGE AVEC QR CODE -->
                                    <div class="absolute bottom-4 left-0 w-full flex flex-col items-center justify-center">
                                        @if($currentMemo && $currentMemo->qr_code)
                                            <div class="bg-white p-0.5 border border-gray-200 inline-block mb-2">
                                                {{ QrCode::size(50)->generate(route('memo.verify', $currentMemo->qr_code)) }}
                                            </div>
                                        @endif
                                        <div class="text-[10px] text-gray-500 italic">FOR-ME-07-V1</div>
                                    </div>

                                </div> 
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
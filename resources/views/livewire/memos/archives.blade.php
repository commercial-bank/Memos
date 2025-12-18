<div class="min-h-screen bg-gray-50/50 py-8 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- HEADER : BARRE DE RECHERCHE ET FILTRES -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <div class="flex items-center gap-2">
                <div class="bg-gray-100 p-2 rounded-lg text-gray-500">
                    <!-- Icone Archive -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Archives</h2>
                    <p class="text-xs text-gray-500">Consultez l'historique de vos dossiers traités</p>
                </div>
            </div>

            <!-- Recherche -->
            <div class="relative w-full md:w-96">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" 
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-gray-400 focus:border-gray-400 sm:text-sm transition duration-150 ease-in-out" 
                       placeholder="Rechercher par objet, référence...">
            </div>
        </div>

        <!-- TABLEAU DES ARCHIVES -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clôturé le</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Objet</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expéditeur</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($archives as $memo)
                            <tr class="hover:bg-gray-50 transition-colors duration-150 group">
                                
                                <!-- Date de mise à jour (Date de clôture approx) -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $memo->updated_at->format('d/m/Y') }}
                                    <div class="text-xs text-gray-400">{{ $memo->updated_at->format('H:i') }}</div>
                                </td>

                                <!-- Référence avec Badge -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 font-mono">
                                        {{ $memo->reference ?? 'NON-REF' }}
                                    </span>
                                </td>

                                <!-- Objet -->
                                <td class="px-6 py-4">
                                    <div class="max-w-md">
                                        <p class="text-sm font-bold text-gray-800 truncate">{{ $memo->object }}</p>
                                        <p class="text-xs text-gray-500 truncate mt-0.5">{{ Str::limit($memo->concern, 50) }}</p>
                                    </div>
                                </td>

                                <!-- Expéditeur -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600 uppercase mr-3">
                                            {{ substr($memo->user->first_name ?? 'U', 0, 1) }}{{ substr($memo->user->last_name ?? 'N', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ $memo->user->first_name }} {{ $memo->user->last_name }}</p>
                                            <p class="text-xs text-gray-400">{{ $memo->user->entity->ref ?? 'Entité' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex justify-center items-center space-x-3">
                                        <!-- Bouton Voir -->
                                        <button wire:click="viewMemo({{ $memo->id }})" class="text-gray-400 hover:text-blue-600 transition-colors bg-white border border-gray-200 rounded-md p-1.5 shadow-sm hover:shadow-md">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                        
                                        <!-- Bouton Download -->
                                        <button wire:click="downloadPdf({{ $memo->id }})" class="text-gray-400 hover:text-red-600 transition-colors bg-white border border-gray-200 rounded-md p-1.5 shadow-sm hover:shadow-md">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <svg class="h-16 w-16 mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                        <p class="text-lg font-medium text-gray-900">Aucune archive trouvée</p>
                                        <p class="text-sm">Les dossiers clôturés apparaîtront ici.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $archives->links() }}
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL DE VISUALISATION "ARCHIVES" (AVEC TAMPON)         -->
    <!-- ========================================================= -->
    @if($isOpen && $selectedMemo)
        <div class="relative z-[200]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Overlay Sombre -->
            <div wire:click="closeModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

            <!-- Bouton Fermer Flottant -->
            <div class="fixed top-4 right-4 z-[210]">
                <button wire:click="closeModal" class="bg-white/10 hover:bg-white/20 text-white rounded-full p-2 transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Contenu Scrollable -->
            <div class="fixed inset-0 z-[205] overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    
                    <!-- FEUILLE A4 (Design Papier) -->
                    <div class="relative bg-white w-full max-w-[210mm] min-h-[297mm] shadow-2xl mx-auto my-8 p-[10mm] text-black text-[13px] font-serif transform transition-all">
                        
                        <!-- EFFET TAMPON "ARCHIVÉ" -->
                        <div class="absolute top-10 right-10 pointer-events-none opacity-80 z-20" style="transform: rotate(-15deg);">
                            <div class="border-4 border-red-800 text-red-800 px-6 py-2 rounded-lg font-black text-4xl tracking-widest uppercase opacity-70 mix-blend-multiply" 
                                 style="font-family: 'Courier New', Courier, monospace; mask-image: url('https://grainy-gradients.vercel.app/noise.svg');">
                                ARCHIVÉ
                            </div>
                            <div class="text-red-800 text-xs font-bold text-center mt-1 font-mono uppercase">
                                Le {{ $selectedMemo->updated_at->format('d/m/Y') }}
                            </div>
                        </div>

                        <!-- CADRE DORÉ -->
                        <div class="border-[3px] border-[#D4AF37] rounded-tr-[60px] rounded-bl-[60px] p-8 h-full min-h-[270mm] flex flex-col relative">

                            <!-- EN-TÊTE -->
                            <div class="text-center mb-8">
                                <img src="{{ asset('images/logo.jpg') }}" alt="logo" class="h-16 mx-auto mb-2 object-contain">
                                <h2 class="text-xs uppercase font-bold tracking-widest text-gray-600">{{ $selectedMemo->user->entity->name ?? 'ENTITÉ' }}</h2>
                                <h1 class="font-bold text-3xl uppercase mt-2 italic font-sans text-gray-900">Memorandum</h1>
                            </div>

                            <!-- MÉTA-DONNÉES (Tableau) -->
                            <div class="mb-8 font-sans">
                                <table class="w-full border border-gray-800 text-sm">
                                    <tr>
                                        <td class="border border-gray-800 p-2 font-bold bg-gray-50 w-32">Réf.</td>
                                        <td class="border border-gray-800 p-2 font-mono font-bold">{{ $selectedMemo->reference }}</td>
                                        <td class="border border-gray-800 p-2 font-bold bg-gray-50 w-32">Date</td>
                                        <td class="border border-gray-800 p-2">{{ $selectedMemo->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-800 p-2 font-bold bg-gray-50">De</td>
                                        <td class="border border-gray-800 p-2">{{ $selectedMemo->user->first_name }} {{ $selectedMemo->user->last_name }}</td>
                                        <td class="border border-gray-800 p-2 font-bold bg-gray-50">Pour</td>
                                        <td class="border border-gray-800 p-2">
                                            @foreach($selectedMemo->destinataires as $dest)
                                                <span class="block text-xs">
                                                    - {{ $dest->entity->ref }} 
                                                    @if($dest->processing_status == 'decision_prise')
                                                        <span class="text-[10px] font-bold text-green-700 ml-1">[DÉCISION: {{ strtoupper($dest->decision_result) }}]</span>
                                                    @elseif($dest->processing_status == 'traite')
                                                         <span class="text-[10px] text-gray-500 ml-1">[Traité]</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- OBJET & CONCERNE -->
                            <div class="mb-6 space-y-2">
                                <div class="flex">
                                    <span class="font-bold underline w-24 flex-shrink-0">OBJET :</span>
                                    <span class="font-bold uppercase">{{ $selectedMemo->object }}</span>
                                </div>
                                <div class="flex text-gray-700">
                                    <span class="font-bold underline w-24 flex-shrink-0">Concerne :</span>
                                    <span class="italic">{{ $selectedMemo->concern }}</span>
                                </div>
                            </div>

                            <!-- CONTENU -->
                            <div class="flex-grow text-justify leading-relaxed text-gray-900 prose max-w-none mb-10">
                                {!! $selectedMemo->content !!}
                            </div>

                            <!-- SIGNATURES (Simulation) -->
                            <div class="grid grid-cols-2 gap-8 mt-auto pt-10">
                                <div class="text-center">
                                    <!-- Espace vide pour signature 1 -->
                                </div>
                                <div class="text-center">
                                    <p class="font-bold text-xs uppercase mb-8">Le Directeur</p>
                                    @if($selectedMemo->signature_dir)
                                        <img src="{{ asset('storage/'.$selectedMemo->signature_dir) }}" class="h-16 mx-auto opacity-80 filter grayscale" alt="Signature">
                                    @else
                                        <p class="text-gray-300 italic text-xs">[Signé électroniquement]</p>
                                    @endif
                                </div>
                            </div>

                            <!-- PIED DE PAGE -->
                            <div class="absolute bottom-2 left-0 w-full text-center">
                                <div class="flex flex-col items-center">
                                    @if($selectedMemo->qr_code)
                                        <div class="opacity-70 mb-1">
                                            {{ QrCode::size(40)->color(100,100,100)->generate(route('memo.verify', $selectedMemo->qr_code)) }}
                                        </div>
                                    @endif
                                    <p class="text-[10px] text-gray-400">Archivé le {{ now()->format('d/m/Y') }} - Copie conforme</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
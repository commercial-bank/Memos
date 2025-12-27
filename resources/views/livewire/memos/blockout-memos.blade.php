<div class="relative bg-[#fdfbf7] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.3)] rounded-lg overflow-hidden border border-gray-300 min-h-[600px] font-sans">

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
        
    
                    <!-- Décoration : Reliure à gauche (Effet Classeur) -->
                    <div class="absolute left-0 top-0 bottom-0 w-12 bg-gray-100 border-r border-gray-300 flex flex-col items-center pt-8 space-y-12 z-10">
                        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
                        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
                        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
                        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
                        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
                    </div>

                    <!-- Contenu du registre (Décalé vers la droite à cause de la reliure) -->
                    <div class="ml-12 flex flex-col h-full min-h-[600px]">
                        
                        <!-- En-tête du registre -->
                            <div class="bg-slate-800 text-white px-8 py-5 flex flex-col md:flex-row justify-between items-center gap-4 border-b-4 border-slate-600 shadow-md z-20 relative">
                                
                                <!-- TITRE -->
                                <div class="flex-shrink-0">
                                    <h2 class="text-2xl font-serif tracking-widest uppercase font-bold">Registre </h2>
                                    <p class="text-slate-400 text-xs uppercase tracking-wide">Mémos Sortants</p>
                                </div>

                                <!-- BARRE DE RECHERCHE CENTRALE (NOUVEAU) -->
                                <div class="flex-grow max-w-xl w-full mx-4">
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <!-- Icone Loupe -->
                                            <svg class="h-5 w-5 text-slate-400 group-focus-within:text-yellow-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                        
                                        <input 
                                            wire:model.live.debounce.300ms="search" 
                                            type="text" 
                                            class="block w-full pl-10 pr-10 py-2 border border-slate-600 rounded-lg leading-5 bg-slate-900/50 text-slate-200 placeholder-slate-500 focus:outline-none focus:bg-slate-900 focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm transition duration-150 ease-in-out shadow-inner" 
                                            placeholder="Rechercher par référence, objet, contenu..."
                                        >

                                        <!-- Bouton Reset (X) s'affiche si recherche active -->
                                        @if(!empty($search))
                                            <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-white cursor-pointer">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- SÉLECTEUR D'ANNÉE INTEGRÉ -->
                                <div class="flex-shrink-0 flex items-center gap-3 bg-slate-700/50 p-1.5 rounded-lg border border-slate-600">
                                    <label for="yearSelect" class="text-xs text-slate-300 uppercase font-bold pl-2">Année :</label>
                                    <div class="relative group">
                                        <select 
                                            wire:model.live="selectedYear" 
                                            id="yearSelect"
                                            class="appearance-none bg-slate-900 border border-slate-500 text-white text-sm font-mono font-bold py-1 pl-3 pr-8 rounded cursor-pointer hover:bg-slate-800 hover:border-yellow-500 focus:outline-none focus:ring-1 focus:ring-yellow-500 transition-all"
                                        >
                                            @for($y = date('Y'); $y >= 2023; $y--) 
                                                <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                        </select>
                                        <!-- Flèche custom -->
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400 group-hover:text-yellow-500 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <!-- Tableau "Grille Papier" -->
                        <div class="overflow-x-auto flex-grow">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="text-xs uppercase text-slate-500 border-b-2 border-slate-800 bg-slate-100 sticky top-0 z-10 shadow-sm">
                                        <th class="px-4 py-3 border-r border-slate-300 w-28 text-center font-bold">Date Sortie</th>
                                        <th class="px-4 py-3 border-r border-slate-300 w-32 font-bold">N° Reference</th>
                                        <th class="px-4 py-3 border-r border-slate-300 w-48 font-bold">Concerne</th>
                                        <th class="px-4 py-3 font-bold">Objet</th>
                                        <th class="px-2 py-3 w-10"></th> 
                                    </tr>
                                </thead>
                                <tbody class="text-sm text-slate-700 bg-[url('https://www.transparenttextures.com/patterns/lined-paper.png')]">
                    
                                    @forelse($references as $ref)
                                    <tr class="border-b border-slate-300 hover:bg-yellow-50 transition-colors duration-150 group h-14 bg-white/50">
                                        
                                        <!-- Date (Champ date_enreg de la table bloc) -->
                                        <td class="px-4 py-2 border-r border-slate-300 font-mono text-slate-600 text-xs text-center">
                                            {{ $ref->date_enreg }} 
                                        </td>

                                        <!-- N° Ordre (Extrait de la référence : tout ce qui est avant le premier /) -->
                                        <td class="px-4 py-2 border-r border-slate-300">
                                            <span class="bg-slate-100 text-slate-900 px-2 py-1 rounded text-xs font-bold font-mono border border-slate-300">
                                                {{ $ref->reference}}
                                            </span>
                                        </td>

                                        <!-- Concerne (Via la relation memo) -->
                                        <td class="px-4 py-2 border-r border-slate-300 font-semibold text-slate-800">
                                            {{ $ref->memo->concern ?? 'N/A' }}
                                        </td>

                                        <!-- Objet (Via la relation memo) -->
                                        <td class="px-4 py-2 text-slate-700 leading-snug">
                                            {{ Str::limit($ref->memo->object ?? 'Objet non disponible', 60) }} 
                                        </td>
                                        
                                        <!-- Actions -->
                                        <td class="px-2 text-center">
                                            <!-- On passe l'ID du mémo car c'est ce que attend viewReference -->
                                            <button 
                                                wire:click="viewMemo({{ $ref->memo_id }})"
                                                class="text-slate-400 hover:text-blue-600 opacity-0 group-hover:opacity-100 transition transform hover:scale-110"
                                                title="Voir les détails"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                                    <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                                    <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic font-serif">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                
                                                @if(!empty($search))
                                                    <span>Aucun résultat ne correspond à votre recherche "<strong>{{ $search }}</strong>" pour l'année {{ $selectedYear }}.</span>
                                                    <button wire:click="$set('search', '')" class="mt-2 text-blue-500 hover:underline text-sm font-sans">Réinitialiser la recherche</button>
                                                @else
                                                    <span>Aucune référence trouvée pour l'année <strong>{{ $selectedYear }}</strong>.</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                    
                                    <!-- ... (reste du code de remplissage identique) ... -->

                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pied de page -->
                        <div class="p-4 border-t-2 border-slate-800 bg-slate-50 flex justify-between items-center mt-auto">
                            <div class="text-xs text-slate-500 font-serif italic flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                Registre synchronisé • Année {{ $selectedYear }}
                            </div>
                            <div class="text-xs font-bold text-slate-700 bg-slate-200 px-3 py-1 rounded-full">
                                TOTAL : {{ count($references) }}
                            </div>
                        </div>

                    </div>
                @endif
    
</div>
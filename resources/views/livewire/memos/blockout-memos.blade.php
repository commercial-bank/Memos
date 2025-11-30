<!-- Conteneur principal avec effet de profondeur et reliure -->
<div class="relative bg-[#fdfbf7] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.3)] rounded-lg overflow-hidden border border-gray-300 min-h-[600px] font-sans">
    
    <!-- Décoration : Reliure à gauche (Effet Classeur) -->
    <div class="absolute left-0 top-0 bottom-0 w-12 bg-gray-100 border-r border-gray-300 flex flex-col items-center pt-8 space-y-12 z-10">
        <!-- Trous du classeur -->
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
        <div class="w-4 h-4 rounded-full bg-gray-800 shadow-[inset_0_2px_4px_rgba(0,0,0,0.6)]"></div>
    </div>

    <!-- Contenu du registre (Décalé vers la droite à cause de la reliure) -->
    <div class="ml-12">
        
        <!-- En-tête du registre -->
        <div class="bg-slate-800 text-white px-8 py-5 flex justify-between items-center border-b-4 border-slate-600">
            <div>
                <h2 class="text-2xl font-serif tracking-widest uppercase font-bold">Registre Chrono</h2>
                <p class="text-slate-400 text-xs uppercase tracking-wide">Départ Courrier / Mémos Sortants</p>
            </div>
            <!-- Petit effet "Page X" -->
            <div class="border border-slate-500 px-3 py-1 rounded text-sm text-slate-300 font-mono">
                REF: {{ date('Y') }}
            </div>
        </div>

        <!-- Tableau "Grille Papier" -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs uppercase text-slate-500 border-b-2 border-slate-800 bg-slate-100">
                        <th class="px-4 py-3 border-r border-slate-300 w-24 text-center font-bold">Date</th>
                        <th class="px-4 py-3 border-r border-slate-300 w-32 font-bold">N° Ordre</th>
                        <th class="px-4 py-3 border-r border-slate-300 w-48 font-bold">Concerne</th>
                        <th class="px-4 py-3 font-bold">Objet</th>
                        <th class="px-2 py-3 w-10"></th> <!-- Actions -->
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700">
                    <!-- Boucle sur vos données (Exemple statique + dynamique) -->
                    @forelse($references as $ref)
                    <tr class="border-b border-slate-300 hover:bg-yellow-50 transition-colors duration-150 group h-14">
                        
                        <!-- Date : Police Mono pour aspect technique -->
                        <td class="px-4 py-2 border-r border-slate-300 font-mono text-slate-600 text-xs">
                            {{ $ref->date }} 
                        </td>

                        <!-- N° Ordre : Aspect "Tampon" -->
                        <td class="px-4 py-2 border-r border-slate-300">
                            <span class="bg-slate-200 text-slate-800 px-2 py-1 rounded text-xs font-bold font-mono border border-slate-300 shadow-sm">
                                {{ $ref->numero_ordre_path }}
                            </span>
                        </td>


                        <!-- Concerne -->
                        <td class="px-4 py-2 border-r border-slate-300 font-semibold text-slate-800">
                            {{ $ref->concerne }}
                        </td>

                        <!-- Objet -->
                        <td class="px-4 py-2 text-slate-700 leading-snug">
                            {{ Str::limit($ref->object, 60) }} 
                        </td>
                        
                        <!-- Actions (visible au survol) -->
                        <td class="px-2 text-center">
                             <button 
                                wire:click="viewReference({{ $ref->memo_id }})"
                                class="text-slate-400 hover:text-blue-600 opacity-0 group-hover:opacity-100 transition"
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
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic font-serif">
                            Aucune référence enregistrée dans ce registre.
                        </td>
                    </tr>
                    @endforelse
                    
                    <!-- Lignes vides pour remplir la page (Esthétique) -->
                    @for($i = 0; $i < 5; $i++)
                    <tr class="border-b border-slate-200 h-14 bg-transparent opacity-50 pointer-events-none">
                        <td class="border-r border-slate-200"></td>
                        <td class="border-r border-slate-200"></td>
                        <td class="border-r border-slate-200"></td>
                        <td class="border-r border-slate-200"></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endfor

                </tbody>
            </table>
        </div>
        
        <!-- Pied de page du registre -->
        <div class="p-4 border-t-2 border-slate-800 mt-auto bg-slate-50 flex justify-between items-center">
            <div class="text-xs text-slate-500 font-serif italic">
                Registre généré numériquement - Authentifié par le système.
            </div>
            <!-- Pagination ou totaux -->
            <div class="text-xs font-bold text-slate-700">
                TOTAL: {{ isset($references) ? count($references) : 0 }} ENTRÉES
            </div>
        </div>

    </div>

    


</div>



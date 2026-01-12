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

                    <!-- HEADER -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <div class="bg-green-50 p-2 rounded-lg text-green-600">
                                <!-- Icône Dossier Archivé (Check) -->
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-800">Archives Générales</h2>
                                <p class="text-xs text-gray-500">Historique des mémos dont le circuit de validation est totalement terminé.</p>
                            </div>
                        </div>

                        <div class="relative w-full md:w-96">
                            <input wire:model.live.debounce.300ms="search" type="text" 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 placeholder-gray-400 focus:ring-1 focus:ring-blue-400 sm:text-sm transition" 
                                placeholder="Rechercher (Objet, Réf)...">
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Clôture</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence / Objet</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut Entité</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expéditeur</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($archives as $memo)
                                        @php
                                            // 1. Récupération sécurisée de l'entité de l'utilisateur
                                            $user = Auth::user();
                                            $userEntityIds = array_filter([$user->dir_id, $user->sd_id]);

                                            // 2. Recherche si mon entité était impliquée spécifiquement
                                            $myDest = $memo->destinataires->whereIn('entity_id', $userEntityIds)->first();
                                            
                                            // 3. Définition du badge (Label + Couleur)
                                            $statusLabel = 'Archivé';
                                            $badgeClasses = 'bg-gray-100 text-gray-700 border-gray-200';

                                            if ($myDest) {
                                                switch($myDest->processing_status) {
                                                    case 'decision_prise':
                                                        $statusLabel = 'Décision Rendue';
                                                        $badgeClasses = 'bg-purple-100 text-purple-700 border-purple-200';
                                                        break;
                                                    case 'repondu':
                                                        $statusLabel = 'Répondu';
                                                        $badgeClasses = 'bg-blue-100 text-blue-700 border-blue-200';
                                                        break;
                                                    case 'traiter':
                                                        $statusLabel = 'Traité';
                                                        $badgeClasses = 'bg-green-100 text-green-700 border-green-200';
                                                        break;
                                                    default:
                                                        // Cas rare où c'est fini globalement mais pas marqué localement (force majeure)
                                                        $statusLabel = 'Clos'; 
                                                        break;
                                                }
                                            }
                                        @endphp
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                <!-- Date de mise à jour globale du mémo (clôture) -->
                                                {{ $memo->updated_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($memo->reference)
                                                    <span class="text-[10px] font-mono font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded uppercase">{{ $memo->reference }}</span>
                                                @else
                                                    <span class="text-[10px] font-mono font-bold text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded uppercase">SANS-REF</span>
                                                @endif
                                                <p class="text-sm font-bold text-gray-800 truncate mt-1">{{ $memo->object }}</p>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badgeClasses }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $memo->user->entity->name ?? ($memo->user->dir->name ?? 'N/A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex justify-center gap-2">
                                                    <button wire:click="viewMemo({{ $memo->id }})" class="p-1.5 text-gray-500 hover:text-blue-600 border rounded-md hover:bg-blue-50 transition" title="Consulter le dossier">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    </button>
                                                    
                                    
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-16 text-center">
                                                <div class="flex flex-col items-center justify-center">
                                                    <!-- Icône Boîte d'Archive -->
                                                    <div class="bg-gray-50 p-4 rounded-full mb-4">
                                                        <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                        </svg>
                                                    </div>
                                                    
                                                    <p class="text-lg font-bold text-gray-800">Aucun dossier archivé</p>
                                                    <p class="text-sm text-gray-500 max-w-xs mx-auto mt-1">
                                                        Les mémos dont le circuit de traitement est totalement terminé apparaîtront ici.
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 border-t bg-gray-50">{{ $archives->links() }}</div>
                    </div>
                @endif
    </div>

</div>
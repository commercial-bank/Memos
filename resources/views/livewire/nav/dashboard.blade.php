<div class="space-y-8 pb-10">

    <!-- ============================================= -->
    <!-- 1. STYLES LOCAUX (Pour garantir la charte)    -->
    <!-- ============================================= -->
    <style>
        :root {
            --gold: #daaf2c;
            --gold-light: #daaf2c1a; /* 10% opacité */
            --dark: #000000;
        }
        .text-gold { color: var(--gold); }
        .bg-gold { background-color: var(--gold); }
        .border-gold { border-color: var(--gold); }
        .ring-gold:focus { box-shadow: 0 0 0 2px #fff, 0 0 0 4px var(--gold); }
        
        /* Scrollbar fine et invisible pour un look épuré */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 20px; }
    </style>

    <!-- ============================================= -->
    <!-- 2. HEADER : TITRE & BOUTON ACTION             -->
    <!-- ============================================= -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                Tableau de Bord
            </h2>
            <p class="text-gray-500 mt-2 font-medium">
                Vue d'ensemble de vos activités et flux documentaires.
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Date Widget -->
            <div class="hidden md:flex items-center px-4 py-2.5 bg-white border border-gray-200 rounded-full text-sm font-semibold text-gray-700 shadow-sm">
                <svg class="w-4 h-4 mr-2 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                {{ now()->translatedFormat('d F Y') }}
            </div>
            
        </div>
    </div>

    <!-- ============================================= -->
    <!-- 3. KPI CARDS (CARTES INDICATEURS)             -->
    <!-- ============================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Carte 1 : Mémos Sortants (Style Gold) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-yellow-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Sortants</p>
                    <h3 class="text-4xl font-black text-gray-900 mt-3">{{ $memosSortantsCount }}</h3>
                </div>
                <div class="p-3 bg-gold text-black rounded-xl shadow-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </div>
            </div>
            <div class="relative mt-4 flex items-center text-xs font-medium text-gold">
                <span>en cours d'expédition</span>
            </div>
        </div>

        <!-- Carte 2 : Mémos Entrants (Style Noir) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all group relative overflow-hidden">
             <div class="absolute -right-6 -top-6 w-24 h-24 bg-gray-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Entrants</p>
                    <h3 class="text-4xl font-black text-gray-900 mt-3">{{ $memosEntrantsCount }}</h3>
                </div>
                <div class="p-3 bg-gray-900 text-white rounded-xl shadow-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
            </div>
            <div class="relative mt-4 flex items-center text-xs font-medium text-gray-500">
                <span>en cours de cotation</span>
            </div>
        </div>

        <!-- Carte 3 : Favoris (Remplacement de Archives) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all relative overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Mes Favoris</p>
                    <!-- Affichage dynamique du compteur -->
                    <h3 class="text-4xl font-black text-gray-900 mt-3">{{ $favoritesCount }}</h3>
                </div>
                
                <!-- Icône Étoile (Jaune/Or) -->
                <div class="p-3 bg-yellow-50 text-yellow-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
            </div>
            <div class="relative mt-4 flex items-center text-xs font-medium text-gray-500">
                <span>documents épinglés</span>
            </div>
        </div>
        
        <!-- Carte 4 : Actions / Signatures (Logique conditionnelle) -->
        @php
            $isDirector = auth()->user()->poste == 'Directeur';
            $isSubDirector = auth()->user()->poste == 'Sous-Directeur';
            // Calcul du nombre selon le poste
            $count = $isDirector ? $toValidateCount_dir : ($isSubDirector ? $toValidateCount_sd : 0);
            $hasAction = ($isDirector || $isSubDirector) && $count > 0;
        @endphp

        @if($isDirector || $isSubDirector)
            <div class="{{ $hasAction ? 'bg-black text-white' : 'bg-gray-50' }} rounded-2xl p-6 shadow-lg transition-all transform hover:-translate-y-1 cursor-pointer border border-gray-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest {{ $hasAction ? 'text-gold' : 'text-gray-400' }}">
                            À Signer
                        </p>
                        <h3 class="text-4xl font-black mt-3 {{ $hasAction ? 'text-white' : 'text-gray-400' }}">
                            {{ $count }}
                        </h3>
                    </div>
                    <div class="p-3 rounded-xl {{ $hasAction ? 'bg-gold text-black' : 'bg-gray-200 text-gray-400' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </div>
                </div>
                <div class="mt-4 text-xs font-medium {{ $hasAction ? 'text-gray-300' : 'text-gray-400' }}">
                    {{ $hasAction ? 'Action requise immédiatement' : 'Tout est à jour' }}
                </div>
            </div>
        @else
            <!-- Placeholder pour employés standards -->
            <div class="bg-gray-50 rounded-2xl p-6 border border-dashed border-gray-300 flex flex-col justify-center items-center text-center">
                 <span class="text-gray-400 mb-2">
                    <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                 </span>
                 <p class="text-sm text-gray-500 font-medium">Aucune tâche en attente</p>
            </div>
        @endif
    </div>

    <!-- ============================================= -->
    <!-- 4. MAIN GRID (Graphique & Notifications)      -->
    <!-- ============================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- A. GRAPHIQUE -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Activité</h3>
                    <p class="text-sm text-gray-500">Volume des mémos générés</p>
                </div>
                
                <div class="relative">
                    <select wire:model.live="chartPeriod" class="appearance-none bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-gold focus:border-gold block w-full p-2.5 pr-8 cursor-pointer font-medium outline-none">
                        <option value="7_jours">7 derniers jours</option>
                        <option value="ce_mois">Ce mois</option>
                    </select>
                </div>
            </div>
            
            <div wire:ignore class="relative">
                <div id="chart-timeline" class="h-80 w-full"></div>
            </div>
        </div>

        <!-- B. NOTIFICATIONS (Timeline Style) -->
        <div wire:poll.10s class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col h-full overflow-hidden">
            
            <!-- Définition de la variable pour éviter l'erreur -->
            @php
                $unreadCount = auth()->user()->unreadNotifications->count();
            @endphp

            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-900">Notifications</h3>
                @if($unreadCount > 0)
                    <button wire:click="markAllNotificationsAsRead" class="text-[10px] uppercase font-bold tracking-wider text-gold hover:text-yellow-600 transition">
                        Tout effacer
                    </button>
                @else
                     <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400">À jour</span>
                @endif
            </div>

            <div class="flex-1 overflow-y-auto max-h-[400px] p-4 custom-scrollbar">
                <ul class="relative border-l-2 border-gray-100 ml-3 space-y-6">
                    @forelse(auth()->user()->unreadNotifications as $notification)
                        <li wire:key="{{ $notification->id }}" 
                            wire:click="markNotificationAsRead('{{ $notification->id }}')"
                            class="mb-2 ml-6 group cursor-pointer">
                            
                            <!-- Point sur la timeline -->
                            <span class="absolute -left-[9px] mt-1.5 h-4 w-4 rounded-full border-2 border-white bg-gold ring-4 ring-gray-50 group-hover:ring-yellow-100 transition-all"></span>
                            
                            <div class="p-3 rounded-xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                                <p class="text-sm font-bold text-gray-900 leading-snug">
                                    {{ $notification->data['message'] ?? 'Notification' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $notification->data['object'] ?? '' }}
                                </p>
                                <span class="text-[10px] font-semibold text-gray-400 mt-2 block">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="py-12 text-center">
                            <div class="inline-block p-4 rounded-full bg-gray-50 mb-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            </div>
                            <p class="text-sm text-gray-500 font-medium">Rien à signaler pour l'instant.</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- 5. TABLEAU RÉCENT (CLEAN LIST)                -->
    <!-- ============================================= -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Mouvements Récents</h3>
            <span class="text-xs font-medium text-gray-400 bg-gray-50 px-3 py-1 rounded-full">Historique</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-gray-400 border-b border-gray-50">
                        <th class="px-8 py-4 font-semibold">Objet</th>
                        <th class="px-8 py-4 font-semibold">Statut / Visa</th>
                        <th class="px-8 py-4 font-semibold text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentMovements as $history)
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            <!-- Objet -->
                            <td class="px-8 py-4">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-yellow-50 text-gold flex items-center justify-center mr-3 flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <span class="font-medium text-gray-900 truncate max-w-[250px]" title="{{ $history->memo->object ?? '' }}">
                                        {{ $history->memo->object ?? 'Document supprimé' }}
                                    </span>
                                </div>
                            </td>
                            
                            <!-- Visa -->
                            <td class="px-8 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border 
                                    {{ $history->visa == 'Validé' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                    {{ $history->visa }}
                                </span>
                            </td>
                            
                            <!-- Date -->
                            <td class="px-8 py-4 text-right text-sm text-gray-500 font-mono">
                                {{ $history->created_at->format('d/m/Y') }} <span class="text-gray-300">|</span> {{ $history->created_at->format('H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-8 py-12 text-center text-gray-400">
                                Aucun historique disponible.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-4 border-t border-gray-50">
            {{ $recentMovements->links() }}
        </div>
    </div>

</div>

<!-- ============================================== -->
<!-- 7. SCRIPTS (Charts & Logic)                    -->
<!-- ============================================== -->
<script>
    document.addEventListener('livewire:init', () => {
        let chart;

        const initChart = (categories, seriesData) => {
            var options = {
                series: [{ 
                    name: 'Mémos créés', 
                    data: seriesData 
                }],
                chart: {
                    type: 'area',
                    height: 320,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    animations: { enabled: true }
                },
                colors: ['#daaf2c'],
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: { opacityFrom: 0.5, opacityTo: 0.05 }
                },
                xaxis: {
                    categories: categories,
                    labels: { style: { colors: '#9ca3af', fontSize: '12px' } }
                },
                yaxis: {
                    min: 0,
                    forceNiceScale: true,
                    labels: { style: { colors: '#9ca3af' } }
                },
                tooltip: { theme: 'light' }
            };

            if(chart) { chart.destroy(); } // Détruire l'ancien graphique si existant
            chart = new ApexCharts(document.querySelector("#chart-timeline"), options);
            chart.render();
        };

        // Premier rendu (chargement de la page)
        initChart(@json($chartCategories), @json($chartSortants));

        // Rendu lors des mises à jour Livewire (Changement de période)
        // On utilise hook 'request' ou simplement l'écouteur d'événement
        Livewire.on('update-chart', (event) => {
            // Note : Dans Livewire 3, les paramètres sont envoyés dans un objet
            const categories = event.categories;
            const series = event.series;
            
            if(chart) {
                chart.updateOptions({
                    xaxis: { categories: categories }
                });
                chart.updateSeries([{
                    data: series
                }]);
            }
        });
    });
</script>
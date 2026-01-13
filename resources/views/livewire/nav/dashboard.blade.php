<div class="space-y-8 pb-10 transition-colors duration-300">

    <!-- ============================================= -->
    <!-- 1. STYLES LOCAUX DYNAMIQUES                   -->
    <!-- ============================================= -->
    <style>
        :root {
            --gold: #daaf2c;
            --dash-bg-card: {{ $darkMode ? '#1e1e1e' : '#ffffff' }};
            --dash-border: {{ $darkMode ? '#2d2d2d' : '#f1f1f1' }};
            --dash-text-main: {{ $darkMode ? '#ffffff' : '#111827' }};
            --dash-text-muted: {{ $darkMode ? '#a0a0a0' : '#6b7280' }};
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: var(--gold); border-radius: 20px; }
    </style>

    <!-- ============================================= -->
    <!-- 2. HEADER                                     -->
    <!-- ============================================= -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight" style="color: var(--dash-text-main);">
                Tableau de Bord
            </h2>
            <p class="font-medium mt-2" style="color: var(--dash-text-muted);">
                Vue d'ensemble de vos activités et flux documentaires.
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="hidden md:flex items-center px-4 py-2.5 border rounded-full text-sm font-semibold shadow-sm" 
                 style="background-color: var(--dash-bg-card); border-color: var(--dash-border); color: var(--dash-text-main);">
                <svg class="w-4 h-4 mr-2 text-[#daaf2c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                {{ now()->translatedFormat('d F Y') }}
            </div>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- 3. KPI CARDS                                  -->
    <!-- ============================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Carte 1 : Sortants -->
        <div class="rounded-2xl p-6 shadow-sm border transition-all group relative overflow-hidden" style="background-color: var(--dash-bg-card); border-color: var(--dash-border);">
            <div class="absolute -right-6 -top-6 w-24 h-24 {{ $darkMode ? 'bg-white/5' : 'bg-yellow-50' }} rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest" style="color: var(--dash-text-muted);">Sortants</p>
                    <h3 class="text-4xl font-black mt-3" style="color: var(--dash-text-main);">{{ $memosSortantsCount }}</h3>
                </div>
                <div class="p-3 bg-[#daaf2c] text-black rounded-xl shadow-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </div>
            </div>
            <div class="relative mt-4 flex items-center text-xs font-medium text-[#daaf2c]">
                <span>en cours d'expédition</span>
            </div>
        </div>

        <!-- Carte 2 : Entrants -->
        <div class="rounded-2xl p-6 shadow-sm border transition-all group relative overflow-hidden" style="background-color: var(--dash-bg-card); border-color: var(--dash-border);">
             <div class="absolute -right-6 -top-6 w-24 h-24 {{ $darkMode ? 'bg-white/5' : 'bg-gray-50' }} rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest" style="color: var(--dash-text-muted);">Entrants</p>
                    <h3 class="text-4xl font-black mt-3" style="color: var(--dash-text-main);">{{ $memosEntrantsCount }}</h3>
                </div>
                <div class="p-3 {{ $darkMode ? 'bg-white text-black' : 'bg-gray-900 text-white' }} rounded-xl shadow-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
            </div>
            <div class="relative mt-4 flex items-center text-xs font-medium" style="color: var(--dash-text-muted);">
                <span>en cours de cotation</span>
            </div>
        </div>

        <!-- Carte 3 : Favoris -->
        <div class="rounded-2xl p-6 shadow-sm border transition-all relative overflow-hidden" style="background-color: var(--dash-bg-card); border-color: var(--dash-border);">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest" style="color: var(--dash-text-muted);">Mes Favoris</p>
                    <h3 class="text-4xl font-black mt-3" style="color: var(--dash-text-main);">{{ $favoritesCount }}</h3>
                </div>
                <div class="p-3 {{ $darkMode ? 'bg-yellow-500/10' : 'bg-yellow-50' }} text-yellow-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
            </div>
            <div class="relative mt-4 flex items-center text-xs font-medium" style="color: var(--dash-text-muted);">
                <span>documents épinglés</span>
            </div>
        </div>
        
        <!-- Carte 4 : Archives -->
        <div class="rounded-2xl p-6 shadow-sm border transition-all border-dashed" style="background-color: var(--dash-bg-card); border-color: var(--dash-border);">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest" style="color: var(--dash-text-muted);">Mémos Archivés</p>
                    <!-- Affichage dynamique du nombre d'archives -->
                    <h3 class="text-4xl font-black mt-3" style="color: var(--dash-text-muted);">{{ $archivesCount }}</h3>
                </div>
                <div class="p-3 rounded-xl {{ $darkMode ? 'bg-white/5 text-gray-500' : 'bg-gray-100 text-gray-400' }}">
                    <!-- Icône Archive (Boîte) -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                </div>
            </div>
            <div class="mt-4 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                Dossiers clôturés
            </div>
        </div>
        
    </div>

    <!-- ============================================= -->
    <!-- 4. MAIN GRID (Graphique & Notifications)      -->
    <!-- ============================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- A. GRAPHIQUE -->
        <div class="lg:col-span-2 rounded-2xl border shadow-sm p-8" style="background-color: var(--dash-bg-card); border-color: var(--dash-border);">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="text-xl font-bold" style="color: var(--dash-text-main);">Activité</h3>
                    <p class="text-sm" style="color: var(--dash-text-muted);">Volume des mémos générés</p>
                </div>
                
                <div class="relative">
                    <select wire:model.live="chartPeriod" class="appearance-none border text-sm rounded-lg block w-full p-2.5 pr-8 cursor-pointer font-medium outline-none transition-colors"
                            style="background-color: {{ $darkMode ? '#2d2d2d' : '#f9fafb' }}; border-color: var(--dash-border); color: var(--dash-text-main);">
                        <option value="7_jours">7 derniers jours</option>
                        <option value="ce_mois">Ce mois</option>
                    </select>
                </div>
            </div>
            
            <div wire:ignore class="relative">
                <div id="chart-timeline" class="h-80 w-full"></div>
            </div>
        </div>

        <!-- B. NOTIFICATIONS -->
        <div wire:poll.10s class="rounded-2xl border shadow-sm flex flex-col h-full overflow-hidden" style="background-color: var(--dash-bg-card); border-color: var(--dash-border);">
            @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp

            <div class="p-6 border-b flex justify-between items-center {{ $darkMode ? 'bg-white/5' : 'bg-gray-50/50' }}" style="border-color: var(--dash-border);">
                <h3 class="font-bold" style="color: var(--dash-text-main);">Notifications</h3>
                @if($unreadCount > 0)
                    <button wire:click="markAllNotificationsAsRead" class="text-[10px] uppercase font-bold tracking-wider text-[#daaf2c] hover:text-yellow-600 transition">Tout effacer</button>
                @else
                     <span class="text-[10px] uppercase font-bold tracking-wider" style="color: var(--dash-text-muted);">À jour</span>
                @endif
            </div>

            <div class="flex-1 overflow-y-auto max-h-[400px] p-4 custom-scrollbar">
                <ul class="relative border-l-2 ml-3 space-y-6" style="border-color: var(--dash-border);">
                    @forelse(auth()->user()->unreadNotifications as $notification)
                        <li wire:key="{{ $notification->id }}" wire:click="markNotificationAsRead('{{ $notification->id }}')" class="mb-2 ml-6 group cursor-pointer">
                            <span class="absolute -left-[9px] mt-1.5 h-4 w-4 rounded-full border-2 bg-[#daaf2c] ring-4 transition-all" style="border-color: var(--dash-bg-card); ring-color: var(--dash-bg-card);"></span>
                            <div class="p-3 rounded-xl transition-colors border border-transparent {{ $darkMode ? 'hover:bg-white/5' : 'hover:bg-gray-50' }}">
                                <p class="text-sm font-bold leading-snug" style="color: var(--dash-text-main);">{{ $notification->data['message'] ?? 'Notification' }}</p>
                                <p class="text-xs mt-1" style="color: var(--dash-text-muted);">{{ $notification->data['object'] ?? '' }}</p>
                                <span class="text-[10px] font-semibold mt-2 block" style="color: var(--dash-text-muted);">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="py-12 text-center">
                            <div class="inline-block p-4 rounded-full bg-gray-50/10 mb-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            </div>
                            <p class="text-sm font-medium" style="color: var(--dash-text-muted);">Rien à signaler.</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- 5. TABLEAU RÉCENT                             -->
    <!-- ============================================= -->
    <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color: var(--dash-bg-card); border-color: var(--dash-border);">
        <div class="px-8 py-6 border-b flex items-center justify-between" style="border-color: var(--dash-border);">
            <h3 class="text-lg font-bold" style="color: var(--dash-text-main);">Mouvements Récents</h3>
            <span class="text-xs font-medium px-3 py-1 rounded-full {{ $darkMode ? 'bg-white/5 text-gray-400' : 'bg-gray-50 text-gray-400' }}">Historique</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs uppercase tracking-wider border-b" style="color: var(--dash-text-muted); border-color: var(--dash-border);">
                        <th class="px-8 py-4 font-semibold">Objet</th>
                        <th class="px-8 py-4 font-semibold">Statut</th>
                        <th class="px-8 py-4 font-semibold text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--dash-border);">
                    @forelse($recentMovements as $history)
                        <tr class="transition-colors group {{ $darkMode ? 'hover:bg-white/5' : 'hover:bg-gray-50' }}">
                            <td class="px-8 py-4">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full flex items-center justify-center mr-3 flex-shrink-0 {{ $darkMode ? 'bg-[#daaf2c]/20' : 'bg-yellow-50' }} text-[#daaf2c]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <span class="font-medium truncate max-w-[250px]" style="color: var(--dash-text-main);">{{ $history->memo->object ?? 'Document supprimé' }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border 
                                    {{ $history->visa == 'Validé' ? 'bg-green-500/10 text-green-500 border-green-500/20' : 'bg-gray-500/10 text-gray-500 border-gray-500/20' }}">
                                    {{ $history->visa }}
                                </span>
                            </td>
                            <td class="px-8 py-4 text-right text-sm font-mono" style="color: var(--dash-text-muted);">
                                {{ $history->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-8 py-12 text-center text-gray-400">Aucun historique.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 7. SCRIPTS APEXCHARTS                          -->
<!-- ============================================== -->
<script>
    document.addEventListener('livewire:init', () => {
        let chart;
        let isDark = {{ $darkMode ? 'true' : 'false' }};

        const getChartOptions = (categories, seriesData, dark) => {
            return {
                series: [{ name: 'Mémos créés', data: seriesData }],
                chart: {
                    type: 'area', height: 320, fontFamily: 'inherit',
                    toolbar: { show: false },
                    animations: { enabled: true }
                },
                colors: ['#daaf2c'],
                stroke: { curve: 'smooth', width: 3 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.5, opacityTo: 0.05 } },
                xaxis: {
                    categories: categories,
                    labels: { style: { colors: dark ? '#a0a0a0' : '#9ca3af', fontSize: '12px' } }
                },
                yaxis: {
                    labels: { style: { colors: dark ? '#a0a0a0' : '#9ca3af' } }
                },
                grid: { borderColor: dark ? '#2d2d2d' : '#f1f1f1' },
                tooltip: { theme: dark ? 'dark' : 'light' }
            };
        };

        const initChart = (categories, seriesData) => {
            if(chart) { chart.destroy(); }
            chart = new ApexCharts(document.querySelector("#chart-timeline"), getChartOptions(categories, seriesData, isDark));
            chart.render();
        };

        initChart(@json($chartCategories), @json($chartSortants));

        // Mise à jour lors du changement de période
        Livewire.on('update-chart', (event) => {
            if(chart) {
                chart.updateOptions({ xaxis: { categories: event.categories } });
                chart.updateSeries([{ data: event.series }]);
            }
        });

        // Mise à jour en direct lors du basculement Dark Mode
        Livewire.on('dark-mode-toggled', (event) => {
            isDark = event.darkMode;
            if(chart) {
                chart.updateOptions({
                    xaxis: { labels: { style: { colors: isDark ? '#a0a0a0' : '#9ca3af' } } },
                    yaxis: { labels: { style: { colors: isDark ? '#a0a0a0' : '#9ca3af' } } },
                    grid: { borderColor: isDark ? '#2d2d2d' : '#f1f1f1' },
                    tooltip: { theme: isDark ? 'dark' : 'light' }
                });
            }
        });
    });
</script>


    
    <div class="flex flex-col flex-grow"> {{-- 'flex-grow' permet à ce div de prendre tout l'espace horizontal restant --}}

        <header 
    id="navbar" 
    class="sticky top-0 z-40 w-full flex items-center justify-between h-28 px-4 sm:px-6 bg-white/90 backdrop-blur-md border-b border-slate-200 shadow-sm transition-all duration-300"
>
    
    <!-- SECTION GAUCHE : Toggle + Titre -->
    <div class="flex items-center gap-4">
        
        <!-- Bouton Sidebar avec effet hover -->
        <button 
            id="navbarToggleBtn" 
            class="p-2 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-colors"
            aria-label="Toggle Sidebar"
        >
            <!-- Icône Hamburger plus moderne -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
        </button>

        <!-- Titre de la page -->
        <div class="flex flex-col">
            <span 
                id="navbarTitle" 
                class="text-lg font-bold text-slate-800 tracking-tight leading-none"
            >
                {{ $navbarTitle ?? 'Dashboard' }}
            </span>
            <!-- Petit fil d'ariane optionnel (Breadcrumb) -->
            <span class="text-[10px] uppercase font-semibold text-slate-400 tracking-wider">
                Espace de travail
            </span>
        </div>
    </div>

    <!-- SECTION DROITE : Notifications + Profil -->
    <div class="flex items-center gap-2 sm:gap-4">
        
        <!-- Icône Notifications (Lien avec votre système précédent) -->
        <button 
            wire:click="selectTab('notifications')" 
            class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors rounded-full hover:bg-slate-50"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            
            <!-- Badge Rouge (Dynamique) -->
           
                <span class="absolute top-1.5 right-1.5 flex h-2.5 w-2.5">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                </span>
          
        </button>

        <!-- Séparateur Vertical -->
        <div class="h-8 w-px bg-slate-200 mx-1 hidden sm:block"></div>

        <!-- Dropdown Utilisateur -->
        <div class="relative group">
            <button class="flex items-center gap-3 p-1 rounded-full hover:bg-slate-50 transition-all border border-transparent hover:border-slate-200 focus:outline-none">
                
                <!-- Avatar avec bordure -->
                <div class="relative">
                    <img 
                        src="{{ asset('images/user3.png') }}" 
                        alt="Avatar" 
                        class="h-9 w-9 rounded-full object-cover border-2 border-white shadow-sm group-hover:shadow-md transition-shadow"
                    >
                    <!-- Indicateur de statut (Connecté) -->
                    <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white bg-green-500"></span>
                </div>

                <!-- Nom et Rôle (Masqué sur mobile) -->
                <div class="hidden md:flex flex-col items-start text-sm mr-2">
                    <span class="font-bold text-slate-700 leading-none group-hover:text-slate-900">
                        {{ auth()->user()->first_name }}
                    </span>
                    <span class="text-[10px] text-slate-500 font-medium">
                        {{ auth()->user()->poste ?? 'Utilisateur' }}
                    </span>
                </div>

                <!-- Chevron bas -->
                <svg class="w-4 h-4 text-slate-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <!-- Menu Dropdown (Optionnel, au cas où vous voulez l'ajouter) -->
            <!-- 
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 focus:outline-none hidden group-hover:block z-50 animate-fade-in-down">
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Déconnexion</a>
            </div> 
            -->
        </div>

    </div>
</header>

        {{-- Le Contenu Principal (sous la Navbar) --}}
        <main class="content"> {{-- Cette classe est déjà définie dans votre CSS pour prendre l'espace et gérer le défilement --}}
           {{-- Ici, le p-4 ajoute un padding général au contenu défilant --}}

           @if($currentContent == 'dashboard-content')

                <!-- Dashboard Container -->
                <div class="space-y-6">

                    <!-- 1. HEADER : Bienvenue & Date -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                                Tableau de Bord
                            </h2>
                            <p class="text-sm text-slate-500 mt-1">
                                Vue d'ensemble de vos activités et flux documentaires.
                            </p>
                        </div>
                        
                        <div class="mt-4 md:mt-0 flex items-center gap-3">
                            <!-- Date Widget -->
                            <div class="hidden md:flex items-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 shadow-sm">
                                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ now()->translatedFormat('d F Y') }}
                            </div>
                            
                            <!-- Bouton Création Rapide -->
                            <button wire:click="openModal" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-lg flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Nouveau Mémo
                            </button>
                        </div>
                    </div>

                    <!-- 2. KPI CARDS (Inspiration Bancaire) -->
                    <!-- Grid de 4 cartes avec indicateurs de progression -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        
                        <!-- Carte 1 : Mémos Sortants -->
                        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                            <div class="absolute right-0 top-0 h-full w-1 bg-yellow-500 group-hover:w-2 transition-all"></div>
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mémos Sortants</p>
                                    <h3 class="text-3xl font-bold text-slate-900 mt-2">24</h3>
                                </div>
                                <div class="p-2 bg-yellow-50 rounded-lg text-yellow-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-xs">
                                <span class="text-green-600 font-bold bg-green-50 px-1.5 py-0.5 rounded flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                    +12%
                                </span>
                                <span class="text-slate-400 ml-2">depuis hier</span>
                            </div>
                        </div>

                        <!-- Carte 2 : Mémos Entrants (Pending) -->
                        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                            <div class="absolute right-0 top-0 h-full w-1 bg-blue-500 group-hover:w-2 transition-all"></div>
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mémos Entrants</p>
                                    <h3 class="text-3xl font-bold text-slate-900 mt-2">08</h3>
                                </div>
                                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-xs">
                                <span class="text-orange-600 font-bold bg-orange-50 px-1.5 py-0.5 rounded flex items-center">
                                    3 Urgents
                                </span>
                                <span class="text-slate-400 ml-2">à traiter</span>
                            </div>
                        </div>

                        <!-- Carte 3 : Courriers -->
                        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                            <div class="absolute right-0 top-0 h-full w-1 bg-purple-500 group-hover:w-2 transition-all"></div>
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Courriers</p>
                                    <h3 class="text-3xl font-bold text-slate-900 mt-2">142</h3>
                                </div>
                                <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-xs">
                                <span class="text-slate-500 font-medium">Archive globale</span>
                            </div>
                        </div>

                        <!-- Carte 4 : Validation Requise (Action) -->
                        <div class="bg-slate-900 rounded-xl p-6 shadow-lg shadow-slate-300 transform hover:-translate-y-1 transition-transform cursor-pointer">
                            <div class="flex justify-between items-start text-white">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">À Valider</p>
                                    <h3 class="text-3xl font-bold mt-2">5</h3>
                                </div>
                                <div class="p-2 bg-slate-800 rounded-lg text-yellow-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                            </div>
                            <div class="mt-4 border-t border-slate-700 pt-2">
                                <p class="text-xs text-slate-300">Documents en attente de votre signature.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 3. MAIN SECTION : Graphe & Notifications -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <!-- A. GRAPHE DE SUIVI (2/3 largeur) -->
                        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-bold text-slate-800">Flux de Création</h3>
                                <select class="bg-slate-50 border-none text-xs rounded-md text-slate-600 py-1 px-3 focus:ring-0 cursor-pointer">
                                    <option>7 derniers jours</option>
                                    <option>Ce mois</option>
                                    <option>Cette année</option>
                                </select>
                            </div>
                            
                            <!-- Zone du Graphique (Placeholder pour ApexCharts) -->
                            <div id="chart-timeline" class="h-80 w-full"></div>
                        </div>

                        <!-- B. ZONE DE NOTIFICATIONS & ACTIVITÉS (1/3 largeur) -->
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col h-full">
                            <!-- Header Notifs -->
                            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-slate-800">Notifications</h3>
                                <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-full">3 new</span>
                            </div>

                            <!-- Liste Scrollable -->
                            <div class="flex-1 overflow-y-auto max-h-[350px] p-2">
                                <ul class="space-y-1">
                                    <!-- Item 1 -->
                                    <li class="hover:bg-slate-50 p-3 rounded-lg transition-colors cursor-pointer group">
                                        <div class="flex items-start gap-3">
                                            <div class="bg-blue-100 text-blue-600 rounded-full p-2 mt-1 shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-800 group-hover:text-blue-600 transition">Nouveau mémo reçu</p>
                                                <p class="text-xs text-slate-500">Service Comptabilité - "Factures proforma"</p>
                                                <p class="text-[10px] text-slate-400 mt-1">Il y a 10 min</p>
                                            </div>
                                        </div>
                                    </li>

                                    <!-- Item 2 -->
                                    <li class="hover:bg-slate-50 p-3 rounded-lg transition-colors cursor-pointer group">
                                        <div class="flex items-start gap-3">
                                            <div class="bg-yellow-100 text-yellow-600 rounded-full p-2 mt-1 shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-800 group-hover:text-yellow-600 transition">Validation en attente</p>
                                                <p class="text-xs text-slate-500">Réf: #MEM-2023-089</p>
                                                <p class="text-[10px] text-slate-400 mt-1">Il y a 2h</p>
                                            </div>
                                        </div>
                                    </li>

                                    <!-- Item 3 -->
                                    <li class="hover:bg-slate-50 p-3 rounded-lg transition-colors cursor-pointer group">
                                        <div class="flex items-start gap-3">
                                            <div class="bg-green-100 text-green-600 rounded-full p-2 mt-1 shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-800 group-hover:text-green-600 transition">Mémo #402 Approuvé</p>
                                                <p class="text-xs text-slate-500">Par Directeur Général</p>
                                                <p class="text-[10px] text-slate-400 mt-1">Hier, 16:30</p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Footer Notifs -->
                            <div class="p-3 border-t border-slate-100 text-center">
                                <button class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline">Voir tout l'historique</button>
                            </div>
                        </div>
                    </div>

                    <!-- 4. SECTION TABLEAU RÉCENT (Style "Table Financière") -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Derniers Mouvements</h3>
                            <button class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg></button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead>
                                    <tr class="text-slate-500 border-b border-slate-100">
                                        <th class="px-6 py-3 font-medium">Référence</th>
                                        <th class="px-6 py-3 font-medium">Objet</th>
                                        <th class="px-6 py-3 font-medium">Statut</th>
                                        <th class="px-6 py-3 font-medium">Date</th>
                                        <th class="px-6 py-3 font-medium text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <!-- Row 1 -->
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="px-6 py-3 font-mono text-slate-600 font-bold">#REF-2023-001</td>
                                        <td class="px-6 py-3 text-slate-800 font-medium">Rapport Financier T3</td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Envoyé
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-slate-500">29 Nov 2025</td>
                                        <td class="px-6 py-3 text-right">
                                            <button class="text-slate-400 hover:text-blue-600 group-hover:opacity-100 opacity-0 transition-opacity">Voir</button>
                                        </td>
                                    </tr>
                                    <!-- Row 2 -->
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="px-6 py-3 font-mono text-slate-600 font-bold">#REF-2023-002</td>
                                        <td class="px-6 py-3 text-slate-800 font-medium">Demande de congés</td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-slate-500">30 Nov 2025</td>
                                        <td class="px-6 py-3 text-right">
                                            <button class="text-slate-400 hover:text-blue-600 group-hover:opacity-100 opacity-0 transition-opacity">Voir</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- SCRIPT POUR LE GRAPHE (ApexCharts) -->
                <!-- À mettre idéalement dans votre layout principal ou pushé dans un stack 'scripts' -->
                <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
                <script>
                    document.addEventListener('livewire:initialized', () => {
                        var options = {
                            series: [{
                                name: 'Mémos Entrants',
                                data: [31, 40, 28, 51, 42, 109, 100]
                            }, {
                                name: 'Mémos Sortants',
                                data: [11, 32, 45, 32, 34, 52, 41]
                            }],
                            chart: {
                                height: 320,
                                type: 'area', // Look "bancaire" moderne
                                fontFamily: 'inherit',
                                toolbar: { show: false }
                            },
                            colors: ['#3b82f6', '#eab308'], // Bleu et Jaune (Vos couleurs)
                            dataLabels: { enabled: false },
                            stroke: { curve: 'smooth', width: 2 },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.3,
                                    opacityTo: 0.05,
                                    stops: [0, 90, 100]
                                }
                            },
                            xaxis: {
                                categories: ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"],
                                axisBorder: { show: false },
                                axisTicks: { show: false }
                            },
                            yaxis: { show: false }, // Minimaliste
                            grid: {
                                borderColor: '#f1f5f9',
                                strokeDashArray: 4,
                            },
                            tooltip: {
                                theme: 'light'
                            }
                        };

                        var chart = new ApexCharts(document.querySelector("#chart-timeline"), options);
                        chart.render();
                    });
                </script>

            @endif

            @if($currentContent == 'memos-content')
                @livewire('memos.memos')
            @endif

            @if($currentContent == 'profile-content')
                @livewire('setting.profil')
            @endif

            @if($currentContent == 'courriers-content')
                @livewire('courriers.courriers')
            @endif

            <!-- On change ici 'reports-content' par 'notifications-content' pour être logique -->
@if($currentContent == 'reports-content')
               
    <div class="max-w-5xl mx-auto space-y-6 font-sans">

        <!-- 1. HEADER -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Centre de Notifications</h2>
                <p class="text-slate-500 text-sm">Gérez vos alertes et le suivi de vos activités.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button wire:click="markAllAsRead" class="text-sm text-slate-600 hover:text-slate-900 font-medium bg-white border border-slate-300 px-4 py-2 rounded-lg shadow-sm transition hover:bg-slate-50">
                    <i class="fa-solid fa-check-double mr-2"></i> Tout marquer comme lu
                </button>
            </div>
        </div>

        <!-- 2. FILTRES -->
        <div class="flex space-x-1 bg-slate-100 p-1 rounded-xl w-fit">
            <button wire:click="" class="px-4 py-2 text-sm font-medium rounded-lg transition-all ">
                Toutes
            </button>
            <button wire:click="" class="px-4 py-2 text-sm font-medium rounded-lg transition-all ">
                Non lues 
                
                    <span class="ml-1 bg-blue-100 text-blue-700 text-xs px-1.5 py-0.5 rounded-full">0</span>
              
            </button>
        </div>

        <!-- 3. LISTE DES NOTIFICATIONS DYNAMIQUE -->
        <div class="space-y-8">

            <!-- On vérifie s'il y a des notifications -->
           
                
                <!-- On groupe par jour (Optionnel, ici liste simple triée) -->
                <div class="group relative bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer 
                    ">
                    
                    <!-- Indicateur "Non Lu" -->
                 
                        <div class="absolute top-4 right-4 h-2 w-2 bg-blue-500 rounded-full" title="Non lu"></div>
                    

                    <div class="flex items-start gap-4">
                        <!-- Icone Contextuelle basée sur le type de notif -->
                        <div class="shrink-0 h-12 w-12 rounded-full flex items-center justify-center 
                            ">
                            
                            <!-- Vous pouvez personnaliser l'icône selon $notification->type -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        </div>

                        <div class="flex-1">
                            <div class="flex justify-between items-start pr-6">
                                <!-- Titre (Assurez-vous de stocker 'title' dans data) -->
                                <h4 class="text-sm font-bold text-slate-800"></h4>
                                <span class="text-xs text-slate-400 font-mono"></span>
                            </div>
                            
                            <!-- Message -->
                            <p class="text-sm text-slate-600 mt-1 line-clamp-1"></p>
                            
                            <!-- Actions au survol -->
                            <div class="mt-3 flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                
                                    <button wire:click="" class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-md">
                                        Marquer comme lu
                                    </button>
                                
                                <button wire:click="" class="text-xs text-red-500 hover:text-red-700">Supprimer</button>
                            </div>
                        </div>
                    </div>
                </div>

           
               

        </div>

        <!-- PAGINATION -->
        <div class="pt-6 flex justify-center">
             <!-- Si vous utilisez paginate() dans le composant Livewire -->
              $notifications->links() 
        </div>

    </div>        

@endif

            @if($currentContent == 'settings-content')reports-content
               @livewire('setting.settings')
            @endif

        </main>
        
    </div>

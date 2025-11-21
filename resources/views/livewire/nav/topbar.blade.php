

    
    <div class="flex flex-col flex-grow"> {{-- 'flex-grow' permet à ce div de prendre tout l'espace horizontal restant --}}

        {{-- La Navbar (Topbar) --}}
        <header class="navbar" id="navbar">
            <div class="navbar-left">
                <button class="navbar-toggle-sidebar-btn" id="navbarToggleBtn"><i class="fas fa-bars"></i></button>
                <span class="navbar-title" id="navbarTitle">{{ $navbarTitle }}</span>
            </div>
            <div class="navbar-right">
                <a href="#" class="navbar-icon"><i class="fas fa-bell"></i></a>
                <span class="notification-badge">3</span>
                <a href="#" class="navbar-icon"><i class="fas fa-cog"></i></a>
                <div class="navbar-user-dropdown">
                    <img src="{{ asset('images/user3.png') }}" alt="User Avatar" class="navbar-user-avatar">
                    <span class="navbar-user-name">  {{ auth()->user()->first_name }} </span>
                </div>
            </div>
        </header>

        {{-- Le Contenu Principal (sous la Navbar) --}}
        <main class="content"> {{-- Cette classe est déjà définie dans votre CSS pour prendre l'espace et gérer le défilement --}}
           {{-- Ici, le p-4 ajoute un padding général au contenu défilant --}}

            @if($currentContent == 'dashboard-content')
                
                <div class="dashboard-cards-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    @livewire('card.dashboard-card', [
                        'title' => 'Memoriums',
                        'value' => '€12,345',
                        'description' => 'Par rapport au mois dernier',
                        'icon' => 'fas fa-file-alt',
                        'trend' => 'up',
                        'trendValue' => '+12%'
                    ])

                    @livewire('card.dashboard-card', [
                        'title' => 'Courrier',
                        'value' => '876',
                        'description' => 'Augmentation cette semaine',
                        'icon' => 'fas fa-envelope',
                        'trend' => 'up',
                        'trendValue' => '+5%'
                    ])

                    @livewire('card.dashboard-card', [
                        'title' => 'Courrier',
                        'value' => '876',
                        'description' => 'Augmentation cette semaine',
                        'icon' => 'fas fa-envelope',
                        'trend' => 'up',
                        'trendValue' => '+5%'
                    ])

                    @livewire('card.dashboard-card', [
                        'title' => 'Courrier',
                        'value' => '876',
                        'description' => 'Augmentation cette semaine',
                        'icon' => 'fas fa-envelope',
                        'trend' => 'up',
                        'trendValue' => '+5%'
                    ])



                    
                </div>

            @endif

            @if($currentContent == 'memos-content')
                @livewire('memos.memos')
            @endif

             @if($currentContent == 'analytic-content')

                        <!-- DÉBUT DU COMPOSANT CIRCUIT TIMELINE -->
<div class="w-full max-w-5xl mx-auto p-4 relative font-sans text-slate-200">

    <!-- Ligne Verticale Centrale (Le "Bus" du circuit) -->
    <!-- Sur mobile : ligne à gauche. Sur desktop : ligne au centre -->
    <div class="absolute left-8 md:left-1/2 top-0 bottom-0 w-0.5 bg-gradient-to-b from-slate-700 via-slate-600 to-slate-800 md:-translate-x-1/2"></div>

    <!-- ÉTAPE 1 : TERMINÉ (Droite) -->
    <div class="relative flex flex-col md:flex-row items-center justify-between mb-12 group">
        <!-- Espaceur pour centrer sur desktop -->
        <div class="hidden md:block w-5/12"></div>
        
        <!-- Point de connexion (Noeud) -->
        <div class="absolute left-8 md:left-1/2 -translate-x-1/2 w-4 h-4 bg-emerald-500 rounded-full border-4 border-slate-900 shadow-[0_0_10px_#10b981] z-10"></div>

        <!-- Contenu de l'étape -->
        <div class="w-[calc(100%-4rem)] ml-16 md:ml-0 md:w-5/12 relative">
            <!-- Trait de liaison horizontal -->
            <div class="absolute top-4 -left-8 md:-left-10 w-8 md:w-10 h-0.5 bg-emerald-500/50"></div>
            
            <!-- Carte -->
            <div class="bg-slate-800/80 border-l-4 border-emerald-500 p-5 rounded-r-lg rounded-bl-lg backdrop-blur-sm hover:bg-slate-800 transition-colors">
                <span class="text-emerald-400 text-xs font-bold tracking-widest uppercase">Phase 01 • Terminée</span>
                <h3 class="text-xl font-bold text-white mt-1">Initialisation</h3>
                <p class="text-slate-400 text-sm mt-2">Définition du cahier des charges et stack technique.</p>
            </div>
        </div>
    </div>

    <!-- ÉTAPE 2 : EN COURS (Gauche - Effet Actif) -->
    <div class="relative flex flex-col md:flex-row-reverse items-center justify-between mb-12 group">
        <div class="hidden md:block w-5/12"></div>
        
        <!-- Point de connexion (Pulsation) -->
        <div class="absolute left-8 md:left-1/2 -translate-x-1/2 z-10">
            <div class="w-4 h-4 bg-cyan-500 rounded-full border-2 border-white shadow-[0_0_15px_#06b6d4]"></div>
            <div class="absolute top-0 left-0 w-4 h-4 bg-cyan-500 rounded-full animate-ping opacity-75"></div>
        </div>

        <!-- Contenu -->
        <div class="w-[calc(100%-4rem)] ml-16 md:ml-0 md:mr-0 md:w-5/12 relative">
            <!-- Trait de liaison -->
            <div class="absolute top-4 -left-8 md:left-auto md:-right-10 w-8 md:w-10 h-0.5 bg-cyan-500 shadow-[0_0_8px_#06b6d4]"></div>

            <!-- Carte Active -->
            <div class="bg-slate-800 border border-cyan-500/50 shadow-[0_0_20px_rgba(6,182,212,0.15)] p-5 rounded-xl relative overflow-hidden">
                <!-- Effet de scan arrière plan -->
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-cyan-500/5 to-transparent pointer-events-none"></div>
                
                <div class="flex justify-between items-start">
                    <div>
                        <span class="inline-flex items-center gap-2 text-cyan-400 text-xs font-bold tracking-widest uppercase">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                            En développement
                        </span>
                        <h3 class="text-xl font-bold text-white mt-1">Développement API</h3>
                    </div>
                    <span class="text-xs font-mono text-cyan-300 border border-cyan-900 bg-cyan-900/30 px-2 py-1 rounded">v0.8.2</span>
                </div>
                
                <p class="text-slate-300 text-sm mt-3">Intégration des endpoints et tests de charge en cours.</p>
                
                <!-- Barre de progression -->
                <div class="w-full bg-slate-700 h-1 mt-4 rounded-full overflow-hidden">
                    <div class="bg-cyan-400 h-full w-3/4 shadow-[0_0_10px_#22d3ee]"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ÉTAPE 3 : À VENIR (Droite) -->
    <div class="relative flex flex-col md:flex-row items-center justify-between mb-12 group opacity-60 hover:opacity-100 transition-opacity duration-300">
        <div class="hidden md:block w-5/12"></div>
        
        <!-- Point de connexion (Inactif) -->
        <div class="absolute left-8 md:left-1/2 -translate-x-1/2 w-3 h-3 bg-slate-900 border-2 border-slate-600 rounded-full z-10"></div>

        <!-- Contenu -->
        <div class="w-[calc(100%-4rem)] ml-16 md:ml-0 md:w-5/12 relative">
            <div class="absolute top-4 -left-8 md:-left-10 w-8 md:w-10 h-px bg-slate-700 border-t border-dashed border-slate-500"></div>
            
            <div class="bg-slate-900 border border-slate-700 border-dashed p-5 rounded-lg">
                <span class="text-slate-500 text-xs font-bold tracking-widest uppercase">Phase 03 • Future</span>
                <h3 class="text-lg font-bold text-slate-300 mt-1">Recette & QA</h3>
                <p class="text-slate-500 text-sm mt-2">Phase de validation utilisateur.</p>
            </div>
        </div>
    </div>

    <!-- ÉTAPE 4 : À VENIR (Gauche) -->
    <div class="relative flex flex-col md:flex-row-reverse items-center justify-between mb-12 group opacity-40">
        <div class="hidden md:block w-5/12"></div>
        
        <div class="absolute left-8 md:left-1/2 -translate-x-1/2 w-3 h-3 bg-slate-900 border border-slate-700 rounded-full z-10"></div>

        <div class="w-[calc(100%-4rem)] ml-16 md:ml-0 md:mr-0 md:w-5/12 relative">
             <div class="absolute top-4 -left-8 md:left-auto md:-right-10 w-8 md:w-10 h-px bg-slate-800"></div>
            <div class="p-4 rounded-lg border border-transparent">
                <h3 class="text-md font-semibold text-slate-500">Déploiement Final</h3>
            </div>
        </div>
    </div>

</div>
<!-- FIN DU COMPOSANT -->
               

            @endif

        </main>
        
    </div>



    
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
                @livewire('nav.dashboard')
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
                        
               xvxcvcvxcvxcv

            @endif

            @if($currentContent == 'settings-content')
              @livewire('setting.settings')
            @endif

        </main>


        {{-- ============================================================ --}}
        {{-- ZONE ABSOLUE : MODALE DE BLOCAGE (PROFIL INCOMPLET)          --}}
        {{-- ============================================================ --}}
        
        @auth
                {{-- CAS 1 : COMPTE DÉSACTIVÉ (Prioritaire) --}}
                @if(auth()->user()->isInactive())
                    
                    <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/95 backdrop-blur-sm transition-opacity duration-300">
                        <!-- Bordure rouge pour le danger -->
                        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-8 text-center border-t-4 border-red-500 animate-bounce-in transform scale-100">
                            
                            <!-- Icône Cadenas Rouge -->
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-6 animate-pulse">
                                <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>

                            <h2 class="text-2xl font-bold text-slate-800 mb-2">Accès Restreint</h2>
                            
                            <p class="text-slate-600 mb-8 leading-relaxed text-sm">
                                Votre compte a été désactivé par l'administrateur. Vous ne pouvez plus effectuer d'actions sur cette plateforme pour le moment.
                            </p>

                            <div class="flex justify-center">
                                <!-- Bouton Déconnexion (Action logique ici) -->
                                <form method="GET" action="{{ route('login') }}">
                                    @csrf
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center transform hover:-translate-y-1">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                        <span>Se connecter</span>
                                    </button>
                                </form>
                            </div>
                            
                            <p class="mt-6 text-xs text-slate-400">
                                Veuillez contacter votre responsable IT pour plus d'infos.
                            </p>
                        </div>
                    </div>

                {{-- CAS 2 : PROFIL INCOMPLET (Seulement si le compte est actif) --}}
                @elseif(auth()->user()->hasIncompleteProfile() && $currentContent !== 'profile-content')
                    
                    <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/90 backdrop-blur-sm transition-opacity duration-300">
                        <!-- Bordure jaune/or -->
                        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-8 text-center border-t-4 border-[#b8962f] animate-bounce-in transform scale-100">
                            
                            <!-- Icône Attention Jaune -->
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-50 mb-6 animate-pulse">
                                <svg class="h-10 w-10 text-[#b8962f]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>

                            <h2 class="text-2xl font-bold text-slate-800 mb-2">Profil Incomplet</h2>
                            
                            <p class="text-slate-600 mb-8 leading-relaxed text-sm">
                                Bienvenue sur Memo App. Pour accéder à vos mémos et utiliser le workflow de signature, vous devez impérativement compléter vos informations (Poste, Entité, etc.).
                            </p>

                            <div class="flex justify-center">
                                <button 
                                    wire:click="forceGoToProfile"
                                    class="bg-[#b8962f] hover:bg-[#a48425] text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center transform hover:-translate-y-1"
                                >
                                    <span>Compléter mon profil</span>
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                </button>
                            </div>
                            
                            <p class="mt-6 text-xs text-slate-400">
                                Cette action est requise une seule fois.
                            </p>
                        </div>
                    </div>

                @endif
            @endauth
        
    </div>

<div class="flex flex-col flex-grow h-screen overflow-hidden bg-[#f9fafb]"> <!-- Fond très clair pour le contenu -->

    

    <!-- ======================= -->
    <!-- NAVBAR (En-tête)        -->
    <!-- ======================= -->
    <header id="navbar" class="sticky top-0 z-40 w-full flex items-center justify-between h-20 px-4 sm:px-6 bg-white/80 backdrop-blur-xl border-b border-gray-100 transition-all duration-300">
        
        <!-- SECTION GAUCHE -->
        <div class="flex items-center gap-4">
            <!-- Bouton Mobile -->
            <button wire:click="$parent.toggleSidebar" class="p-2 rounded-lg text-[#707173] hover:text-[#000000] hover:bg-[#daaf2c]/10 focus:outline-none focus:ring-2 focus:ring-[#daaf2c] transition-colors md:hidden">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
            </button>

            <!-- Titre de la page -->
            <div class="flex flex-col">
                <span id="navbarTitle" class="text-xl font-bold tracking-tight leading-none" style="color: var(--c-dark);">
                    {{ $navbarTitle ?? 'Dashboard' }}
                </span>
                <span class="text-[11px] uppercase font-bold tracking-widest mt-1" style="color: var(--c-secondary);">
                    Espace de travail
                </span>
            </div>
        </div>

        <!-- SECTION DROITE -->
        <div class="flex items-center gap-3 sm:gap-6">
            
            <!-- Notification Button -->
            <button wire:click="selectTab('notifications')" class="relative p-2.5 rounded-full text-[#707173] hover:text-[#daaf2c] hover:bg-[#daaf2c]/10 transition-all duration-200 group">
                <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="absolute top-2 right-2 flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500 border border-white"></span>
                    </span>
                @endif
            </button>

            <!-- Séparateur vertical -->
            <div class="h-8 w-px bg-gray-200 hidden sm:block"></div>

            <!-- Avatar / Profil -->
            <div class="relative group">
                <button wire:click="selectTab('profile')" class="flex items-center gap-3 p-1 pr-3 rounded-full hover:bg-gray-50 transition-all border border-transparent hover:border-gray-200">
                    <div class="relative">
                        <!-- Avatar avec contour fin -->
                        <img src="{{ asset('images/user3.png') }}" alt="Avatar" class="h-10 w-10 rounded-full object-cover border shadow-sm group-hover:ring-2 group-hover:ring-[#daaf2c] transition-all">
                        <!-- Statut en ligne -->
                        <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full ring-2 ring-white bg-green-500"></span>
                    </div>
                    <div class="hidden md:flex flex-col items-start text-sm">
                        <span class="font-bold leading-none" style="color: var(--c-dark);">{{ auth()->user()->first_name }}</span>
                        <span class="text-[10px] font-medium mt-1" style="color: var(--c-secondary);">{{ auth()->user()->poste ?? 'Utilisateur' }}</span>
                    </div>
                    <!-- Petite flèche vers le bas -->
                    <svg class="w-4 h-4 text-[#707173] hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- ======================= -->
    <!-- CONTENU PRINCIPAL       -->
    <!-- ======================= -->
    <main class="flex-1 overflow-y-auto p-4 sm:p-6 custom-scrollbar relative">
        <!-- Fond décoratif subtil (optionnel) -->
        <div class="absolute inset-0 pointer-events-none opacity-[0.02]" 
             style="background-image: radial-gradient(#000000 1px, transparent 1px); background-size: 24px 24px;">
        </div>

        <div class="relative z-10 max-w-7xl mx-auto">
            @switch($currentContent)
                @case('dashboard-content')
                    @livewire('nav.dashboard')
                    @break
                @case('memos-content')
                    @livewire('memos.memos')
                    @break
                @case('profile-content')
                    @livewire('setting.profil')
                    @break
                @case('courriers-content')
                    @livewire('courriers.courriers')
                    @break
                @case('notifications-content')
                    @livewire('notifications.notifications-dropdown')
                    @break
                @case('settings-content')
                    @livewire('setting.settings')
                    @break
                @case('settings-tasks')
                    @livewire('setting.tasks')
                    @break  
                @case('settings-calendar')
                    @livewire('setting.calendar')
                    @break  
                @case('settings-documents')
                    @livewire('setting.documents')
                    @break 
                @case('settings-reports')
                    @livewire('setting.reports')
                    @break       
                @default
                    @livewire('nav.dashboard')
            @endswitch
        </div>
    </main>

    <!-- ======================= -->
    <!-- MODALS (Overlay)        -->
    <!-- ======================= -->
    @auth
        @if(auth()->user()->isInactive())
            <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-[#000000]/80 backdrop-blur-sm p-4">
                <div class="bg-white rounded-2xl p-8 text-center max-w-sm w-full shadow-2xl border-t-8 border-red-600">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                        <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    
                    <h2 class="text-2xl font-bold mb-2 text-gray-900">Compte Suspendu</h2>
                    
                    <!-- AFFICHAGE DU MOTIF ICI -->
                    <div class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-100">
                        <p class="text-[10px] font-black text-red-500 uppercase mb-1">Motif de la restriction :</p>
                        <p class="text-sm text-gray-700 font-bold italic leading-relaxed">
                            "{{ auth()->user()->blocking_reason ?? 'Aucun motif spécifique n\'a été fourni par l\'administrateur.' }}"
                        </p>
                    </div>
                    
                    <p class="mb-6 text-[11px] text-gray-500 uppercase font-bold">Veuillez contacter la Direction Technique pour plus de détails.</p>
                    
                    <form method="GET" action="{{ route('login') }}">
                        @csrf
                        <button class="w-full py-3 px-4 rounded-xl shadow-lg font-bold text-white bg-red-600 hover:bg-red-700 transition-all">
                            Retour à l'accueil
                        </button>
                    </form>
                </div>
            </div>

        @elseif(auth()->user()->hasIncompleteProfile() && $currentContent !== 'profile-content')
             <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-[#000000]/70 backdrop-blur-sm p-4">
                <div class="bg-white rounded-2xl p-8 text-center max-w-sm w-full shadow-2xl border-t-8" style="border-color: var(--c-primary);">
                    
                    <!-- Icone Info / Attention -->
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-50 mb-6">
                        <svg class="h-8 w-8" style="color: var(--c-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <h2 class="text-2xl font-extrabold mb-2" style="color: var(--c-dark);">Profil Incomplet</h2>
                    <p class="mb-6 text-gray-500">Pour garantir le bon fonctionnement de l'application, nous avons besoin de quelques informations supplémentaires.</p>
                    
                    <button wire:click="forceGoToProfile" 
                            class="w-full py-3 px-4 rounded-xl shadow-lg font-bold text-white transform hover:-translate-y-1 transition-all duration-200"
                            style="background-color: var(--c-primary); color: var(--c-dark);">
                        Compléter mon profil
                    </button>
                </div>
           </div>
        @endif
    @endauth

</div>
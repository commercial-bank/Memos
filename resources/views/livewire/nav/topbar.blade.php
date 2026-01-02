<div class="flex flex-col flex-grow h-screen overflow-hidden transition-colors duration-300 {{ $darkMode ? 'bg-[#121212]' : 'bg-[#f9fafb]' }}">
    
    <!-- STYLE DYNAMIQUE POUR LES VARIABLES CSS -->
    <style>
        :root {
            --c-primary: #daaf2c;
            --c-dark: {{ $darkMode ? '#ffffff' : '#1a1a1a' }};
            --c-secondary: {{ $darkMode ? '#a0a0a0' : '#707173' }};
            --nav-bg: {{ $darkMode ? 'rgba(26, 26, 26, 0.8)' : 'rgba(255, 255, 255, 0.8)' }};
            --border-col: {{ $darkMode ? '#2d2d2d' : '#f1f1f1' }};
        }
    </style>

    <!-- ======================= -->
    <!-- NAVBAR (En-tête)        -->
    <!-- ======================= -->
    <header id="navbar" class="sticky top-0 z-40 w-full flex items-center justify-between h-20 px-4 sm:px-6 backdrop-blur-xl border-b transition-all duration-300"
            style="background-color: var(--nav-bg); border-color: var(--border-col);">
        
        <!-- SECTION GAUCHE -->
        <div class="flex items-center gap-4">
            <button wire:click="$parent.toggleSidebar" class="p-2 rounded-lg hover:bg-[#daaf2c]/10 transition-colors md:hidden" style="color: var(--c-secondary);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
            </button>

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
            <button wire:click="selectTab('notifications')" class="relative p-2.5 rounded-full transition-all duration-200 group hover:bg-[#daaf2c]/10" style="color: var(--c-secondary);">
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

            <div class="h-8 w-px hidden sm:block" style="background-color: var(--border-col);"></div>

            <!-- Avatar / Profil -->
            <div class="relative group">
                <button wire:click="selectTab('profile')" class="flex items-center gap-3 p-1 pr-3 rounded-full transition-all border border-transparent hover:border-gray-200 {{ $darkMode ? 'hover:bg-white/5' : 'hover:bg-gray-50' }}">
                    <div class="relative">
                        <img src="{{ asset('images/user3.png') }}" alt="Avatar" class="h-10 w-10 rounded-full object-cover border shadow-sm group-hover:ring-2 group-hover:ring-[#daaf2c] transition-all" style="border-color: var(--border-col);">
                        <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full ring-2 ring-white bg-green-500"></span>
                    </div>
                    <div class="hidden md:flex flex-col items-start text-sm">
                        <span class="font-bold leading-none" style="color: var(--c-dark);">{{ auth()->user()->first_name }}</span>
                        <span class="text-[10px] font-medium mt-1" style="color: var(--c-secondary);">{{ auth()->user()->poste ?? 'Utilisateur' }}</span>
                    </div>
                    <svg class="w-4 h-4 hidden md:block" style="color: var(--c-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- ======================= -->
    <!-- CONTENU PRINCIPAL       -->
    <!-- ======================= -->
    <main class="flex-1 overflow-y-auto p-4 sm:p-6 custom-scrollbar relative">
        <!-- Fond décoratif -->
        <div class="absolute inset-0 pointer-events-none opacity-[0.03]" 
             style="background-image: radial-gradient({{ $darkMode ? '#ffffff' : '#000000' }} 1px, transparent 1px); background-size: 24px 24px;">
        </div>

        <div class="relative z-10 max-w-7xl mx-auto">
            @switch($currentContent)
                @case('dashboard-content') @livewire('nav.dashboard') @break
                @case('memos-content') @livewire('memos.memos') @break
                @case('profile-content') @livewire('setting.profil') @break
                @case('notifications-content') @livewire('notifications.notifications-dropdown') @break
                @case('settings-content') @livewire('setting.settings') @break  
                @case('settings-documents') @livewire('setting.documents') @break       
                @default @livewire('nav.dashboard')
            @endswitch
        </div>
    </main>

    <!-- ======================= -->
    <!-- MODALS (Adaptés au Dark) -->
    <!-- ======================= -->
    @auth
        @if(auth()->user()->isInactive())
            <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
                <div class="{{ $darkMode ? 'bg-[#1e1e1e] border-gray-800' : 'bg-white' }} rounded-2xl p-8 text-center max-w-sm w-full shadow-2xl border-t-8 border-red-600">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100/10 mb-6">
                        <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold mb-2" style="color: var(--c-dark);">Compte Suspendu</h2>
                    <div class="{{ $darkMode ? 'bg-white/5' : 'bg-gray-50' }} rounded-xl p-4 mb-6">
                        <p class="text-[10px] font-black text-red-500 uppercase mb-1">Motif de la restriction :</p>
                        <p class="text-sm font-bold italic leading-relaxed" style="color: var(--c-dark);">
                            "{{ auth()->user()->blocking_reason ?? 'Aucun motif fourni.' }}"
                        </p>
                    </div>
                    <form method="GET" action="{{ route('login') }}">
                        <button class="w-full py-3 px-4 rounded-xl shadow-lg font-bold text-white bg-red-600 hover:bg-red-700 transition-all">Retour à l'accueil</button>
                    </form>
                </div>
            </div>

        @elseif(auth()->user()->hasIncompleteProfile() && $currentContent !== 'profile-content')
             <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
                <div class="{{ $darkMode ? 'bg-[#1e1e1e] border-gray-800' : 'bg-white' }} rounded-2xl p-8 text-center max-w-sm w-full shadow-2xl border-t-8" style="border-color: var(--c-primary);">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-50/10 mb-6">
                        <svg class="h-8 w-8" style="color: var(--c-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-extrabold mb-2" style="color: var(--c-dark);">Profil Incomplet</h2>
                    <p class="mb-6" style="color: var(--c-secondary);">Veuillez compléter vos informations pour continuer.</p>
                    <button wire:click="forceGoToProfile" class="w-full py-3 px-4 rounded-xl shadow-lg font-bold transform hover:-translate-y-1 transition-all" style="background-color: var(--c-primary); color: #000;">
                        Compléter mon profil
                    </button>
                </div>
           </div>
        @endif
    @endauth
</div>
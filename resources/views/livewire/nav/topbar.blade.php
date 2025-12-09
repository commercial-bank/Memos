<div class="flex flex-col flex-grow h-screen overflow-hidden"> <!-- 1. LE ROOT ELEMENT OBLIGATOIRE -->

    <!-- ======================= -->
    <!-- NAVBAR (En-tête)        -->
    <!-- ======================= -->
    <header id="navbar" class="sticky top-0 z-40 w-full flex items-center justify-between h-20 px-4 sm:px-6 bg-white/90 backdrop-blur-md border-b border-slate-200 shadow-sm transition-all duration-300">
        
        <!-- SECTION GAUCHE -->
        <div class="flex items-center gap-4">
            <button wire:click="$parent.toggleSidebar" class="p-2 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-colors md:hidden">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
            </button>

            <div class="flex flex-col">
                <span id="navbarTitle" class="text-lg font-bold text-slate-800 tracking-tight leading-none">
                    {{ $navbarTitle ?? 'Dashboard' }}
                </span>
                <span class="text-[10px] uppercase font-semibold text-slate-400 tracking-wider">Espace de travail</span>
            </div>
        </div>

        <!-- SECTION DROITE -->
        <div class="flex items-center gap-2 sm:gap-4">
            
            <!-- Notification Button -->
            <button wire:click="selectTab('notifications')" class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors rounded-full hover:bg-slate-50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="absolute top-1.5 right-1.5 flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-600"></span>
                    </span>
                @endif
            </button>

            <div class="h-8 w-px bg-slate-200 mx-1 hidden sm:block"></div>

            <!-- Avatar -->
            <div class="relative group">
                <button wire:click="selectTab('profile')" class="flex items-center gap-3 p-1 rounded-full hover:bg-slate-50 transition-all border border-transparent hover:border-slate-200">
                    <div class="relative">
                        <img src="{{ asset('images/user3.png') }}" alt="Avatar" class="h-9 w-9 rounded-full object-cover border-2 border-white shadow-sm">
                        <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white bg-green-500"></span>
                    </div>
                    <div class="hidden md:flex flex-col items-start text-sm mr-2">
                        <span class="font-bold text-slate-700 leading-none">{{ auth()->user()->first_name }}</span>
                        <span class="text-[10px] text-slate-500 font-medium">{{ auth()->user()->poste ?? 'Utilisateur' }}</span>
                    </div>
                </button>
            </div>
        </div>
    </header>

    <!-- ======================= -->
    <!-- CONTENU PRINCIPAL       -->
    <!-- ======================= -->
    <main class="flex-1 overflow-y-auto p-4 bg-[#f8f9fa] custom-scrollbar">
        
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
            @default
                @livewire('nav.dashboard')
        @endswitch

    </main>

    <!-- ======================= -->
    <!-- MODALS (Overlay)        -->
    <!-- ======================= -->
    @auth
        @if(auth()->user()->isInactive())
            <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/95 backdrop-blur-sm">
                 <div class="bg-white rounded-2xl p-8 text-center border-t-4 border-red-500 shadow-2xl">
                     <h2 class="text-2xl font-bold mb-2">Compte Désactivé</h2>
                     <p class="mb-4 text-slate-600">Veuillez contacter l'administrateur.</p>
                     <form method="GET" action="{{ route('login') }}">
                        @csrf
                        <button class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">Déconnexion</button>
                     </form>
                 </div>
            </div>
        @elseif(auth()->user()->hasIncompleteProfile() && $currentContent !== 'profile-content')
             <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/90 backdrop-blur-sm">
                <div class="bg-white rounded-2xl p-8 text-center border-t-4 border-[#b8962f] shadow-2xl">
                    <h2 class="text-2xl font-bold mb-2">Profil Incomplet</h2>
                    <p class="mb-4 text-slate-600">Veuillez compléter vos informations pour continuer.</p>
                    <button wire:click="forceGoToProfile" class="bg-[#b8962f] text-white px-6 py-2 rounded-lg hover:bg-[#a48425] transition">Compléter mon profil</button>
                </div>
           </div>
        @endif
    @endauth

</div> <!-- FIN DU ROOT ELEMENT OBLIGATOIRE -->
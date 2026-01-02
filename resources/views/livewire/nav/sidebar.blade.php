<aside 
    class="flex flex-col h-screen shadow-2xl transition-all duration-300 ease-in-out border-r {{ $isCollapsed ? 'w-[70px]' : 'w-[250px]' }} {{ $darkMode ? 'bg-[#121212] border-gray-800' : 'bg-white border-gray-100' }}" 
    id="sidebar"
>
    <!-- CONFIGURATION DES COULEURS DYNAMIQUE -->
    <style>
        .sidebar-text-inactive { color: {{ $darkMode ? '#a0a0a0' : '#707173' }}; }
        .sidebar-text-header { color: {{ $darkMode ? '#ffffff' : '#000000' }}; }
        .sidebar-bg-active { background-color: #daaf2c; } /* L'or reste identique */
        .sidebar-text-active { color: #000000 !important; font-weight: 700; }
        
        .sidebar-link:hover {
            background-color: {{ $darkMode ? '#1e1e1e' : '#f9fafb' }};
            color: {{ $darkMode ? '#ffffff' : '#000000' }};
        }

        .custom-border { border-color: {{ $darkMode ? '#2d2d2d' : '#f3f4f6' }}; }
        .text-main { color: {{ $darkMode ? '#ffffff' : '#000000' }}; }
    </style>

    <!-- Sidebar Header -->
    <div class="flex items-center h-[90px] border-b custom-border {{ $isCollapsed ? 'justify-center p-0' : 'justify-start px-5 py-5' }}">
        <div class="flex items-center overflow-hidden">
            <img src="{{ asset('images/lo.png') }}" class="w-10 h-10 object-contain" alt="Logo">
            <span class="text-2xl font-bold whitespace-nowrap transition-opacity duration-300 ease-in-out ml-3 sidebar-text-header {{ $isCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100' }}">
                Memos
            </span>
        </div>
    </div>

    <!-- Sidebar Nav -->
    <nav class="flex-grow py-5 overflow-y-auto custom-scrollbar">
        
        <!-- Main Menu Section -->
        <div class="mb-6 {{ $isCollapsed ? 'px-2' : 'px-5' }}">
            <span class="block text-xs uppercase mb-2 tracking-wide whitespace-nowrap font-bold transition-opacity duration-300 ease-in-out {{ $isCollapsed ? 'opacity-0 h-0 p-0 m-0 overflow-hidden' : 'opacity-100' }}"
                  style="color: #daaf2c;">
                Main Menu
            </span>
            
            <ul class="space-y-1">
                <!-- Dashboard -->
                <li>
                    <a href="#" wire:click.prevent="selectTab('dashboard')" 
                       class="flex items-center rounded-xl py-3 transition-all duration-200 ease-in-out sidebar-link {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }} {{ $activeTab == 'dashboard' ? 'sidebar-bg-active sidebar-text-active shadow-md' : 'sidebar-text-inactive' }}">
                        <i class="fas fa-th-large text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Dashboard</span>
                    </a>
                </li>
                
                <!-- Mémos -->
                <li>
                    <a href="#" wire:click.prevent="selectTab('memos')" 
                       class="flex items-center rounded-xl py-3 transition-all duration-200 ease-in-out sidebar-link {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }} {{ $activeTab == 'memos' ? 'sidebar-bg-active sidebar-text-active shadow-md' : 'sidebar-text-inactive' }}">
                        <i class="fas fa-file-alt text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Mémos</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- General Section -->
        <div class="mb-6 {{ $isCollapsed ? 'px-2' : 'px-5' }}">
            <span class="block text-xs uppercase mb-2 tracking-wide font-bold transition-opacity duration-300 {{ $isCollapsed ? 'opacity-0 h-0 overflow-hidden' : 'opacity-100' }}" style="color: #daaf2c;">
                General
            </span>
            <ul class="space-y-1">
                <li>
                    <a href="#" wire:click.prevent="selectTab('documents')" 
                       class="flex items-center rounded-xl py-3 transition-all duration-200 ease-in-out sidebar-link {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }} {{ $activeTab == 'documents' ? 'sidebar-bg-active sidebar-text-active shadow-md' : 'sidebar-text-inactive' }}">
                        <i class="fas fa-copy text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Documents</span>
                    </a>
                </li>

                <!-- NOTIFICATIONS -->
                <li wire:poll.10s>
                    @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                    <a href="#" wire:click.prevent="selectTab('notifications')"
                       class="relative flex items-center rounded-xl py-3 transition-all duration-200 ease-in-out group sidebar-link {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }} {{ $activeTab == 'notifications' ? 'sidebar-bg-active sidebar-text-active shadow-md' : 'sidebar-text-inactive' }}">
                        <div class="relative flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}">
                            <i class="fas fa-bell text-lg w-5 text-center"></i>
                            @if($isCollapsed && $unreadCount > 0)
                                <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 bg-red-500"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-600 border border-white"></span>
                                </span>
                            @endif
                        </div>
                        <span class="nav-text flex-1 flex justify-between items-center transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">
                            <span>Notifications</span>
                            @if($unreadCount > 0)
                                <span class="text-white text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-600">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                            @endif
                        </span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Account Section -->
        <div class="{{ $isCollapsed ? 'px-2' : 'px-5' }}">
            <span class="block text-xs uppercase mb-2 tracking-wide font-bold transition-opacity duration-300 {{ $isCollapsed ? 'opacity-0 h-0 overflow-hidden' : 'opacity-100' }}" style="color: #daaf2c;">
                Account
            </span>
            <ul class="space-y-1">
                <li>
                    <a href="#" wire:click.prevent="selectTab('profile')" 
                       class="flex items-center rounded-xl py-3 transition-all duration-200 ease-in-out sidebar-link {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }} {{ $activeTab == 'profile' ? 'sidebar-bg-active sidebar-text-active shadow-md' : 'sidebar-text-inactive' }}">
                        <i class="fas fa-user-circle text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Profil</span>
                    </a>
                </li>

                @if(auth()->user()->is_admin) 
                    <li>
                        <a href="#" wire:click.prevent="selectTab('settings')" 
                           class="flex items-center rounded-xl py-3 transition-all duration-200 ease-in-out sidebar-link {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }} {{ $activeTab == 'settings' ? 'sidebar-bg-active sidebar-text-active shadow-md' : 'sidebar-text-inactive' }}">
                            <i class="fas fa-cog text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"></i>
                            <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Settings</span>
                        </a>
                    </li>
                @endif    

                <!-- Dark Mode Toggle -->
                <li>
                    <a href="#" wire:click.prevent="toggleDarkMode" 
                       class="flex items-center rounded-xl py-3 transition-all duration-200 ease-in-out sidebar-link {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }} sidebar-text-inactive">
                        <i class="fas {{ $darkMode ? 'fa-sun text-yellow-400' : 'fa-moon' }} text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">
                            {{ $darkMode ? 'Mode Clair' : 'Mode Sombre' }}
                        </span>
                    </a>
                </li>
                
                <li>
                    <a wire:click.prevent="selectTab('logout')" 
                       class="flex items-center rounded-xl py-3 transition-colors duration-200 ease-in-out cursor-pointer hover:bg-red-50 hover:text-red-600 text-gray-400 {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-sign-out-alt text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="px-5 py-5 border-t custom-border min-h-[80px] {{ $isCollapsed ? 'justify-center py-4 px-0' : 'px-5 py-5' }}">
        <div class="flex items-center overflow-hidden {{ $isCollapsed ? 'justify-center' : '' }}">
            <img src="{{ asset('images/user3.png') }}" alt="User Avatar" 
                 class="w-10 h-10 rounded-full object-cover border-2 flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"
                 style="border-color: #daaf2c;">
                 
            <div class="flex flex-col whitespace-nowrap transition-opacity duration-300 ease-in-out {{ $isCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100' }}">
                <span class="font-bold text-sm text-main">{{ auth()->user()->user_name }}</span>
                <span class="text-xs text-gray-400">{{ auth()->user()->poste ?? 'Utilisateur' }}</span>
            </div>
        </div>
    </div>
</aside>
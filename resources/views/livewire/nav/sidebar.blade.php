<aside 
    class="flex flex-col h-screen shadow-2xl transition-all duration-300 ease-in-out bg-white border-r border-gray-100 {{ $isCollapsed ? 'w-[70px]' : 'w-[250px]' }}" 
    id="sidebar"
>
    <!-- CONFIGURATION DES COULEURS (Locales au sidebar pour ce style 'Light') -->
    <style>
        /* On redéfinit les couleurs pour le mode clair spécifiquement ici */
        .sidebar-text-inactive { color: #707173; } /* Votre Gris */
        .sidebar-text-header { color: #000000; }   /* Votre Noir */
        .sidebar-bg-active { background-color: #daaf2c; } /* Votre Or */
        .sidebar-text-active { color: #000000; font-weight: 700; }
        
        /* Hover doux */
        .sidebar-link:hover {
            background-color: #f9fafb; /* Gris très clair au survol */
            color: #000000;
        }
    </style>

    <!-- Sidebar Header -->
    <div class="flex items-center h-[90px] border-b border-gray-100 {{ $isCollapsed ? 'justify-center p-0' : 'justify-start px-5 py-5' }}">
        <div class="flex items-center overflow-hidden">
            <!-- Logo -->
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
                  style="color: #daaf2c;"> <!-- Titre de section en OR pour l'élégance -->
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
            <span class="block text-xs uppercase mb-2 tracking-wide whitespace-nowrap font-bold transition-opacity duration-300 ease-in-out {{ $isCollapsed ? 'opacity-0 h-0 p-0 m-0 overflow-hidden' : 'opacity-100' }}"
                  style="color: #daaf2c;">
                General
            </span>
            
            <ul class="space-y-1">
                <!-- Groups -->
                <li>
                    <a href="#" wire:click.prevent="selectTab('groups')" 
                       class="flex items-center rounded-xl py-3 transition-all duration-200 ease-in-out sidebar-link {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }} {{ $activeTab == 'groups' ? 'sidebar-bg-active sidebar-text-active shadow-md' : 'sidebar-text-inactive' }}">
                        
                        <i class="fas fa-users text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"></i>
                        
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Groups</span>
                    </a>
                </li>

                <!-- Reports -->
                <li>
                    <a href="#" wire:click.prevent="selectTab('reports')" 
                       class="flex items-center rounded-xl py-3 transition-all duration-200 ease-in-out sidebar-link {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }} {{ $activeTab == 'reports' ? 'sidebar-bg-active sidebar-text-active shadow-md' : 'sidebar-text-inactive' }}">
                        
                        <i class="fas fa-flag text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"></i>
                        
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Reports</span>
                    </a>
                </li>

                <!-- Tasks -->
                <li>
                    <a href="#" wire:click.prevent="selectTab('tasks')" 
                       class="flex items-center rounded-xl py-3 transition-all duration-200 ease-in-out sidebar-link {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }} {{ $activeTab == 'tasks' ? 'sidebar-bg-active sidebar-text-active shadow-md' : 'sidebar-text-inactive' }}">
                       
                        <i class="fas fa-tasks text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"></i>
                           
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Tasks</span>
                    </a>
                </li>

                <!-- Calendar -->
                <li>
                    <a href="#" wire:click.prevent="selectTab('calendar')" 
                       class="flex items-center rounded-xl py-3 transition-all duration-200 ease-in-out sidebar-link {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }} {{ $activeTab == 'calendar' ? 'sidebar-bg-active sidebar-text-active shadow-md' : 'sidebar-text-inactive' }}">
                       
                        <i class="fas fa-calendar-alt text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"></i>
                           
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Calendar</span>
                    </a>
                </li>

                <!-- Documents -->
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
                        
                        <!-- Icône & Point Rouge -->
                        <div class="relative flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}">
                            <i class="fas fa-bell text-lg w-5 text-center"></i>
                               
                            @if($isCollapsed && $unreadCount > 0)
                                <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 bg-red-500"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-600 border border-white"></span>
                                </span>
                            @endif
                        </div>

                        <!-- Texte & Badge -->
                        <span class="nav-text flex-1 flex justify-between items-center transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">
                            <span>Notifications</span>
                            @if($unreadCount > 0)
                                <span class="text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm bg-red-600">
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            @endif
                        </span>

                        <!-- Tooltip Sidebar Fermée -->
                        @if($isCollapsed)
                            <div class="absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-md px-3 py-2 text-xs font-medium text-black shadow-xl opacity-0 transition-all duration-300 group-hover:visible group-hover:opacity-100 group-hover:translate-x-0 invisible -translate-x-2 border border-gray-200 bg-white">
                                <div class="absolute left-0 top-1/2 -ml-1 h-2 w-2 -translate-y-1/2 rotate-45 border-l border-b border-gray-200 bg-white"></div>
                                <div class="relative flex items-center gap-2">
                                    <span>Notifications</span>
                                    @if($unreadCount > 0)
                                        <span class="flex items-center justify-center min-w-[18px] h-[18px] rounded-full bg-red-600 px-1.5 text-[10px] font-bold text-white shadow-sm">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </a>
                </li>
            </ul>
        </div>

        <!-- Account Section -->
        <div class="{{ $isCollapsed ? 'px-2' : 'px-5' }}">
            <span class="block text-xs uppercase mb-2 tracking-wide whitespace-nowrap font-bold transition-opacity duration-300 ease-in-out {{ $isCollapsed ? 'opacity-0 h-0 p-0 m-0 overflow-hidden' : 'opacity-100' }}"
                  style="color: #daaf2c;">
                Account
            </span>
            <ul class="space-y-1">
                <!-- Profile -->
                <li>
                    <a href="#" wire:click.prevent="selectTab('profile')" 
                       class="flex items-center rounded-xl py-3 transition-all duration-200 ease-in-out sidebar-link {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }} {{ $activeTab == 'profile' ? 'sidebar-bg-active sidebar-text-active shadow-md' : 'sidebar-text-inactive' }}">
                       
                        <i class="fas fa-user-circle text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"></i>
                           
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Profil</span>
                    </a>
                </li>

                <!-- Settings -->
                @if(auth()->user()->is_admin) 
                    <li>
                        <a href="#" wire:click.prevent="selectTab('settings')" 
                        class="flex items-center rounded-xl py-3 transition-all duration-200 ease-in-out sidebar-link {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }} {{ $activeTab == 'settings' ? 'sidebar-bg-active sidebar-text-active shadow-md' : 'sidebar-text-inactive' }}">
                        
                            <i class="fas fa-cog text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"></i>
                            
                            <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Settings</span>
                        </a>
                    </li>
                @endif    
                
                <!-- Logout -->
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
    <div class="px-5 py-5 border-t border-gray-100 min-h-[80px] {{ $isCollapsed ? 'justify-center py-4 px-0' : 'px-5 py-5' }}">
        <div class="flex items-center overflow-hidden {{ $isCollapsed ? 'justify-center' : '' }}">
            
            <!-- Avatar avec Bordure Or -->
            <img src="{{ asset('images/user3.png') }}" alt="User Avatar" 
                 class="w-10 h-10 rounded-full object-cover border-2 flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"
                 style="border-color: #daaf2c;">
                 
            <div class="flex flex-col whitespace-nowrap transition-opacity duration-300 ease-in-out {{ $isCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100' }}">
                <span class="font-bold text-sm text-black">{{ auth()->user()->user_name }}</span>
                <span class="text-xs text-gray-400">{{ auth()->user()->poste ?? 'Utilisateur' }}</span>
            </div>
        </div>
    </div>
</aside>
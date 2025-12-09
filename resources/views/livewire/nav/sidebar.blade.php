<aside class="flex flex-col h-screen bg-[#2a2a2a] text-[#e0e0e0] shadow-lg transition-all duration-300 ease-in-out {{ $isCollapsed ? 'w-[70px] bg-[#1a1a1a]' : 'w-[250px]' }}" id="sidebar">

    <!-- Sidebar Header -->
    <div class="flex items-center border-b border-gray-700 h-[90px] {{ $isCollapsed ? 'justify-center p-0' : 'justify-start px-5 py-5' }}">
        <div class="flex items-center overflow-hidden">
            <img src="{{ asset('images/logo.svg') }}" class="w-50 h-20" alt="Logo">
            <span class="text-2xl font-bold whitespace-nowrap transition-opacity duration-300 ease-in-out {{ $isCollapsed ? 'opacity-0 w-0 -ml-2' : 'opacity-100' }}">Memos</span>
        </div>
    </div>

    <!-- Sidebar Nav -->
    <nav class="flex-grow py-5 overflow-y-auto custom-scrollbar">
        
        <!-- Main Menu Section -->
        <div class="mb-6 {{ $isCollapsed ? 'px-0' : 'px-5' }}">
            <span class="block text-xs uppercase text-[#a0a0a0] mb-2 tracking-wide whitespace-nowrap transition-opacity duration-300 ease-in-out {{ $isCollapsed ? 'opacity-0 h-0 p-0 m-0 overflow-hidden' : 'opacity-100' }}">Main Menu</span>
            
            <ul>
                <!-- Dashboard -->
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('dashboard')" class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out {{ $activeTab == 'dashboard' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }} {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-th-large text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }} {{ $activeTab == 'dashboard' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Dashboard</span>
                    </a>
                </li>
                
                <!-- Mémos -->
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('memos')" class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out {{ $activeTab == 'memos' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }} {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-file-alt text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }} {{ $activeTab == 'memos' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Mémos</span>
                    </a>
                </li>
                
                <!-- Courriers -->
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('courriers')" class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out {{ $activeTab == 'courriers' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }} {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-envelope text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }} {{ $activeTab == 'courriers' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Courriers</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- General Section -->
        <div class="mb-6 {{ $isCollapsed ? 'px-0' : 'px-5' }}">
            <span class="block text-xs uppercase text-[#a0a0a0] mb-2 tracking-wide whitespace-nowrap transition-opacity duration-300 ease-in-out {{ $isCollapsed ? 'opacity-0 h-0 p-0 m-0 overflow-hidden' : 'opacity-100' }}">General</span>
            
            <ul>
                <!-- Groups -->
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('groups')" class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out {{ $activeTab == 'groups' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }} {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-users text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }} {{ $activeTab == 'groups' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Groups</span>
                    </a>
                </li>

                <!-- Reports -->
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('reports')" class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out {{ $activeTab == 'reports' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }} {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-flag text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }} {{ $activeTab == 'reports' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Reports</span>
                    </a>
                </li>

                <!-- Tasks -->
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('tasks')" class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out {{ $activeTab == 'tasks' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }} {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-tasks text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }} {{ $activeTab == 'tasks' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Tasks</span>
                    </a>
                </li>

                <!-- Calendar -->
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('calendar')" class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out {{ $activeTab == 'calendar' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }} {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-calendar-alt text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }} {{ $activeTab == 'calendar' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Calendar</span>
                    </a>
                </li>

                <!-- Documents -->
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('documents')" class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out {{ $activeTab == 'documents' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }} {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-copy text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }} {{ $activeTab == 'documents' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Documents</span>
                    </a>
                </li>

                <!-- NOTIFICATIONS (Spécial avec Polling) -->
                <li class="mb-1" wire:poll.10s>
                    @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp

                    <a href="#" wire:click.prevent="selectTab('notifications')"
                       class="relative flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out group {{ $activeTab == 'notifications' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }} {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        
                        <!-- Icône & Point Rouge (Sidebar Fermée) -->
                        <div class="relative flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}">
                            <i class="fas fa-bell text-lg w-5 text-center {{ $activeTab == 'notifications' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                            @if($isCollapsed && $unreadCount > 0)
                                <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500 border border-[#2a2a2a]"></span>
                                </span>
                            @endif
                        </div>

                        <!-- Texte & Badge (Sidebar Ouverte) -->
                        <span class="nav-text flex-1 flex justify-between items-center transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">
                            <span>Notifications</span>
                            @if($unreadCount > 0)
                                <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            @endif
                        </span>

                        <!-- Tooltip Design (Sidebar Fermée) -->
                        @if($isCollapsed)
                            <div class="absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-md bg-slate-800 px-3 py-2 text-xs font-medium text-white shadow-xl opacity-0 transition-all duration-300 group-hover:visible group-hover:opacity-100 group-hover:translate-x-0 invisible -translate-x-2 border border-slate-700/50 pointer-events-none">
                                <div class="absolute left-0 top-1/2 -ml-1 h-2 w-2 -translate-y-1/2 rotate-45 bg-slate-800 border-l border-b border-slate-700/50"></div>
                                <div class="relative flex items-center gap-2">
                                    <span>Notifications</span>
                                    @if($unreadCount > 0)
                                        <span class="flex items-center justify-center min-w-[18px] h-[18px] rounded-full bg-red-500 px-1.5 text-[10px] font-bold text-white shadow-sm ring-1 ring-red-400/30">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </a>
                </li>
            </ul>
        </div>

        <!-- Account Section -->
        <div class="{{ $isCollapsed ? 'px-0' : 'px-5' }}">
            <span class="block text-xs uppercase text-[#a0a0a0] mb-2 tracking-wide whitespace-nowrap transition-opacity duration-300 ease-in-out {{ $isCollapsed ? 'opacity-0 h-0 p-0 m-0 overflow-hidden' : 'opacity-100' }}">Account</span>
            <ul>
                <!-- Profile -->
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('profile')" class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out {{ $activeTab == 'profile' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }} {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-user-circle text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }} {{ $activeTab == 'profile' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Profil</span>
                    </a>
                </li>

                <!-- Settings -->
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('settings')" class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out {{ $activeTab == 'settings' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }} {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-cog text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }} {{ $activeTab == 'settings' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Settings</span>
                    </a>
                </li>
                
                <!-- Logout -->
                <li class="mb-1">
                    <a wire:click.prevent="selectTab('logout')" class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out text-[#e0e0e0] hover:bg-gray-700/50 {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }} cursor-pointer">
                        <i class="fas fa-sign-out-alt text-lg w-5 text-center flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="px-5 py-5 border-t border-gray-700 min-h-[80px] {{ $isCollapsed ? 'justify-center py-4 px-0' : 'px-5 py-5' }}">
        <div class="flex items-center overflow-hidden {{ $isCollapsed ? 'justify-center' : '' }}">
            <img src="{{ asset('images/user3.png') }}" alt="User Avatar" class="w-10 h-10 rounded-full object-cover border-2 border-[#b8962f] flex-shrink-0 {{ $isCollapsed ? 'mr-0' : 'mr-4' }}">
            <div class="flex flex-col whitespace-nowrap transition-opacity duration-300 ease-in-out {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">
                <span class="font-bold text-[#e0e0e0]">{{ auth()->user()->user_name }}</span>
                <span class="text-sm text-[#a0a0a0]">{{ auth()->user()->poste ?? 'Utilisateur' }}</span>
            </div>
        </div>
    </div>
</aside>
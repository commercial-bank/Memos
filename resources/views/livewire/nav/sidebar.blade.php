<!-- Livewire/Nav/Sidebar.blade.php -->
<aside class="flex flex-col h-screen bg-[#2a2a2a] text-[#e0e0e0] shadow-lg transition-all duration-300 ease-in-out
              {{ $isCollapsed ? 'w-[70px] bg-[#1a1a1a]' : 'w-[250px]' }}"
       id="sidebar">

    <!-- Sidebar Header -->
    <div class="flex items-center border-b border-gray-700 h-[90px]
                {{ $isCollapsed ? 'justify-center p-0' : 'justify-start px-5 py-5' }}">
        <div class="flex items-center overflow-hidden">
            <img src="{{ asset('images/logo.svg') }}" class="w-50 h-20" alt="">
            <span class="text-2xl font-bold whitespace-nowrap transition-opacity duration-300 ease-in-out
                         {{ $isCollapsed ? 'opacity-0 w-0 -ml-2' : 'opacity-100' }}">Nexus</span>
        </div>
    </div>

    <!-- Sidebar Nav -->
    <nav class="flex-grow py-5 overflow-y-auto custom-scrollbar">
        <!-- Main Menu Section -->
        <div class="mb-6 {{ $isCollapsed ? 'px-0' : 'px-5' }}">
            <span class="block text-xs uppercase text-[#a0a0a0] mb-2 tracking-wide whitespace-nowrap transition-opacity duration-300 ease-in-out
                         {{ $isCollapsed ? 'opacity-0 h-0 p-0 m-0 overflow-hidden' : 'opacity-100' }}">Main Menu</span>
            <ul>
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('dashboard')"
                       class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out
                              {{ $activeTab == 'dashboard' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }}
                              {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-th-large text-lg w-5 text-center flex-shrink-0
                                  {{ $isCollapsed ? 'mr-0' : 'mr-4' }}
                                  {{ $activeTab == 'dashboard' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden
                                     {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Dashboard</span>
                        @if($isCollapsed)
                            <span class="tooltip absolute left-[70px] -translate-y-1/2 bg-[#2a2a2a] p-2 rounded-md shadow-lg text-sm invisible group-hover:visible z-10">Dashboard</span>
                        @endif
                    </a>
                </li>
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('memos')"
                       class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out
                              {{ $activeTab == 'memos' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }}
                              {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-file-alt text-lg w-5 text-center flex-shrink-0
                                  {{ $isCollapsed ? 'mr-0' : 'mr-4' }}
                                  {{ $activeTab == 'memos' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden
                                     {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Mémos</span>
                        @if($isCollapsed)
                            <span class="tooltip absolute left-[70px] -translate-y-1/2 bg-[#2a2a2a] p-2 rounded-md shadow-lg text-sm invisible group-hover:visible z-10">Mémos</span>
                        @endif
                    </a>
                </li>
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('courriers')"
                       class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out
                              {{ $activeTab == 'analytic' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }}
                              {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-envelope text-lg w-5 text-center flex-shrink-0
                                  {{ $isCollapsed ? 'mr-0' : 'mr-4' }}
                                  {{ $activeTab == 'analytic' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden
                                     {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Courriers</span>
                        @if($isCollapsed)
                            <span class="tooltip absolute left-[70px] -translate-y-1/2 bg-[#2a2a2a] p-2 rounded-md shadow-lg text-sm invisible group-hover:visible z-10">Courriers</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>

        <!-- General Section -->
        <div class="mb-6 {{ $isCollapsed ? 'px-0' : 'px-5' }}">
            <span class="block text-xs uppercase text-[#a0a0a0] mb-2 tracking-wide whitespace-nowrap transition-opacity duration-300 ease-in-out
                         {{ $isCollapsed ? 'opacity-0 h-0 p-0 m-0 overflow-hidden' : 'opacity-100' }}">General</span>
            <ul>
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('groups')"
                       class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out
                              {{ $activeTab == 'groups' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }}
                              {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-users text-lg w-5 text-center flex-shrink-0
                                  {{ $isCollapsed ? 'mr-0' : 'mr-4' }}
                                  {{ $activeTab == 'groups' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden
                                     {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Groups</span>
                        @if($isCollapsed)
                            <span class="tooltip absolute left-[70px] -translate-y-1/2 bg-[#2a2a2a] p-2 rounded-md shadow-lg text-sm invisible group-hover:visible z-10">Groups</span>
                        @endif
                    </a>
                </li>
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('reports')"
                       class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out
                              {{ $activeTab == 'reports' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }}
                              {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-flag text-lg w-5 text-center flex-shrink-0
                                  {{ $isCollapsed ? 'mr-0' : 'mr-4' }}
                                  {{ $activeTab == 'reports' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden
                                     {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Reports</span>
                        @if($isCollapsed)
                            <span class="tooltip absolute left-[70px] -translate-y-1/2 bg-[#2a2a2a] p-2 rounded-md shadow-lg text-sm invisible group-hover:visible z-10">Reports</span>
                        @endif
                    </a>
                </li>
                 <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('tasks')"
                       class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out
                              {{ $activeTab == 'tasks' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }}
                              {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-tasks text-lg w-5 text-center flex-shrink-0
                                  {{ $isCollapsed ? 'mr-0' : 'mr-4' }}
                                  {{ $activeTab == 'tasks' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden
                                     {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Tasks</span>
                        @if($isCollapsed)
                            <span class="tooltip absolute left-[70px] -translate-y-1/2 bg-[#2a2a2a] p-2 rounded-md shadow-lg text-sm invisible group-hover:visible z-10">Tasks</span>
                        @endif
                    </a>
                </li>
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('calendar')"
                       class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out
                              {{ $activeTab == 'calendar' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }}
                              {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-calendar-alt text-lg w-5 text-center flex-shrink-0
                                  {{ $isCollapsed ? 'mr-0' : 'mr-4' }}
                                  {{ $activeTab == 'calendar' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden
                                     {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Calendar</span>
                        @if($isCollapsed)
                            <span class="tooltip absolute left-[70px] -translate-y-1/2 bg-[#2a2a2a] p-2 rounded-md shadow-lg text-sm invisible group-hover:visible z-10">Calendar</span>
                        @endif
                    </a>
                </li>
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('documents')"
                       class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out
                              {{ $activeTab == 'documents' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }}
                              {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-copy text-lg w-5 text-center flex-shrink-0
                                  {{ $isCollapsed ? 'mr-0' : 'mr-4' }}
                                  {{ $activeTab == 'documents' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden
                                     {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Documents</span>
                        @if($isCollapsed)
                            <span class="tooltip absolute left-[70px] -translate-y-1/2 bg-[#2a2a2a] p-2 rounded-md shadow-lg text-sm invisible group-hover:visible z-10">Documents</span>
                        @endif
                    </a>
                </li>
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('notifications')"
                       class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out
                              {{ $activeTab == 'notifications' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }}
                              {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-bell text-lg w-5 text-center flex-shrink-0
                                  {{ $isCollapsed ? 'mr-0' : 'mr-4' }}
                                  {{ $activeTab == 'notifications' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden
                                     {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Notifications</span>
                        @if($isCollapsed)
                            <span class="tooltip absolute left-[70px] -translate-y-1/2 bg-[#2a2a2a] p-2 rounded-md shadow-lg text-sm invisible group-hover:visible z-10">Notifications</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>

        <!-- Account Section -->
        <div class="{{ $isCollapsed ? 'px-0' : 'px-5' }}">
            <span class="block text-xs uppercase text-[#a0a0a0] mb-2 tracking-wide whitespace-nowrap transition-opacity duration-300 ease-in-out
                         {{ $isCollapsed ? 'opacity-0 h-0 p-0 m-0 overflow-hidden' : 'opacity-100' }}">Account</span>
            <ul>
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('profile')"
                       class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out
                              {{ $activeTab == 'profile' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }}
                              {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-user-circle text-lg w-5 text-center flex-shrink-0
                                  {{ $isCollapsed ? 'mr-0' : 'mr-4' }}
                                  {{ $activeTab == 'profile' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden
                                     {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Profil</span>
                        @if($isCollapsed)
                            <span class="tooltip absolute left-[70px] -translate-y-1/2 bg-[#2a2a2a] p-2 rounded-md shadow-lg text-sm invisible group-hover:visible z-10">Profil</span>
                        @endif
                    </a>
                </li>
                <li class="mb-1">
                    <a href="#" wire:click.prevent="selectTab('settings')"
                       class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out
                              {{ $activeTab == 'settings' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }}
                              {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-cog text-lg w-5 text-center flex-shrink-0
                                  {{ $isCollapsed ? 'mr-0' : 'mr-4' }}
                                  {{ $activeTab == 'settings' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden
                                     {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Settings</span>
                        @if($isCollapsed)
                            <span class="tooltip absolute left-[70px] -translate-y-1/2 bg-[#2a2a2a] p-2 rounded-md shadow-lg text-sm invisible group-hover:visible z-10">Settings</span>
                        @endif
                    </a>
                </li>
                <li class="mb-1">
                    <a wire:click.prevent="selectTab('logout')"   
                       class="flex items-center rounded-lg py-3 transition-colors duration-200 ease-in-out
                              {{ $activeTab == 'logout' ? 'bg-[#b8962f] text-white' : 'text-[#e0e0e0] hover:bg-gray-700/50' }}
                              {{ $isCollapsed ? 'justify-center px-0' : 'px-4' }}">
                        <i class="fas fa-sign-out-alt text-lg w-5 text-center flex-shrink-0
                                  {{ $isCollapsed ? 'mr-0' : 'mr-4' }}
                                  {{ $activeTab == 'logout' ? 'text-white' : 'text-[#e0e0e0]' }}"></i>
                        <span class="nav-text transition-opacity duration-300 ease-in-out whitespace-nowrap overflow-hidden
                                     {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">Logout</span>
                    
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="px-5 py-5 border-t border-gray-700 min-h-[80px]
                {{ $isCollapsed ? 'justify-center py-4 px-0' : 'px-5 py-5' }}">
        <div class="flex items-center overflow-hidden
                    {{ $isCollapsed ? 'justify-center' : '' }}">
            <img src="{{ asset('images/user3.png') }}" alt="User Avatar"
                 class="w-10 h-10 rounded-full object-cover border-2 border-[#b8962f] flex-shrink-0
                        {{ $isCollapsed ? 'mr-0' : 'mr-4' }}">
            <div class="flex flex-col whitespace-nowrap transition-opacity duration-300 ease-in-out
                        {{ $isCollapsed ? 'opacity-0 w-0' : 'opacity-100' }}">
                <span class="font-bold text-[#e0e0e0]">{{ auth()->user()->user_name }}</span>
                <span class="text-sm text-[#a0a0a0]">{{ auth()->user()->poste }}</span>
            </div>
        </div>
    </div>
</aside>

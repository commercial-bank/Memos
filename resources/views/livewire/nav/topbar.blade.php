

    
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

    
                </div>

            @endif

            @if($currentContent == 'memos-content')
                @livewire('memos.memos')
            @endif

             @if($currentContent == 'profile-content')
               @livewire('setting.profil')
            @endif

        </main>
        
    </div>

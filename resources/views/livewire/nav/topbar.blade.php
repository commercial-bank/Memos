

    
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

            @if($currentContent == 'courriers-content')
                @livewire('courriers.courriers')
            @endif

            @if($currentContent == 'reports-content')
               
                        
    

        

        <!-- 2. NAVIGATION (ONGLETS) -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <!-- Onglet Tous -->
                <button 
                    @click="activeTab = 'memos'"
                    :class="activeTab === 'memos' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors"
                >
                    <i class="far fa-sticky-note mr-2"></i> Mes Mémos
                </button>

                <!-- Onglet Favoris -->
                <button 
                    @click="activeTab = 'favorites'"
                    :class="activeTab === 'favorites' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors"
                >
                    <i class="fas fa-star mr-2"></i> Favoris
                </button>

                <!-- Onglet Archives -->
                <button 
                    @click="activeTab = 'archives'"
                    :class="activeTab === 'archives' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors"
                >
                    <i class="fas fa-archive mr-2"></i> Archives
                </button>
            </nav>
        </div>

        <!-- 3. ZONE DE CRÉATION (Expandable) -->
        <div class="max-w-2xl mx-auto mb-10" x-show="activeTab === 'memos'">
            <div @click.away="isCreating = false" class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-200">
                
                <!-- État réduit -->
                <div x-show="!isCreating" @click="isCreating = true" class="p-4 cursor-text flex items-center justify-between text-gray-500 hover:bg-gray-50 transition">
                    <span class="font-medium">Créer une nouvelle note...</span>
                    <i class="fas fa-plus-circle text-2xl text-yellow-500"></i>
                </div>

                <!-- État ouvert (Formulaire) -->
                <div x-show="isCreating" style="display: none;">
                    <!-- Zone de texte colorée selon la sélection -->
                    <div :class="{
                        'bg-white': selectedColor === 'white',
                        'bg-red-50': selectedColor === 'red',
                        'bg-yellow-50': selectedColor === 'yellow',
                        'bg-green-50': selectedColor === 'green',
                        'bg-blue-50': selectedColor === 'blue'
                    }" class="p-4 transition-colors duration-200">
                        <input type="text" placeholder="Titre" class="w-full text-lg font-bold bg-transparent border-none focus:ring-0 p-0 mb-2 placeholder-gray-400 text-gray-900">
                        <textarea rows="3" placeholder="Tapez votre mémo ici..." class="w-full text-sm bg-transparent border-none focus:ring-0 resize-none p-0 text-gray-600 placeholder-gray-400"></textarea>
                    </div>
                    
                    <!-- Barre d'outils du bas -->
                    <div class="bg-gray-50 px-4 py-3 flex items-center justify-between border-t border-gray-100">
                        <!-- Sélecteur de couleur -->
                        <div class="flex space-x-2">
                            <button @click="selectedColor = 'white'" class="w-6 h-6 rounded-full border border-gray-300 bg-white hover:ring-2 hover:ring-gray-400 focus:outline-none"></button>
                            <button @click="selectedColor = 'yellow'" class="w-6 h-6 rounded-full border border-yellow-200 bg-yellow-100 hover:ring-2 hover:ring-yellow-400 focus:outline-none"></button>
                            <button @click="selectedColor = 'red'" class="w-6 h-6 rounded-full border border-red-200 bg-red-100 hover:ring-2 hover:ring-red-400 focus:outline-none"></button>
                            <button @click="selectedColor = 'green'" class="w-6 h-6 rounded-full border border-green-200 bg-green-100 hover:ring-2 hover:ring-green-400 focus:outline-none"></button>
                            <button @click="selectedColor = 'blue'" class="w-6 h-6 rounded-full border border-blue-200 bg-blue-100 hover:ring-2 hover:ring-blue-400 focus:outline-none"></button>
                        </div>

                        <div class="flex space-x-3">
                            <button @click="isCreating = false" class="text-sm text-gray-500 hover:text-gray-700 font-medium">Fermer</button>
                            <button class="inline-flex items-center px-4 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none">
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. GRILLE DES MÉMOS (Masonry Layout) -->
        <div class="columns-1 md:columns-2 lg:columns-3 gap-6 space-y-6 pb-10">

            <!-- CARTE 1 : Favori (Jaune) -->
            <div x-show="activeTab === 'memos' || activeTab === 'favorites'" class="break-inside-avoid bg-yellow-50 rounded-lg shadow border border-yellow-100 hover:shadow-lg transition-shadow duration-200 group relative">
                <!-- Pin Icon (Active) -->
                <div class="absolute top-4 right-4 text-yellow-500">
                    <i class="fas fa-thumbtack transform rotate-45"></i>
                </div>
                
                <div class="p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 pr-6">Codes Accès Serveur</h3>
                    <p class="text-gray-700 text-sm mb-4 font-mono bg-yellow-100 p-2 rounded">
                        User: admin_root<br>
                        Pass: Xy9#mP2!vv
                    </p>
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-yellow-200">
                        <span class="text-xs text-yellow-700 font-medium">Favori</span>
                        <div class="flex space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="text-gray-400 hover:text-gray-600" title="Archiver"><i class="fas fa-archive"></i></button>
                            <button class="text-gray-400 hover:text-red-600" title="Supprimer"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARTE 2 : Standard (Blanc) -->
            <div x-show="activeTab === 'memos'" class="break-inside-avoid bg-white rounded-lg shadow border border-gray-200 hover:shadow-lg transition-shadow duration-200 group relative">
                <div class="p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Réunion Lundi Matin</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Préparer les points suivants :
                        <ul class="list-disc list-inside mt-1 ml-1">
                            <li>Budget Q4</li>
                            <li>Recrutement stagiaires</li>
                            <li>Nouveaux locaux</li>
                        </ul>
                    </p>
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                        <span class="text-xs text-gray-400">Hier à 14:30</span>
                        <div class="flex space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="text-gray-400 hover:text-yellow-500" title="Favori"><i class="far fa-star"></i></button>
                            <button class="text-gray-400 hover:text-gray-600" title="Archiver"><i class="fas fa-archive"></i></button>
                            <button class="text-gray-400 hover:text-red-600" title="Supprimer"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARTE 3 : Urgent (Rouge) -->
            <div x-show="activeTab === 'memos'" class="break-inside-avoid bg-red-50 rounded-lg shadow border border-red-100 hover:shadow-lg transition-shadow duration-200 group relative">
                <div class="p-5">
                    <div class="flex justify-between items-start">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">A FAIRE AVANT CE SOIR</h3>
                    </div>
                    <p class="text-gray-800 text-sm">
                        Envoyer le rapport final au directeur. Vérifier les pièces jointes (PDF).
                    </p>
                    <div class="mt-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-200 text-red-800">
                            Urgent
                        </span>
                    </div>
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-red-200">
                        <span class="text-xs text-red-400">Il y a 2h</span>
                        <div class="flex space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="text-red-400 hover:text-yellow-600" title="Favori"><i class="far fa-star"></i></button>
                            <button class="text-red-400 hover:text-gray-700" title="Archiver"><i class="fas fa-archive"></i></button>
                            <button class="text-red-400 hover:text-red-700" title="Supprimer"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARTE 4 : Archivée (Grisé) -->
            <div x-show="activeTab === 'archives'" class="break-inside-avoid bg-gray-50 rounded-lg shadow-sm border border-gray-200 opacity-75 hover:opacity-100 transition group relative">
                <div class="p-5">
                    <h3 class="text-lg font-bold text-gray-700 mb-2 line-through">Idée Cadeau Secret Santa</h3>
                    <p class="text-gray-500 text-sm">
                        Une tasse personnalisée ou un bon d'achat Amazon.
                    </p>
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
                        <span class="text-xs text-gray-400 flex items-center"><i class="fas fa-archive mr-1"></i> Archivé</span>
                        <div class="flex space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="text-gray-400 hover:text-blue-600" title="Restaurer"><i class="fas fa-box-open"></i></button>
                            <button class="text-gray-400 hover:text-red-600" title="Supprimer Définitivement"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                </div>
            </div>
             <!-- CARTE 5 : Confidentiel (Bleu) -->
             <div x-show="activeTab === 'memos' || activeTab === 'favorites'" class="break-inside-avoid bg-blue-50 rounded-lg shadow border border-blue-100 hover:shadow-lg transition-shadow duration-200 group relative">
                 <!-- Cadenas Icon -->
                 <div class="absolute top-4 right-4 text-blue-300">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Note Confidentielle RH</h3>
                    <p class="text-gray-700 text-sm blur-sm hover:blur-none transition duration-300 cursor-pointer select-none">
                        Discussion prévue avec J.Doe concernant la promotion au poste de Senior Dev.
                    </p>
                    <p class="text-xs text-blue-400 mt-1 italic">(Survolez pour lire)</p>

                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-blue-200">
                        <span class="text-xs text-blue-400">Privé</span>
                        <div class="flex space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="text-blue-400 hover:text-yellow-600" title="Favori"><i class="far fa-star"></i></button>
                            <button class="text-blue-400 hover:text-gray-600" title="Archiver"><i class="fas fa-archive"></i></button>
                            <button class="text-blue-400 hover:text-red-600" title="Supprimer"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

            @endif

            @if($currentContent == 'settings-content')reports-content
               @livewire('setting.settings')
            @endif

        </main>
        
    </div>



    
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
                        
               <!-- CONTENEUR PRINCIPAL -->
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-10">

                    <!-- EN-TÊTE DE SECTION -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Paramètres de l'application</h2>
                            <p class="text-sm text-gray-500 mt-1">Gérez les configurations des modules et les privilèges d'accès.</p>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition shadow-sm">
                                Annuler
                            </button>
                            <button type="button" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm shadow-indigo-200">
                                Enregistrer les modifications
                            </button>
                        </div>
                    </div>

                    <!-- 1. SECTION MODULES (GRID 2x2) -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            Configuration des Tables (Modules)
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- MODULE 1 : PRODUITS (Exemple rempli) -->
                            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-300 relative overflow-hidden">
                                <div class="absolute top-0 right-0 p-4 opacity-10">
                                    <span class="text-6xl font-bold text-gray-900">1</span>
                                </div>
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-800">Module Produits</h4>
                                        <span class="px-2 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-full ring-1 ring-green-600/20">Actif</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Nom de la table (DB)</label>
                                            <input type="text" value="app_products" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                        </div>
                                        <div class="flex gap-3">
                                            <div class="w-1/2">
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Quota Entrées</label>
                                                <input type="number" value="1000" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                            </div>
                                            <div class="w-1/2">
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Visibilité</label>
                                                <select class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                                    <option selected>Public</option>
                                                    <option>Privé</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MODULE 2 : CLIENTS (Exemple rempli) -->
                            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-300 relative overflow-hidden">
                                <div class="absolute top-0 right-0 p-4 opacity-10">
                                    <span class="text-6xl font-bold text-gray-900">2</span>
                                </div>
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-800">Module Clients</h4>
                                        <span class="px-2 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-full ring-1 ring-green-600/20">Actif</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Nom de la table (DB)</label>
                                            <input type="text" value="app_customers" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                        </div>
                                        <div class="flex gap-3">
                                            <div class="w-1/2">
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Quota Entrées</label>
                                                <input type="number" value="5000" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                            </div>
                                            <div class="w-1/2">
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Export</label>
                                                <select class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                                    <option selected>Autorisé</option>
                                                    <option>Bloqué</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MODULE 3 : COMMANDES (Exemple Maintenance) -->
                            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-300 relative overflow-hidden">
                                <div class="absolute top-0 right-0 p-4 opacity-10">
                                    <span class="text-6xl font-bold text-gray-900">3</span>
                                </div>
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-800">Module Commandes</h4>
                                        <span class="px-2 py-1 bg-yellow-50 text-yellow-700 text-xs font-medium rounded-full ring-1 ring-yellow-600/20">Maintenance</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">API Endpoint</label>
                                            <input type="text" value="/api/v1/orders" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                        </div>
                                        <div class="flex gap-3">
                                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg w-full cursor-pointer hover:bg-gray-50">
                                                <input type="checkbox" checked class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                                <span class="text-sm text-gray-700">Lecture seule</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MODULE 4 : STOCKS (Exemple Inactif) -->
                            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-300 relative overflow-hidden">
                                <div class="absolute top-0 right-0 p-4 opacity-10">
                                    <span class="text-6xl font-bold text-gray-900">4</span>
                                </div>
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-semibold text-gray-800">Module Stocks</h4>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full ring-1 ring-gray-500/20">Inactif</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Nom de la table (DB)</label>
                                            <input type="text" placeholder="Non configuré" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 border p-2.5">
                                        </div>
                                        <div class="flex gap-3">
                                            <button type="button" class="w-full py-2.5 text-sm text-indigo-600 font-medium bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">Activer le module</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-200">

                    <!-- 2. SECTION UTILISATEURS (STATIQUE) -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                Contrôle d'accès & Droits
                            </h3>
                            <div class="relative">
                                <input type="text" placeholder="Rechercher un user..." class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 w-64 shadow-sm">
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>

                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Utilisateur</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date d'ajout</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Droits Admin</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    
                                    <!-- Ligne 1 : Utilisateur Standard (Actif) -->
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-9 w-9">
                                                    <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">JD</div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Jean Dupont</div>
                                                    <div class="text-sm text-gray-500">jean.d@example.com</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            12 Jan 2024
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <!-- Toggle switch désactivé (pas admin) -->
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="sr-only peer">
                                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                            </label>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span> Actif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button type="button" class="text-red-600 hover:text-red-900 hover:bg-red-50 px-3 py-1.5 rounded-md transition border border-transparent hover:border-red-200">
                                                Désactiver
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Ligne 2 : Administrateur (Actif) -->
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-9 w-9">
                                                    <img class="h-9 w-9 rounded-full object-cover" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Sarah Connor</div>
                                                    <div class="text-sm text-gray-500">sarah@skynet.com</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            15 Fev 2024
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <!-- Toggle switch activé (checked) -->
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="sr-only peer" checked>
                                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                            </label>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span> Actif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button type="button" class="text-gray-400 cursor-not-allowed px-3 py-1.5" disabled>
                                                Désactiver
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Ligne 3 : Utilisateur Désactivé -->
                                    <tr class="bg-gray-50 opacity-60">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-9 w-9">
                                                    <div class="h-9 w-9 rounded-full bg-gray-300 flex items-center justify-center text-gray-500 font-bold text-sm">AE</div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Ancien Employé</div>
                                                    <div class="text-sm text-gray-500">ex@example.com</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            01 Jan 2023
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-xs text-gray-400">-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-600">
                                                Inactif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button type="button" class="text-indigo-600 hover:text-indigo-900 font-semibold px-3 py-1.5">
                                                Réactiver
                                            </button>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination Visuelle -->
                        <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 rounded-lg">
                            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Affichage de <span class="font-medium">1</span> à <span class="font-medium">3</span> sur <span class="font-medium">12</span> résultats
                                    </p>
                                </div>
                                <div>
                                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                        <a href="#" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                            <span class="sr-only">Précédent</span>
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
                                        </a>
                                        <a href="#" aria-current="page" class="relative z-10 inline-flex items-center bg-indigo-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">1</a>
                                        <a href="#" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">2</a>
                                        <a href="#" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                            <span class="sr-only">Suivant</span>
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                                        </a>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>    

            @endif

            @if($currentContent == 'settings-content')reports-content
              
            @endif

        </main>
        
    </div>

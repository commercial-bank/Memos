<div class="flex flex-col gap-6">
    <!-- En-tête -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Rapports d'Activité</h1>
        
        <!-- Période Selector -->
        <div class="bg-white rounded-lg border border-gray-200 p-1 flex text-sm shadow-sm">
            <button class="px-3 py-1 rounded bg-gray-100 font-bold text-gray-800">Mois</button>
            <button class="px-3 py-1 rounded hover:bg-gray-50 text-gray-500 transition-colors">Trimestre</button>
            <button class="px-3 py-1 rounded hover:bg-gray-50 text-gray-500 transition-colors">Année</button>
        </div>
    </div>

    <!-- KPI Cards (Top Row) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1 -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-file-alt text-4xl text-[#daaf2c]"></i>
            </div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Mémos Traités</p>
            <h3 class="text-3xl font-extrabold text-gray-900 mt-1">128</h3>
            <span class="text-xs text-green-600 font-bold flex items-center gap-1 mt-2">
                <i class="fas fa-arrow-up"></i> +12% vs mois dernier
            </span>
        </div>

        <!-- Card 2 -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Temps Moyen Validation</p>
            <h3 class="text-3xl font-extrabold text-gray-900 mt-1">2.4 <span class="text-sm font-normal text-gray-500">Jours</span></h3>
            <span class="text-xs text-red-500 font-bold flex items-center gap-1 mt-2">
                <i class="fas fa-arrow-up"></i> +0.5j (Plus lent)
            </span>
        </div>

        <!-- Card 3 -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Taux de Rejet</p>
            <h3 class="text-3xl font-extrabold text-gray-900 mt-1">5%</h3>
            <span class="text-xs text-green-600 font-bold flex items-center gap-1 mt-2">
                <i class="fas fa-arrow-down"></i> Stable
            </span>
        </div>

        <!-- Card 4 -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Utilisateurs Actifs</p>
            <h3 class="text-3xl font-extrabold text-gray-900 mt-1">42</h3>
            <div class="flex -space-x-2 mt-2">
                <img class="w-6 h-6 rounded-full border border-white" src="{{ asset('images/user3.png') }}">
                <div class="w-6 h-6 rounded-full border border-white bg-gray-200 text-[10px] flex items-center justify-center font-bold text-gray-600">+10</div>
            </div>
        </div>
    </div>

    <!-- Section Graphiques (Visuel CSS Simple) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-80">
        
        <!-- Graphique Principal (Bar Chart CSS) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col">
            <h3 class="font-bold text-gray-800 mb-6">Volume Mémos par Département</h3>
            
            <div class="flex-1 flex items-end gap-4 justify-between px-4 pb-2 border-b border-gray-100">
                <!-- Bar Item -->
                <div class="flex flex-col items-center gap-2 group w-full">
                    <div class="w-full bg-[#daaf2c]/20 h-32 rounded-t-md relative group-hover:bg-[#daaf2c] transition-colors">
                        <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 text-xs font-bold opacity-0 group-hover:opacity-100 transition-opacity">45</div>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase">RH</span>
                </div>
                 <!-- Bar Item -->
                <div class="flex flex-col items-center gap-2 group w-full">
                    <div class="w-full bg-[#daaf2c]/20 h-48 rounded-t-md relative group-hover:bg-[#daaf2c] transition-colors"></div>
                    <span class="text-xs font-bold text-gray-400 uppercase">IT</span>
                </div>
                 <!-- Bar Item -->
                <div class="flex flex-col items-center gap-2 group w-full">
                    <div class="w-full bg-[#daaf2c]/20 h-24 rounded-t-md relative group-hover:bg-[#daaf2c] transition-colors"></div>
                    <span class="text-xs font-bold text-gray-400 uppercase">FIN</span>
                </div>
                 <!-- Bar Item -->
                <div class="flex flex-col items-center gap-2 group w-full">
                    <div class="w-full bg-[#daaf2c]/20 h-40 rounded-t-md relative group-hover:bg-[#daaf2c] transition-colors"></div>
                    <span class="text-xs font-bold text-gray-400 uppercase">COM</span>
                </div>
            </div>
        </div>

        <!-- Activité Récente -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 overflow-y-auto custom-scrollbar">
            <h3 class="font-bold text-gray-800 mb-4">Derniers Mouvements</h3>
            <div class="space-y-4">
                <div class="flex gap-3 items-start">
                    <div class="w-2 h-2 rounded-full bg-green-500 mt-1.5 flex-shrink-0"></div>
                    <div>
                        <p class="text-sm text-gray-800 font-medium">Memo #102 Validé</p>
                        <p class="text-xs text-gray-400">Par Directeur Général - Il y a 10min</p>
                    </div>
                </div>
                <div class="flex gap-3 items-start">
                    <div class="w-2 h-2 rounded-full bg-red-500 mt-1.5 flex-shrink-0"></div>
                    <div>
                        <p class="text-sm text-gray-800 font-medium">Memo #99 Rejeté</p>
                        <p class="text-xs text-gray-400">Motif: Budget insuffisant - Il y a 1h</p>
                    </div>
                </div>
                 <div class="flex gap-3 items-start">
                    <div class="w-2 h-2 rounded-full bg-[#daaf2c] mt-1.5 flex-shrink-0"></div>
                    <div>
                        <p class="text-sm text-gray-800 font-medium">Nouvelle Tâche assignée</p>
                        <p class="text-xs text-gray-400">À Alice M. - Il y a 3h</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
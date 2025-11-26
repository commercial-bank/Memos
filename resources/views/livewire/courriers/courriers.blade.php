 <div class="min-h-screen bg-gray-100 py-8" x-data="{ activeTab: 'entrant', filterType: 'all' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- 1. HEADER & KPI (Indicateurs) -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Gestion du Courrier</h1>
            <p class="mt-1 text-sm text-gray-600">Enregistrement, distribution et suivi des flux documentaires.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-8">
            <!-- KPI 1 -->
            <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-blue-500">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Courriers Entrants (Jour)</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">12</dd>
                </div>
            </div>
            <!-- KPI 2 -->
            <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-yellow-500">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">En attente de transmission</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">4</dd>
                </div>
            </div>
            <!-- KPI 3 -->
            <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-red-500">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Factures à payer</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">3</dd>
                </div>
            </div>
            <!-- KPI 4 -->
            <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-green-500">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Archives (Total)</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">1,240</dd>
                </div>
            </div>
        </div>

        <!-- 2. BARRE D'ACTION & NAVIGATION -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex" aria-label="Tabs">
                    <!-- Onglet ARRIVÉE -->
                    <button 
                        @click="activeTab = 'entrant'"
                        :class="activeTab === 'entrant' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-lg flex justify-center items-center transition duration-150"
                    >
                        <i class="fas fa-inbox mr-2"></i> Courrier Arrivée
                    </button>
                    <!-- Onglet DÉPART -->
                    <button 
                        @click="activeTab = 'sortant'"
                        :class="activeTab === 'sortant' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-lg flex justify-center items-center transition duration-150"
                    >
                        <i class="fas fa-paper-plane mr-2"></i> Courrier Départ
                    </button>
                </nav>
            </div>

            <!-- Barre d'outils (Filtres & Recherche) -->
            <div class="p-4 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
                
                <!-- Groupe de boutons Nouveau -->
                <div x-show="activeTab === 'entrant'">
                    <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-plus mr-2"></i> Enregistrer Arrivée
                    </button>
                </div>
                <div x-show="activeTab === 'sortant'" style="display: none;">
                    <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        <i class="fas fa-plus mr-2"></i> Créer Départ
                    </button>
                </div>

                <!-- Filtres -->
                <div class="flex space-x-2">
                    <button @click="filterType = 'all'" :class="filterType === 'all' ? 'bg-gray-200 text-gray-800' : 'bg-white text-gray-500'" class="px-3 py-1 rounded-full text-xs font-medium border">Tout</button>
                    <button @click="filterType = 'invoice'" :class="filterType === 'invoice' ? 'bg-purple-100 text-purple-800 border-purple-200' : 'bg-white text-gray-500'" class="px-3 py-1 rounded-full text-xs font-medium border">Factures</button>
                    <button @click="filterType = 'urgent'" :class="filterType === 'urgent' ? 'bg-red-100 text-red-800 border-red-200' : 'bg-white text-gray-500'" class="px-3 py-1 rounded-full text-xs font-medium border">Urgent</button>
                </div>

                <!-- Recherche -->
                <div class="relative rounded-md shadow-sm max-w-xs w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md py-2" placeholder="N° Réf, Expéditeur...">
                </div>
            </div>
        </div>

        <!-- 3. CONTENU : LISTE DES COURRIERS (ARRIVÉE) -->
        <div x-show="activeTab === 'entrant'" class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                
                <!-- ITEM 1 : Facture (Nouvelle) -->
                <li>
                    <div class="block hover:bg-gray-50 cursor-pointer transition duration-150">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center truncate">
                                    <!-- Icone Type -->
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 mr-3">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </div>
                                    <p class="text-sm font-medium text-blue-600 truncate">Réf: ARR-2023/1042</p>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Nouveau
                                    </span>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-500">
                                        <i class="far fa-clock mr-1"></i> 25 Nov 2025
                                    </p>
                                </div>
                            </div>
                            
                            <div class="mt-2 sm:flex sm:justify-between">
                                <div class="sm:flex">
                                    <p class="flex items-center text-sm text-gray-900 font-bold mr-6">
                                        <i class="fas fa-user-tie text-gray-400 mr-2"></i> ENEO Cameroun
                                    </p>
                                    <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                        Facture électricité Octobre - Siège
                                    </p>
                                </div>
                                <!-- Actions Rapides -->
                                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 gap-2">
                                    <button class="text-gray-400 hover:text-blue-600" title="Voir"><i class="far fa-eye"></i></button>
                                    <button class="text-gray-400 hover:text-yellow-600" title="Annoter/Transmettre"><i class="fas fa-share"></i></button>
                                </div>
                            </div>

                            <!-- TIMELINE D'ACHEMINEMENT (Le Concept Clé) -->
                            <div class="mt-4 pt-3 border-t border-gray-100">
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                        <div class="w-full border-t border-gray-300"></div>
                                    </div>
                                    <div class="relative flex justify-between">
                                        <!-- Etape 1 : Enregistré -->
                                        <div class="bg-white px-1">
                                            <span class="h-6 w-6 rounded-full bg-green-500 flex items-center justify-center ring-4 ring-white" title="Enregistré au Secrétariat">
                                                <i class="fas fa-check text-white text-xs"></i>
                                            </span>
                                            <span class="absolute -bottom-6 left-0 text-[10px] text-gray-500 font-medium">Secrétariat</span>
                                        </div>
                                        <!-- Etape 2 : Transmission DG -->
                                        <div class="bg-white px-1">
                                            <span class="h-6 w-6 rounded-full bg-blue-500 flex items-center justify-center ring-4 ring-white animate-pulse" title="En cours: Bureau DG">
                                                <i class="fas fa-user-tie text-white text-xs"></i>
                                            </span>
                                            <span class="absolute -bottom-6 left-1/4 text-[10px] text-blue-600 font-bold">Direction</span>
                                        </div>
                                        <!-- Etape 3 : Service (Gris = Pas encore) -->
                                        <div class="bg-white px-1">
                                            <span class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center ring-4 ring-white">
                                                <i class="fas fa-building text-gray-400 text-xs"></i>
                                            </span>
                                            <span class="absolute -bottom-6 left-1/2 text-[10px] text-gray-400">Service</span>
                                        </div>
                                        <!-- Etape 4 : Clôturé -->
                                        <div class="bg-white px-1">
                                            <span class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center ring-4 ring-white">
                                                <i class="fas fa-flag-checkered text-gray-400 text-xs"></i>
                                            </span>
                                            <span class="absolute -bottom-6 right-0 text-[10px] text-gray-400">Archivé</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="h-4"></div> <!-- Spacer pour les labels timeline -->
                            </div>
                        </div>
                    </div>
                </li>

                <!-- ITEM 2 : Courrier Simple (Traité) -->
                <li>
                    <div class="block hover:bg-gray-50 cursor-pointer transition duration-150">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center truncate">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 mr-3">
                                        <i class="far fa-envelope"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900 truncate">Réf: ARR-2023/1040</p>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Archivé
                                    </span>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Traité le 24 Nov
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 sm:flex sm:justify-between">
                                <div class="sm:flex">
                                    <p class="flex items-center text-sm text-gray-900 font-bold mr-6">
                                        <i class="fas fa-university text-gray-400 mr-2"></i> Ministère des Finances
                                    </p>
                                    <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                        Demande de renseignements fiscaux
                                    </p>
                                </div>
                            </div>
                            <!-- Timeline Completée -->
                            <div class="mt-4 pt-3 border-t border-gray-100 opacity-50">
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                        <div class="w-full border-t border-green-300"></div>
                                    </div>
                                    <div class="relative flex justify-between">
                                        <div class="bg-white px-1"><span class="h-6 w-6 rounded-full bg-green-500 flex items-center justify-center"><i class="fas fa-check text-white text-xs"></i></span></div>
                                        <div class="bg-white px-1"><span class="h-6 w-6 rounded-full bg-green-500 flex items-center justify-center"><i class="fas fa-check text-white text-xs"></i></span></div>
                                        <div class="bg-white px-1"><span class="h-6 w-6 rounded-full bg-green-500 flex items-center justify-center"><i class="fas fa-check text-white text-xs"></i></span></div>
                                        <div class="bg-white px-1"><span class="h-6 w-6 rounded-full bg-green-500 flex items-center justify-center"><i class="fas fa-check text-white text-xs"></i></span></div>
                                    </div>
                                </div>
                                <div class="h-4"></div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
            
            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <!-- Code de pagination Laravel standard ici -->
                <p class="text-sm text-gray-700">Affichage de 1 à 10 sur 1240 résultats</p>
            </div>
        </div>

        <!-- 4. CONTENU : LISTE DES COURRIERS (DÉPART) -->
        <div x-show="activeTab === 'sortant'" style="display: none;" class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                <li>
                    <div class="px-4 py-4 sm:px-6 hover:bg-yellow-50 transition">
                         <div class="flex items-center justify-between">
                            <div class="flex items-center truncate">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 mr-3">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-900">Réf: DEP-2023/502</p>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    En cours de signature
                                </span>
                            </div>
                            <div class="text-sm text-gray-500">Créé aujourd'hui</div>
                        </div>
                        <div class="mt-2">
                             <p class="text-sm text-gray-900 font-bold">Destinataire: Banque Atlantique</p>
                             <p class="text-sm text-gray-500">Objet: Ordre de virement Salaire Nov.</p>
                        </div>
                        <!-- Barre de progression Depart -->
                        <div class="mt-3 w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-yellow-500 h-2.5 rounded-full" style="width: 45%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-400 mt-1">
                            <span>Rédaction</span>
                            <span class="text-yellow-600 font-bold">Validation N+1</span>
                            <span>Signature DG</span>
                            <span>Expédié</span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

    </div>
</div>

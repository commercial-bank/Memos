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

        
        <div class="min-h-screen bg-slate-50 py-8 font-sans" x-data="{ 
    activeTab: 'entrant', 
    subTab: 'factures', 
    standardZone: 'douala',
    showWorkflowModal: false
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- 1. HEADER GLOBAL -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Gestion des Flux</h1>
                <p class="mt-1 text-sm text-slate-500">Plateforme centralisée de traitement du courrier.</p>
            </div>
            <!-- Boutons d'action rapide globaux -->
            <div class="mt-4 md:mt-0 flex space-x-3">
                <button class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                    <i class="fas fa-cog mr-2"></i> Paramètres
                </button>
            </div>
        </div>

        <!-- 2. NAVIGATION PRINCIPALE (Arrivée / Départ) -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6">
            <div class="border-b border-slate-100">
                <nav class="flex -mb-px" aria-label="Tabs">
                    <button 
                        @click="activeTab = 'entrant'"
                        :class="activeTab === 'entrant' ? 'border-indigo-500 text-indigo-600 bg-indigo-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-lg flex justify-center items-center transition-all duration-200 rounded-tl-xl"
                    >
                        <div class="flex items-center gap-2">
                            <span class="p-2 rounded-lg" :class="activeTab === 'entrant' ? 'bg-indigo-100' : 'bg-slate-100'"><i class="fas fa-inbox"></i></span>
                            Courrier Arrivée
                        </div>
                    </button>
                    <button 
                        @click="activeTab = 'sortant'"
                        :class="activeTab === 'sortant' ? 'border-amber-500 text-amber-600 bg-amber-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-lg flex justify-center items-center transition-all duration-200 rounded-tr-xl"
                    >
                         <div class="flex items-center gap-2">
                            <span class="p-2 rounded-lg" :class="activeTab === 'sortant' ? 'bg-amber-100' : 'bg-slate-100'"><i class="fas fa-paper-plane"></i></span>
                            Courrier Départ
                        </div>
                    </button>
                </nav>
            </div>

            <!-- CONTENU SECTION ARRIVÉE -->
            <div x-show="activeTab === 'entrant'" class="p-6">
                
                <!-- 2.1 SOUS-NAVIGATION (Factures vs Standards) -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <!-- Toggle Switch -->
                    <div class="bg-slate-100 p-1 rounded-lg inline-flex shadow-inner">
                        <button 
                            @click="subTab = 'factures'"
                            :class="subTab === 'factures' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                            class="px-6 py-2 rounded-md text-sm font-semibold transition-all duration-200 flex items-center"
                        >
                            <i class="fas fa-file-invoice-dollar mr-2"></i> Factures
                        </button>
                        <button 
                            @click="subTab = 'standard'"
                            :class="subTab === 'standard' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                            class="px-6 py-2 rounded-md text-sm font-semibold transition-all duration-200 flex items-center"
                        >
                            <i class="fas fa-envelope-open-text mr-2"></i> Courriers Standards
                        </button>
                    </div>

                    <!-- Actions Contextuelles -->
                    <button x-show="subTab === 'factures'" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-lg shadow-indigo-500/30">
                        <i class="fas fa-plus mr-2"></i> Nouvelle Facture
                    </button>
                    <button x-show="subTab === 'standard'" class="inline-flex items-center px-4 py-2 bg-slate-800 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700 active:bg-slate-900 focus:outline-none focus:border-slate-900 focus:ring ring-slate-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-lg shadow-slate-500/30">
                        <i class="fas fa-plus mr-2"></i> Nouveau Courrier
                    </button>
                </div>

                <!-- 2.2 VUE FACTURES (Le Registre Financier) -->
                <div x-show="subTab === 'factures'" class="animate-fade-in-up">
                    
                    <!-- KPI Factures Rapides -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-100 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-indigo-500 uppercase">Total HT (Jour)</p>
                                <p class="text-xl font-bold text-indigo-900">12 450 000 FCFA</p>
                            </div>
                            <div class="bg-white p-2 rounded-full text-indigo-600"><i class="fas fa-coins"></i></div>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4 border border-orange-100 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-orange-500 uppercase">À Traiter</p>
                                <p class="text-xl font-bold text-orange-900">5 Factures</p>
                            </div>
                            <div class="bg-white p-2 rounded-full text-orange-600"><i class="fas fa-hourglass-half"></i></div>
                        </div>
                    </div>

                    <!-- Tableau Registre Facture -->
                    <div class="bg-white overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500 sm:pl-6">Date & N° Ordre</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Expéditeur & Facture</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Montant HT</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Traçabilité / Workflow</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <!-- Ligne Facture 1 -->
                                <tr class="hover:bg-indigo-50/30 transition duration-150 group">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                        <div class="font-bold text-gray-900">11 Déc 2025</div>
                                        <div class="text-gray-500 text-xs">Ordre #042</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <div class="font-medium text-indigo-600">ORANGE Cameroun</div>
                                        <div class="text-gray-500 text-xs">Ref: FAC-2023-889 | Qté: 1</div>
                                        <div class="flex items-center gap-1 mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                                <i class="fas fa-paperclip mr-1 text-gray-400"></i> Scannée
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                Bordereau Oui
                                            </span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <div class="font-mono font-bold text-gray-900">450 000 XAF</div>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 w-1/3">
                                        <!-- BARRE DE WORKFLOW VISUELLE -->
                                        <div class="relative">
                                            <div class="flex items-center justify-between text-[10px] font-medium text-gray-400 mb-1">
                                                <span class="text-indigo-600">Arrivée</span>
                                                <span class="text-indigo-600">Compta.</span>
                                                <span class="">Validation</span>
                                                <span class="">Paiement</span>
                                            </div>
                                            <div class="overflow-hidden h-2 mb-0 text-xs flex rounded bg-gray-200">
                                                <div style="width: 50%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500 animate-pulse"></div>
                                            </div>
                                            <div class="mt-1 text-xs text-indigo-700 font-semibold flex items-center">
                                                <i class="fas fa-map-marker-alt mr-1"></i> Actuellement: Service Comptabilité
                                            </div>
                                        </div>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button class="text-gray-400 hover:text-indigo-600 p-1" title="Voir Scan"><i class="far fa-eye fa-lg"></i></button>
                                            <button class="bg-indigo-600 text-white px-3 py-1 rounded-md text-xs hover:bg-indigo-700 shadow-sm" title="Transmettre">
                                                Envoyer <i class="fas fa-share ml-1"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Ligne Facture 2 (Terminée) -->
                                <tr class="hover:bg-gray-50 transition duration-150 group">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                        <div class="font-bold text-gray-900">10 Déc 2025</div>
                                        <div class="text-gray-500 text-xs">Ordre #041</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <div class="font-medium text-gray-900">CAMAIR-CO</div>
                                        <div class="text-gray-500 text-xs">Ref: BILLET-001 | Qté: 2</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <div class="font-mono font-bold text-gray-900">120 000 XAF</div>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500">
                                        <div class="relative opacity-60">
                                            <div class="overflow-hidden h-2 mb-0 text-xs flex rounded bg-green-200">
                                                <div style="width: 100%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"></div>
                                            </div>
                                            <div class="mt-1 text-xs text-green-700 font-semibold flex items-center">
                                                <i class="fas fa-check-circle mr-1"></i> Payé
                                            </div>
                                        </div>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <button class="text-gray-400 hover:text-gray-600 p-1"><i class="fas fa-archive"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2.3 VUE COURRIERS STANDARDS (Le Registre Administratif) -->
                <div x-show="subTab === 'standard'" class="animate-fade-in-up" style="display: none;">
                    
                    <!-- Filtre de Registre (Zone Géographique) -->
                    <div class="bg-slate-800 rounded-lg p-4 mb-6 text-white shadow-lg">
                        <div class="flex flex-col md:flex-row md:items-center gap-4">
                            <div class="flex-shrink-0">
                                <span class="bg-slate-700 p-2 rounded-lg border border-slate-600">
                                    <i class="fas fa-globe-africa text-blue-400"></i> Choix du Registre
                                </span>
                            </div>
                            <div class="flex-grow grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Selecteur Zone -->
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Zone Géographique</label>
                                    <select x-model="standardZone" class="block w-full rounded-md border-gray-600 bg-slate-700 text-white focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2">
                                        <option value="douala">Douala (Siège & Agences)</option>
                                        <option value="regions">Régions (Villes)</option>
                                    </select>
                                </div>
                                
                                <!-- Selecteur Dynamique -->
                                <div x-show="standardZone === 'douala'">
                                    <label class="block text-xs text-slate-400 mb-1">Agence / Entité</label>
                                    <select class="block w-full rounded-md border-gray-600 bg-slate-700 text-white focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2">
                                        <option>Siège - Direction Générale</option>
                                        <option>Agence Akwa</option>
                                        <option>Agence Bonanjo</option>
                                        <option>Agence Bonamoussadi</option>
                                    </select>
                                </div>
                                <div x-show="standardZone === 'regions'" style="display: none;">
                                    <label class="block text-xs text-slate-400 mb-1">Ville</label>
                                    <select class="block w-full rounded-md border-gray-600 bg-slate-700 text-white focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2">
                                        <option>Yaoundé</option>
                                        <option>Bafoussam</option>
                                        <option>Dschang</option>
                                        <option>Bandjoun</option>
                                        <option>Garoua</option>
                                    </select>
                                </div>

                                <!-- Recherche -->
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Recherche Rapide</label>
                                    <div class="relative">
                                        <input type="text" class="block w-full rounded-md border-gray-600 bg-slate-900 text-white placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 pl-8" placeholder="Expéditeur, Objet...">
                                        <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-slate-500 text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau Registre Standard -->
                    <div class="bg-white overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Date / Ordre</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Intitulé / Nature</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-gray-500 w-1/3">Objet</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Preuves</th>
                                    <th scope="col" class="px-3 py-3.5 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <!-- Ligne Standard 1 -->
                                <tr class="hover:bg-slate-50 transition duration-150">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
                                        <div class="font-bold text-gray-900">11 Déc</div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            #1055
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 text-sm">
                                        <div class="font-bold text-slate-700">M. TALLA Jean</div>
                                        <div class="text-xs text-slate-500 mt-1">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                Particulier Extérieur
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-600">
                                        <p class="line-clamp-2">Demande de rééchelonnement de crédit immobilier suite incendie.</p>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center text-xs text-green-600" title="Signature présente">
                                                <i class="fas fa-file-signature mr-1"></i> Décharge OK
                                            </div>
                                            <div class="flex items-center text-xs text-gray-400">
                                                <i class="fas fa-copy mr-1"></i> Bordereau: N/A
                                            </div>
                                        </div>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <button class="text-indigo-600 hover:text-indigo-900 font-bold bg-indigo-50 px-3 py-1 rounded-md border border-indigo-200">
                                            Traiter
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Ligne Standard 2 (Organisme Tutelle) -->
                                <tr class="hover:bg-red-50/40 transition duration-150 border-l-4 border-red-500">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
                                        <div class="font-bold text-gray-900">11 Déc</div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                            #1056 Urgent
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 text-sm">
                                        <div class="font-bold text-slate-700">COBAC (Libreville)</div>
                                        <div class="text-xs text-slate-500 mt-1">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-100 text-purple-800 border border-purple-200">
                                                Organisme Tutelle
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-600">
                                        <p class="line-clamp-2">Circulaire relative aux nouvelles normes prudentielles 2026.</p>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center text-xs text-green-600">
                                                <i class="fas fa-check mr-1"></i> Décharge OK
                                            </div>
                                            <div class="flex items-center text-xs text-blue-600 cursor-pointer hover:underline">
                                                <i class="fas fa-paperclip mr-1"></i> Voir PJ (PDF)
                                            </div>
                                        </div>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <button class="text-indigo-600 hover:text-indigo-900 font-bold bg-indigo-50 px-3 py-1 rounded-md border border-indigo-200">
                                            Traiter
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div> <!-- Fin Contenu Entrant -->
        </div>
    </div>
</div>

<style>
    /* Petite animation d'entrée */
    .animate-fade-in-up {
        animation: fadeInUp 0.3s ease-out;
    }
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

    </div>
</div>

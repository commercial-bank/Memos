<!-- Dashboard Container -->
                <div class="space-y-6">

                    <!-- 1. HEADER : Bienvenue & Date -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                                Tableau de Bord
                            </h2>
                            <p class="text-sm text-slate-500 mt-1">
                                Vue d'ensemble de vos activités et flux documentaires.
                            </p>
                        </div>
                        
                        <div class="mt-4 md:mt-0 flex items-center gap-3">
                            <!-- Date Widget -->
                            <div class="hidden md:flex items-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 shadow-sm">
                                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ now()->translatedFormat('d F Y') }}
                            </div>
                            
                            <!-- Bouton Création Rapide -->
                            <button wire:click="openModal" class="bg-[#daaf2c] hover:bg-[#daaf2c] text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-lg flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Nouveau Mémo
                            </button>
                        </div>
                    </div>

                    <!-- 2. KPI CARDS (Inspiration Bancaire) -->
                    <!-- Grid de 4 cartes avec indicateurs de progression -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        
                        <!-- Carte 1 : Mémos Sortants -->
                        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                            <div class="absolute right-0 top-0 h-full w-1 bg-yellow-500 group-hover:w-2 transition-all"></div>
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mémos Sortants</p>
                                    <h3 class="text-3xl font-bold text-slate-900 mt-2">
                                        {{ $memosSortantsCount }}
                                    </h3>
                                </div>
                                <div class="p-2 bg-yellow-50 rounded-lg text-yellow-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                </div>
                            </div>
                        </div>


                        <!-- Carte : Mémos Entrants -->
                        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                            <!-- Barre latérale de couleur (orange/rouge pour sortant souvent, ou bleu) -->
                            <div class="absolute right-0 top-0 h-full w-1 bg-blue-500 group-hover:w-2 transition-all"></div>
                            
                            <div class="flex justify-between items-start">
                                <div>
                                    <!-- J'ai mis à jour le label pour correspondre à la logique 'sortant' -->
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mémos Entrants</p>
                                    
                                    <!-- ICI : Affichage de la variable dynamique -->
                                    <h3 class="text-3xl font-bold text-slate-900 mt-2">
                                        {{ $memosEntrantsCount }}
                                    </h3>
                                </div>
                                
                                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                                    <!-- Icône (j'ai gardé la vôtre, mais pour sortant on utilise souvent une flèche vers le haut/droite) -->
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                </div>
                            </div>
                        </div>


                        <!-- Carte 3 : Courriers -->
                        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                            <div class="absolute right-0 top-0 h-full w-1 bg-purple-500 group-hover:w-2 transition-all"></div>
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Courriers</p>
                                    <h3 class="text-3xl font-bold text-slate-900 mt-2">142</h3>
                                </div>
                                <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-xs">
                                <span class="text-slate-500 font-medium">Archive globale</span>
                            </div>
                        </div>
                        
                        <!-- Carte 4 : Validation Requise (Action) -->
                        <!-- Vérification si l'utilisateur est Directeur -->
                        @if(auth()->user()->poste == 'Directeur')

                            <div class="bg-slate-900 rounded-xl p-6 shadow-lg shadow-slate-300 transform hover:-translate-y-1 transition-transform cursor-pointer">
                                <div class="flex justify-between items-start text-white">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">À Valider (DIR)</p>
                                        <!-- Correction : ajout du $ devant la variable -->
                                        <h3 class="text-3xl font-bold mt-2">{{ $toValidateCount_dir }}</h3>
                                    </div>
                                    <div class="p-2 bg-slate-800 rounded-lg text-yellow-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                </div>
                                <div class="mt-4 border-t border-slate-700 pt-2">
                                    <p class="text-xs text-slate-300">
                                        @if($toValidateCount_dir > 0)
                                            Documents en attente de signature DIR.
                                        @else
                                            Aucun mémo à signer.
                                        @endif
                                    </p>
                                </div>
                            </div>

                        <!-- Vérification si l'utilisateur est Sous-Directeur -->
                        @elseif(auth()->user()->poste == 'Sous-Directeur')

                            <div class="bg-slate-800 rounded-xl p-6 shadow-lg shadow-slate-300 transform hover:-translate-y-1 transition-transform cursor-pointer">
                                <div class="flex justify-between items-start text-white">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">À Valider (SD)</p>
                                        <!-- On affiche la variable spécifique au SD -->
                                        <h3 class="text-3xl font-bold mt-2">{{ $toValidateCount_sd }}</h3>
                                    </div>
                                    <div class="p-2 bg-slate-700 rounded-lg text-indigo-400">
                                        <!-- J'ai changé un peu l'icône ou la couleur pour différencier -->
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </div>
                                </div>
                                <div class="mt-4 border-t border-slate-600 pt-2">
                                    <p class="text-xs text-slate-300">
                                        @if($toValidateCount_sd > 0)
                                            Documents en attente de signature SD.
                                        @else
                                            Aucun mémo à viser.
                                        @endif
                                    </p>
                                </div>
                            </div>

                        @else

                            <!-- Cas par défaut (Employé lambda) -->
                            <div class="bg-gray-100 rounded-xl p-6 border border-gray-200">
                                <p class="text-gray-500 text-sm">Aucune action de signature requise pour votre poste.</p>
                            </div>

                        @endif
                        
                    </div>

                    <!-- 3. MAIN SECTION : Graphe & Notifications -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <!-- A. GRAPHE DE SUIVI -->
                        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-bold text-slate-800">Flux de Création</h3>
                                
                                <!-- Select avec Livewire pour dynamiser la période -->
                                <select wire:model.live="chartPeriod" class="bg-slate-50 border-none text-xs rounded-md text-slate-600 py-1 px-3 focus:ring-0 cursor-pointer outline-none shadow-sm">
                                    <option value="7_jours">7 derniers jours</option>
                                    <option value="ce_mois">Ce mois</option>
                                    <!-- <option value="cette_annee">Cette année</option> -->
                                </select>
                            </div>
                            
                            <!-- Zone du Graphique -->
                            <!-- On utilise wire:ignore pour qu'Alpine/Livewire ne réinitialise pas le div brutalement -->
                            <div wire:ignore>
                                <div id="chart-timeline" class="h-80 w-full"></div>
                            </div>
                        </div>

                        <!-- B. ZONE DE NOTIFICATIONS & ACTIVITÉS (1/3 largeur) -->
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col h-full">
                            <!-- Header Notifs -->
                            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-slate-800">Notifications</h3>
                                <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-full">3 new</span>
                            </div>

                            <!-- Liste Scrollable -->
                            <div class="flex-1 overflow-y-auto max-h-[350px] p-2">
                                <ul class="space-y-1">
                                    <!-- Item 1 -->
                                    <li class="hover:bg-slate-50 p-3 rounded-lg transition-colors cursor-pointer group">
                                        <div class="flex items-start gap-3">
                                            <div class="bg-blue-100 text-blue-600 rounded-full p-2 mt-1 shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-800 group-hover:text-blue-600 transition">Nouveau mémo reçu</p>
                                                <p class="text-xs text-slate-500">Service Comptabilité - "Factures proforma"</p>
                                                <p class="text-[10px] text-slate-400 mt-1">Il y a 10 min</p>
                                            </div>
                                        </div>
                                    </li>

                                    <!-- Item 2 -->
                                    <li class="hover:bg-slate-50 p-3 rounded-lg transition-colors cursor-pointer group">
                                        <div class="flex items-start gap-3">
                                            <div class="bg-yellow-100 text-yellow-600 rounded-full p-2 mt-1 shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-800 group-hover:text-yellow-600 transition">Validation en attente</p>
                                                <p class="text-xs text-slate-500">Réf: #MEM-2023-089</p>
                                                <p class="text-[10px] text-slate-400 mt-1">Il y a 2h</p>
                                            </div>
                                        </div>
                                    </li>

                                    <!-- Item 3 -->
                                    <li class="hover:bg-slate-50 p-3 rounded-lg transition-colors cursor-pointer group">
                                        <div class="flex items-start gap-3">
                                            <div class="bg-green-100 text-green-600 rounded-full p-2 mt-1 shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-800 group-hover:text-green-600 transition">Mémo #402 Approuvé</p>
                                                <p class="text-xs text-slate-500">Par Directeur Général</p>
                                                <p class="text-[10px] text-slate-400 mt-1">Hier, 16:30</p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Footer Notifs -->
                            <div class="p-3 border-t border-slate-100 text-center">
                                <button class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline">Voir tout l'historique</button>
                            </div>
                        </div>
                    </div>

                    <!-- 4. SECTION TABLEAU RÉCENT (Style "Table Financière") -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Derniers Mouvements</h3>
                            <button class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg></button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead>
                                    <tr class="text-slate-500 border-b border-slate-100">
                                        <th class="px-6 py-3 font-medium">Référence</th>
                                        <th class="px-6 py-3 font-medium">Objet</th>
                                        <th class="px-6 py-3 font-medium">Statut</th>
                                        <th class="px-6 py-3 font-medium">Date</th>
                                        <th class="px-6 py-3 font-medium text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <!-- Row 1 -->
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="px-6 py-3 font-mono text-slate-600 font-bold">#REF-2023-001</td>
                                        <td class="px-6 py-3 text-slate-800 font-medium">Rapport Financier T3</td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Envoyé
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-slate-500">29 Nov 2025</td>
                                        <td class="px-6 py-3 text-right">
                                            <button class="text-slate-400 hover:text-blue-600 group-hover:opacity-100 opacity-0 transition-opacity">Voir</button>
                                        </td>
                                    </tr>
                                    <!-- Row 2 -->
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="px-6 py-3 font-mono text-slate-600 font-bold">#REF-2023-002</td>
                                        <td class="px-6 py-3 text-slate-800 font-medium">Demande de congés</td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-slate-500">30 Nov 2025</td>
                                        <td class="px-6 py-3 text-right">
                                            <button class="text-slate-400 hover:text-blue-600 group-hover:opacity-100 opacity-0 transition-opacity">Voir</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Modal (Tailwind CSS Corrigé) -->
                    @if($isOpen)
                        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            
                            <!-- L'arrière-plan sombre (Overlay) -->
                            <!-- On ajoute 'fixed inset-0' pour qu'il prenne tout l'écran -->
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                            <!-- Le conteneur de positionnement -->
                            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                    
                                    <!-- Le panneau du formulaire (La boite blanche) -->
                                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">
                                        
                                        <form wire:submit.prevent="save">
                                            <!-- Corps du modal -->
                                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                                <h3 class="text-lg font-semibold leading-6 text-gray-900 mb-4" id="modal-title">
                                                    Nouveau Memo
                                                </h3>
                                                
                                                <!-- Champ Object -->
                                                <div class="mb-4">
                                                    <label for="object" class="block text-sm font-medium text-gray-700 mb-1">Object</label>
                                                    <input type="text" wire:model="object" id="object" class="w-full rounded-md border-yellow-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 py-2 px-3 border text-gray-900">
                                                    @error('object') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                                </div>

                                                <!-- Champ concernet -->
                                                <div class="mb-4">
                                                    <label for="object" class="block text-sm font-medium text-gray-700 mb-1">Concerne</label>
                                                    <input type="text" wire:model="concern" id="concern" class="w-full rounded-md border-yellow-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 py-2 px-3 border text-gray-900">
                                                    @error('concern') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                                </div>


                                                <!-- Champ Contenu (Éditeur Riche) -->
                                                        <div class="mb-4">
                                                            <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Contenu</label>
                                                            
                                                            <div wire:ignore 
                                                                class="rounded-md shadow-sm"
                                                                x-data="{
                                                                    content: @entangle('content'),
                                                                    initQuill() {
                                                                        const quill = new Quill(this.$refs.quillEditor, {
                                                                            theme: 'snow',
                                                                            placeholder: 'Rédigez votre mémo ici...',
                                                                            modules: {
                                                                                toolbar: [
                                                                                    // GROUPE 1 : Titres et Polices
                                                                                    [{ 'header': [1, 2, 3, false] }], // H1, H2, H3, Normal

                                                                                    // GROUPE 2 : Formatage de base
                                                                                    ['bold', 'italic', 'underline', 'strike'], // Gras, Italique, Souligné, Barré
                                                                                    
                                                                                    // GROUPE 3 : Couleurs
                                                                                    [{ 'color': [] }, { 'background': [] }], // Couleur du texte & Surlignage

                                                                                    // GROUPE 4 : Listes et Indentation
                                                                                    [{ 'list': 'ordered'}, { 'list': 'bullet' }], 
                                                                                    [{ 'indent': '-1'}, { 'indent': '+1' }], // Diminuer/Augmenter le retrait

                                                                                    // GROUPE 5 : Alignement
                                                                                    [{ 'align': [] }], 

                                                                                    // GROUPE 6 : Insertion (Lien)
                                                                                    ['link'], // Outil pour insérer un lien hypertexte

                                                                                    // GROUPE 7 : Nettoyage
                                                                                    ['clean'] // Effacer le formatage
                                                                                ]
                                                                            }
                                                                        });

                                                                        // Charger le contenu initial s'il existe (édition)
                                                                        if (this.content) {
                                                                            quill.root.innerHTML = this.content;
                                                                        }

                                                                        // Synchroniser Quill vers Livewire quand on tape
                                                                        quill.on('text-change', () => {
                                                                            this.content = quill.root.innerHTML;
                                                                        });
                                                                    }
                                                                }"
                                                                x-init="initQuill()"
                                                            >
                                                                <!-- Le conteneur visuel de l'éditeur -->
                                                                <div class="bg-white border border-gray-300 rounded-md overflow-hidden focus-within:border-yellow-500 focus-within:ring-1 focus-within:ring-yellow-500 transition-all duration-200">
                                                                    <!-- La zone de saisie Quill -->
                                                                    <div x-ref="quillEditor" class="min-h-[150px] max-h-[300px] text-gray-800 text-base font-sans"></div>
                                                                </div>
                                                            </div>

                                                            @error('content') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                                        </div>
                                            </div>
                                            
                                            <!-- Pied du modal (Boutons) -->
                                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                                <button type="submit" class="inline-flex w-full justify-center rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 sm:ml-3 sm:w-auto">
                                                    Enregistrer
                                                </button>
                                                <button type="button" wire:click="closeModal" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                                    Annuler
                                                </button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
   


                </div>

                
                <!-- SCRIPT DU GRAPHIQUE -->
                <script>
                    document.addEventListener('livewire:initialized', () => {
                        // 1. Initialisation du graphique avec les données PHP initiales
                        var options = {
                            series: [{
                                name: 'Creer',
                                data: @json($chartSortants) // Données au chargement de la page
                            }],
                            chart: {
                                height: 320,
                                type: 'area',
                                toolbar: { show: false },
                                zoom: { enabled: false },
                                animations: {
                                    enabled: true, // Animation fluide lors du changement
                                    easing: 'easeinout',
                                    speed: 800,
                                }
                            },
                            colors: ['#d6da18c4'], // Votre couleur jaune
                            dataLabels: { enabled: false },
                            stroke: { curve: 'smooth', width: 2 },
                            xaxis: {
                                categories: @json($chartCategories),
                                axisBorder: { show: false },
                                axisTicks: { show: false }
                            },
                            yaxis: {
                                show: true,
                                tickAmount: 3,
                                labels: {
                                    formatter: function (val) {
                                        return val.toFixed(0);
                                    }
                                }
                            },
                            grid: {
                                borderColor: '#f1f5f9',
                                strokeDashArray: 4,
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.4,
                                    opacityTo: 0.05,
                                    stops: [0, 90, 100]
                                }
                            },
                            tooltip: {
                                theme: 'light',
                                y: {
                                    formatter: function (val) {
                                        return val + " mémos"
                                    }
                                }
                            }
                        };

                        // Création de l'instance
                        var chart = new ApexCharts(document.querySelector("#chart-timeline"), options);
                        chart.render();

                        // 2. Écouteur de l'événement Livewire pour la mise à jour dynamique
                        // L'événement récupère les données envoyées par $this->dispatch(...)
                        Livewire.on('update-chart', (data) => {
                            
                            // data est un tableau ou objet contenant les arguments nommés envoyés par PHP
                            // Livewire 3 envoie souvent data[0] ou l'objet directement selon la version.
                            // On s'assure de récupérer les bons champs.
                            
                            // Mise à jour de la courbe (Série)
                            chart.updateSeries([{
                                data: data.series
                            }]);
                            
                            // Mise à jour de l'axe X (Dates)
                            chart.updateOptions({
                                xaxis: {
                                    categories: data.categories
                                }
                            });
                        });
                    });
                </script>
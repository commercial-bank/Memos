<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{ activeTab: 'security' }">
    
    <!-- En-tête -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">Paramètres du compte</h2>
            <p class="mt-1 text-sm text-gray-500">Gérez vos préférences, la sécurité et les notifications.</p>
        </div>
    </div>

    <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
        
        <!-- MENU LATÉRAL (Navigation) -->
        <aside class="py-6 px-2 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3">
            <nav class="space-y-1">
                <!-- Onglet Sécurité -->
                <button 
                    @click="activeTab = 'security'"
                    :class="activeTab === 'security' ? 'bg-gray-50 text-yellow-600 hover:bg-white' : 'text-gray-900 hover:text-gray-900 hover:bg-gray-50'"
                    class="group rounded-md px-3 py-2 flex items-center text-sm font-medium w-full transition-colors duration-150 ease-in-out"
                >
                    <i :class="activeTab === 'security' ? 'text-yellow-500' : 'text-gray-400 group-hover:text-gray-500'" class="fas fa-shield-alt flex-shrink-0 -ml-1 mr-3 h-6 w-6 text-center pt-1"></i>
                    <span class="truncate">Sécurité & Connexion</span>
                </button>

                <!-- Onglet Notifications -->
                <button 
                    @click="activeTab = 'notifications'"
                    :class="activeTab === 'notifications' ? 'bg-gray-50 text-yellow-600 hover:bg-white' : 'text-gray-900 hover:text-gray-900 hover:bg-gray-50'"
                    class="group rounded-md px-3 py-2 flex items-center text-sm font-medium w-full transition-colors duration-150 ease-in-out"
                >
                    <i :class="activeTab === 'notifications' ? 'text-yellow-500' : 'text-gray-400 group-hover:text-gray-500'" class="far fa-bell flex-shrink-0 -ml-1 mr-3 h-6 w-6 text-center pt-1"></i>
                    <span class="truncate">Notifications</span>
                </button>

                <!-- Onglet Préférences -->
                <button 
                    @click="activeTab = 'preferences'"
                    :class="activeTab === 'preferences' ? 'bg-gray-50 text-yellow-600 hover:bg-white' : 'text-gray-900 hover:text-gray-900 hover:bg-gray-50'"
                    class="group rounded-md px-3 py-2 flex items-center text-sm font-medium w-full transition-colors duration-150 ease-in-out"
                >
                    <i :class="activeTab === 'preferences' ? 'text-yellow-500' : 'text-gray-400 group-hover:text-gray-500'" class="fas fa-sliders-h flex-shrink-0 -ml-1 mr-3 h-6 w-6 text-center pt-1"></i>
                    <span class="truncate">Préférences</span>
                </button>
            </nav>
        </aside>

        <!-- CONTENU CENTRAL -->
        <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">

            <!-- 1. CONTENU SÉCURITÉ -->
            <div x-show="activeTab === 'security'" x-transition.opacity class="space-y-6">
                
                <!-- Changement de mot de passe -->
                <div class="shadow sm:rounded-md sm:overflow-hidden">
                    <div class="bg-white py-6 px-4 space-y-6 sm:p-6">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Mot de passe</h3>
                            <p class="mt-1 text-sm text-gray-500">Mettez à jour votre mot de passe régulièrement pour sécuriser votre compte.</p>
                        </div>

                        <form wire:submit.prevent="updatePassword">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="current_password" class="block text-sm font-medium text-gray-700">Mot de passe actuel</label>
                                    <input type="password" wire:model="current_password" id="current_password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                    @error('current_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="password" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                                    <input type="password" wire:model="password" id="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer nouveau</label>
                                    <input type="password" wire:model="password_confirmation" id="password_confirmation" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <button type="submit" class="bg-yellow-500 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sessions actives (Fake Data pour l'exemple visuel) -->
                <div class="shadow sm:rounded-md sm:overflow-hidden">
                    <div class="bg-white py-6 px-4 space-y-6 sm:p-6">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Sessions Actives</h3>
                            <p class="mt-1 text-sm text-gray-500">Voici les appareils connectés à votre compte.</p>
                        </div>
                        <ul class="divide-y divide-gray-200 border-t border-b border-gray-200">
                            <li class="py-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-desktop text-gray-400 text-xl mr-4"></i>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Windows 10 - Chrome</p>
                                        <p class="text-xs text-green-600">Session actuelle • Yaoundé, CM</p>
                                    </div>
                                </div>
                            </li>
                            <li class="py-4 flex items-center justify-between opacity-60">
                                <div class="flex items-center">
                                    <i class="fas fa-mobile-alt text-gray-400 text-xl mr-5 ml-1"></i>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">iPhone 12 - Safari</p>
                                        <p class="text-xs text-gray-500">Il y a 3 jours • Douala, CM</p>
                                    </div>
                                </div>
                                <button class="text-xs text-red-600 hover:text-red-800">Révoquer</button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 2. CONTENU NOTIFICATIONS -->
            <div x-show="activeTab === 'notifications'" x-cloak class="shadow sm:rounded-md sm:overflow-hidden">
                <div class="bg-white py-6 px-4 space-y-6 sm:p-6">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Préférences de Notification</h3>
                        <p class="mt-1 text-sm text-gray-500">Choisissez comment nous vous contactons.</p>
                    </div>
                    
                    <fieldset>
                        <legend class="text-base font-medium text-gray-900">Par Email</legend>
                        <div class="mt-4 space-y-4">
                            
                            <!-- Toggle Item 1 -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="comments" name="comments" type="checkbox" checked class="focus:ring-yellow-500 h-4 w-4 text-yellow-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="comments" class="font-medium text-gray-700">Validations RH</label>
                                    <p class="text-gray-500">Recevoir un email quand une demande de congé est traitée.</p>
                                </div>
                            </div>

                            <!-- Toggle Item 2 -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="candidates" name="candidates" type="checkbox" class="focus:ring-yellow-500 h-4 w-4 text-yellow-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="candidates" class="font-medium text-gray-700">Mises à jour système</label>
                                    <p class="text-gray-500">Être notifié lors des maintenances de l'application.</p>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    
                    <div class="bg-gray-50 -mx-6 -mb-6 px-4 py-3 text-right sm:px-6 mt-4">
                        <button type="button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            Sauvegarder les préférences
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. CONTENU PRÉFÉRENCES -->
            <div x-show="activeTab === 'preferences'" x-cloak class="shadow sm:rounded-md sm:overflow-hidden">
                <div class="bg-white py-6 px-4 space-y-6 sm:p-6">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Affichage & Langue</h3>
                        <p class="mt-1 text-sm text-gray-500">Personnalisez l'apparence de l'application.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="language" class="block text-sm font-medium text-gray-700">Langue</label>
                            <select id="language" name="language" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                <option>Français (France)</option>
                                <option>English (United States)</option>
                            </select>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="timezone" class="block text-sm font-medium text-gray-700">Fuseau Horaire</label>
                            <select id="timezone" name="timezone" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                <option>GMT +01:00 (Douala, Paris)</option>
                                <option>GMT +00:00 (London)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
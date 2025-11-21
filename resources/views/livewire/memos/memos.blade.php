<div class="p-6 bg-gray-100 min-h-screen"> 
    <h1 class="text-3xl font-bold mb-4">MEMO RANDUMS</h1>
    <p class="text-gray-600 mb-6">Manage your memo randums.</p>

    <div class="flex space-x-4 mb-6 border-b border-gray-200">
        <button
            class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'incoming' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
            wire:click="selectTab('incoming')"
        >
            Mémos Entrants
            
            <span class="ml-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full">3</span>
        </button>

        <button
            class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'drafted' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
            wire:click="selectTab('drafted')"
        >
            Mémos Brouillons

            <span class="ml-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{$countB}}</span>
        </button>

        <button
            class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'document' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
            wire:click="selectTab('document')"
        >
            Mémos Documents

            <span class="ml-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full">2</span>
        </button>

        <button
            class="pb-3 px-4 text-lg font-medium {{ $activeTab === 'sent' ? 'border-b-2 border-yellow-500 text-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
            wire:click="selectTab('sent')"
        >
            Mémos Envoyés
            <span class="ml-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full">2</span>
        </button>
    </div>

    <div class="flex justify-end mb-4 space-x-2">
        <button
            class="bg-yellow-400 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded flex items-center"
           command="show-modal" commandfor="dialog2" {{-- Appel à la méthode pour ouvrir le modal --}}
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            New Mémo
        </button>
        <button class="border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-2 px-4 rounded">
            Filter
        </button>
    </div>

    {{-- Contenu des onglets --}}
    <div class="mt-8">
        @if ($activeTab === 'incoming')
            <livewire:memos.incoming-memos />
        @elseif ($activeTab === 'drafted')
            tytytyty
        @elseif ($activeTab === 'document')
            tytytydoc
        @elseif ($activeTab === 'sent')
           tytyty
        @endif
    </div>


    {{-- Le modal pour le formulaire de création de mémo --}}
  
                    <el-dialog>
                    <dialog id="dialog2" aria-labelledby="dialog-title" class="fixed inset-0 size-auto max-h-none max-w-none overflow-y-auto bg-transparent backdrop:bg-transparent">
                        <el-dialog-backdrop class="fixed inset-0 bg-gray-500/75 transition-opacity data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in"></el-dialog-backdrop>

                        <div tabindex="0" class="flex min-h-full items-end justify-center p-4 text-center focus:outline-none sm:items-center sm:p-0">
                            <el-dialog-panel class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg max-h-[85vh] overflow-y-auto">
                                <!-- J'ai ajouté 'max-h-[85vh]' (hauteur max) et 'overflow-y-auto' (scrollbar) ci-dessus -->

                                <form id="monFormulaire" method="post" action="{{route('memo.store')}}" class="max-w-2xl mx-auto p-4">
                                    @csrf
                                    <!-- OBJET -->
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="document-object">
                                            Objet
                                        </label>
                                        <input 
                                            type="text" 
                                            name="object"
                                            id="document-object" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                            placeholder="Entrez l'objet ici..." required
                                        >
                                    </div>

                                    <!-- TYPE MEMO -->
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="document-type_memo">
                                            Type_memo
                                        </label>
                                        <select name="type_memo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" id="document-type_memo" required>
                                            <option class="focus:ring-yellow-500" value="Memo De Projet">Memo De Projet</option>
                                            <option value="Autre Memo">Autre Memo</option>
                                        </select>
                                    </div>

                                    <!-- CONTENU -->
                                    <div class="mb-6">
                                        <label class="block text-gray-700 text-sm font-bold mb-2">
                                            Contenu
                                        </label>

                                        <!-- Conteneur de l'éditeur "Style Word" -->
                                        <div class="border border-gray-300 rounded-md bg-white shadow-sm overflow-hidden">
                                            
                                            <!-- BARRE D'OUTILS -->
                                            <div class="flex flex-wrap items-center gap-1 p-2 bg-gray-50 border-b border-gray-300 select-none">
                                                <!-- Gras -->
                                                <button type="button" onclick="execCmd('bold')" class="p-1.5 text-gray-600 rounded hover:bg-gray-200 hover:text-black transition-colors" title="Gras">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6V4zm0 8h9a4 4 0 014 4 4 4 0 01-4 4H6v-8z"></path></svg>
                                                </button>
                                                <!-- Italique -->
                                                <button type="button" onclick="execCmd('italic')" class="p-1.5 text-gray-600 rounded hover:bg-gray-200 hover:text-black transition-colors" title="Italique">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                                </button>
                                                <!-- Souligné -->
                                                <button type="button" onclick="execCmd('underline')" class="p-1.5 text-gray-600 rounded hover:bg-gray-200 hover:text-black transition-colors" title="Souligné">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 19h12M8 5v10a4 4 0 108 0V5"></path></svg>
                                                </button>
                                                <div class="w-px h-6 bg-gray-300 mx-1"></div>
                                                <!-- Listes -->
                                                <button type="button" onclick="execCmd('insertUnorderedList')" class="p-1.5 text-gray-600 rounded hover:bg-gray-200 hover:text-black transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                                </button>
                                                <button type="button" onclick="execCmd('insertOrderedList')" class="p-1.5 text-gray-600 rounded hover:bg-gray-200 hover:text-black transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h12M7 13h12M7 19h12M3 7h.01M3 13h.01M3 19h.01"></path></svg>
                                                </button>
                                                <div class="w-px h-6 bg-gray-300 mx-1"></div>
                                                <!-- Alignement -->
                                                <button type="button" onclick="execCmd('justifyLeft')" class="p-1.5 text-gray-600 rounded hover:bg-gray-200 hover:text-black transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16"></path></svg>
                                                </button>
                                                <button type="button" onclick="execCmd('justifyCenter')" class="p-1.5 text-gray-600 rounded hover:bg-gray-200 hover:text-black transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M4 18h16"></path></svg>
                                                </button>
                                            </div>

                                            <!-- ZONE D'EDITION (Corrigée : elle est maintenant DANS le cadre) -->

                                            <textarea name="content" id="" class="w-full p-4 min-h-[200px] max-h-[400px] overflow-y-auto outline-none prose max-w-none text-gray-800 bg-white" contenteditable="true" required>

                                            </textarea>
                                           

                                            <!-- Barre de statut -->
                                            <div class="bg-gray-50 border-t border-gray-200 px-3 py-1 text-xs text-gray-500 flex justify-end">
                                                Mode édition
                                            </div>
                                        </div>
                                    </div>

                                    <!-- INPUT CACHÉ OBLIGATOIRE POUR ENVOYER EN BD -->
                                    

                                    <!-- BOUTONS D'ACTION -->
                                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 mt-4">
                                        <button type="button" command="close" commandfor="dialog2" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                            Annuler
                                        </button>
                                    
                                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Enregistrer
                                        </button>
                                    </div>

                                </form>
                            </el-dialog-panel>


            </div>

          </dialog>
        </el-dialog>
    

</div>
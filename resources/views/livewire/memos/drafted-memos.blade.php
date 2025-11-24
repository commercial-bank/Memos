{{-- Encapsulez tout dans un seul div parent --}}

<div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">


        @foreach ($writtenMemo as $memo)



                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 flex flex-col justify-between relative overflow-hidden">
                      <!-- Votre design de papier mémo et info expéditeur/auteur -->
                      <div class="absolute top-0 right-0 w-24 h-24 bg-blue-100 opacity-50 transform rotate-45 translate-x-12 -translate-y-12 flex items-center justify-center">
                          <svg class="w-12 h-12 text-blue-400 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                      </div>

                      <div>
                          <div class="flex items-center text-gray-700 mb-2">
                              <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                              <span class="text-sm font-medium">From: <span class="font-semibold">{{ $memo->user->entity }}</span></span>
                          </div>

                          <div class="flex items-center text-gray-700 mb-2">
                              <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                              <span class="text-sm font-medium">Author: <span class="font-semibold">{{ $memo->user->first_name }} {{ $memo->user->last_name }} {{$memo->id}}</span></span>
                          </div>

                          <h3 class="text-lg font-semibold text-gray-800 mt-4 mb-2 truncate">{{ $memo->object }}</h3>
                      </div>

                        <div class="flex justify-between items-center mt-4">
                          <div class="flex items-center text-gray-500 text-sm">
                              <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                              Date: {{ $memo->created_at->format('d/m/Y à H:i') }}
                          </div>

                          <!-- Boutons d'action -->
                          <div class="flex space-x-2">
                             
                              <button wire:click="viewWritten({{ $memo->id }})"   class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition duration-150 ease-in-out" title="Voir les détails">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                              </button>
                              <button wire:click="editWritten({{ $memo->id }})" class="p-2 rounded-full bg-yellow-100 text-yellow-600 hover:bg-yellow-200 transition duration-150 ease-in-out" title="Modifier">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                              </button>
                              <button wire:click="assignWritten({{ $memo->id }})" class="p-2 rounded-full bg-purple-100 text-purple-600 hover:bg-purple-200 transition duration-150 ease-in-out" title="Attribuer / Assigner">
                                    <!-- Icone: User Plus (Ajouter/Assigner une personne) -->
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                              </button>
                              <button wire:click="deleteWritten({{ $memo->id }})" class="p-2 rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition duration-150 ease-in-out" title="Rejeter">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                              </button>

                          </div>
                  </div>
            </div>

      
        @endforeach
        
        @if($isOpen)
            <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                
                <!-- 1. OVERLAY (Fond sombre) -->
                <div 
                    wire:click="closeModal" 
                    class="fixed inset-0 bg-gray-900 bg-opacity-90 transition-opacity backdrop-blur-sm cursor-pointer"
                ></div>

                <!-- 2. BARRE D'OUTILS FLOTTANTE (Fixe à l'écran) -->
                <!-- Je l'ai sortie du scroll pour qu'elle soit toujours visible en haut -->
                <div class="fixed top-0 left-0 w-full z-50 pointer-events-none p-4 flex justify-between items-start print:hidden">
                    
                    <!-- Bouton Retour -->
                    <button wire:click="closeModal" class="pointer-events-auto bg-gray-800 text-white hover:bg-gray-700 px-6 py-2 rounded-full shadow-xl font-bold flex items-center gap-2 transition transform hover:scale-105 border border-gray-600">
                        <span>&larr; Retour</span>
                    </button>

                    <!-- Bouton Imprimer -->
                    <button onclick="window.print()" class="pointer-events-auto bg-white text-gray-900 hover:bg-gray-100 px-6 py-2 rounded-full shadow-xl font-bold flex items-center gap-2 transition transform hover:scale-105 border border-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span>Imprimer</span>
                    </button>
                </div>

                <!-- 3. CONTENEUR DE LA FEUILLE (Scrollable) -->
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto pt-20 pb-10"> <!-- pt-20 pour ne pas cacher le haut de la feuille sous les boutons -->
                    <div class="flex min-h-full items-start justify-center p-4 text-center sm:p-0">
                        
                        <!-- Wrapper de la feuille (J'ai retiré 'transform') -->
                        <div class="relative flex flex-col items-center font-sans w-full max-w-[210mm]">

                            <!-- FEUILLE A4 -->
                            <div class="bg-white w-[210mm] min-h-[297mm] shadow-2xl p-[10mm] text-black text-[13px] leading-snug relative text-left mx-auto">

                                <!-- LE GRAND CADRE DORÉ -->
                                <div class="border-[3px] border-[#D4AF37] rounded-tr-[60px] rounded-bl-[60px] p-8 h-full flex flex-col justify-between relative min-h-[calc(297mm-20mm)]">

                                    <!-- EN-TÊTE -->
                                    <div class="flex flex-col items-center justify-center mb-6 text-center">
                                        <div class="mb-2">
                                            <div class="w-16 h-16 flex items-center justify-center mx-auto mb-1">
                                                <img src="{{ asset('images/log.jpg') }}" alt="logo" class="w-full h-full object-contain">
                                            </div>
                                        </div>

                                        <div class="font-bold text-xl tracking-tight text-gray-900 font-sans">CommercialBank</div>
                                        <div class="text-[9px] text-gray-600 uppercase tracking-widest mb-4">Let's build the future</div>
                                        
                                        <h2 class="font-bold text-xs uppercase text-gray-800">{{ $user_entity ?? 'DIRECTION' }}</h2>
                                        <h1 class="font-extrabold text-2xl uppercase mt-2 italic border-b-2 border-black pb-1 px-4 inline-block">Memorandum</h1>
                                    </div>

                                    <!-- TABLEAU -->
                                    <div class="mb-6 text-sm w-full">
                                        <style>
                                            .checkbox-square { display: inline-block; width: 12px; height: 12px; border: 1px solid black; margin-right: 6px; vertical-align: middle; }
                                        </style>
                                        
                                        <table class="w-full border-collapse border border-black text-[13px] font-sans text-black">
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold w-[35%] align-top">Date : {{ $date }}</td>
                                                <td class="border border-black p-1 pl-2 w-[30%]"><span class="checkbox-square"></span> Faire le nécessaire</td>
                                                <td class="border border-black p-1 text-center font-bold w-[35%] bg-gray-50">Toutes les Directions</td>
                                            </tr>
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold align-top">N° : 298/DGR/SDGR/WT</td>
                                                <td class="border border-black p-1 pl-2"><span class="checkbox-square"></span> Prendre connaissance</td>
                                                <td class="border border-black p-1 text-center font-bold">Direction Générale</td>
                                            </tr>
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold align-top">Emetteur : {{ $user_first_name }} {{ $user_last_name }}</td>
                                                <td class="border border-black p-1 pl-2"><span class="checkbox-square"></span> Prendre position</td>
                                                <td class="border border-black p-1">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td class="border border-black p-1 pl-2 font-bold align-top">Service : {{ $user_service }}</td>
                                                <td class="border border-black p-1 pl-2"><span class="checkbox-square"></span> Décider</td>
                                                <td class="border border-black p-1">&nbsp;</td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- CORPS DU TEXTE -->
                                    <div class="mb-4 flex-grow px-2">
                                        <div class="mb-6">
                                            <p class="mb-1"><span class="font-bold text-[15px] underline">Objet :</span> <span class="uppercase font-bold">{{ $object }} </span></p>
                                        </div>

                                        <div class="text-justify space-y-3 text-[14px] leading-relaxed font-serif text-gray-900">
                                            {{$content}}
                                        </div>
                                    </div>

                                    <!-- PIED DE PAGE -->
                                    <div class="mt-8 pt-4">
                                        <p class="mb-8 text-sm italic">Nous restons à votre disposition pour tout besoin d'accompagnement.</p>

                                        <div class="flex justify-between items-end px-4 mb-2">
                                            <div class="text-center w-1/3">
                                                <div class="font-bold text-black text-sm mb-12 uppercase">Wilfried Lionel TALOM</div>
                                                <div class="text-[10px] text-gray-700 leading-tight border-t border-gray-400 pt-1">Chef de Département Analyse &<br>Traitement des Risques</div>
                                            </div>

                                            <div class="text-center w-1/3">
                                                <div class="font-bold text-black text-sm mb-12 uppercase">Arsène TAGU TCHOUA</div>
                                                <div class="text-[10px] text-gray-700 leading-tight border-t border-gray-400 pt-1">Directeur de la Gestion des Risques</div>
                                            </div>
                                        </div>

                                        <div class="text-right text-[10px] text-gray-500 italic mt-2">FOR-ME-07-V1</div>
                                    </div>

                                </div> 
                            </div>
                            
                            <!-- Bouton Fermer Bas (Optionnel) -->
                            <div class="mt-8 mb-4 print:hidden">
                                <button wire:click="closeModal" class="text-white hover:text-gray-300 underline">Fermer</button>
                            </div>

                        </div>
                
                    </div>
                </div>
            </div>
        @endif



         @if($isOpen2)
            <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
                <!-- L'arrière-plan sombre (Overlay) -->
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

                <!-- Le conteneur de positionnement (Scrollable) -->
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    
                    <!-- CORRECTION 1 : 'items-center' pour centrer verticalement -->
                    <!-- 'min-h-full' assure que le conteneur prend toute la hauteur pour permettre le centrage -->
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        
                        <!-- Le panneau du contenu -->
                        <!-- CORRECTION 2 : J'ai retiré 'mt-1000' qui cassait tout -->
                        <!-- J'ai gardé 'sm:my-8' pour une petite marge en haut/bas sur ordinateur -->
                        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">
                    
                            <form wire:submit.prevent="save">
                                <!-- Corps du modal -->
                                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">
                                            Nouveau Memo
                                        </h3>
                                        <!-- Bouton croix pour fermer (Optionnel mais recommandé) -->
                                        <button type="button" wire:click="closeModalDeux()" class="text-gray-400 hover:text-gray-500">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <!-- Champ Object -->
                                    <div class="mb-4">
                                        <label for="object" class="block text-sm font-medium text-gray-700 mb-1">Objet</label>
                                        <input type="text"  wire:model="object" id="object" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 py-2 px-3 text-gray-900">
                                        @error('object') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Champ Type_memo -->
                                    <div class="mb-4">
                                        <label for="type_memo" class="block text-sm font-medium text-gray-700 mb-1">Type De Memo</label>
                                        <select wire:model="type_memo" id="type_memo" class="w-full rounded-md border border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 py-2 px-3 text-gray-900 bg-white">
                                            <option value="Memo Simple">Memo Simple</option>
                                            <option value="Memo De Projet">Memo De Projet</option>
                                        </select>
                                        @error('type_memo') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Champ Contenu -->
                                    <div class="mb-4">
                                        <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Contenu</label>
                                        <!-- J'ai ajusté la hauteur min/max pour que ça reste joli centré -->
                                        <textarea wire:model="content" id="content" rows="4" class="w-full p-3 min-h-[150px] max-h-[300px] rounded-md border border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 outline-none text-gray-800 bg-white"></textarea>
                                        @error('content') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                
                                <!-- Pied du modal (Boutons) -->
                                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-100">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 sm:ml-3 sm:w-auto">
                                        Enregistrer
                                    </button>
                                    <button type="button" wire:click="closeModalDeux()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                        Annuler
                                    </button>
                                </div>
                            </form>

                        </div>
                        
                    </div>
                </div>
            </div>
         @endif



         @if($isOpen3)
            <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        
                        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-200">
                            
                            <form wire:submit.prevent="saveAssignments">
                                <!-- Header -->
                                <div class="bg-white px-4 py-5 sm:px-6 border-b border-gray-100 flex justify-between items-center">
                                    <div>
                                        <h3 class="text-lg font-semibold leading-6 text-gray-900">Attribuer les destinataires</h3>
                                        <p class="mt-1 text-sm text-gray-500">Cochez les entités concernées et définissez l'action requise.</p>
                                    </div>
                                    <button type="button" wire:click="closeModalTrois" class="text-gray-400 hover:text-gray-500">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>

                                <!-- Body (Liste avec scroll si trop longue) -->
                                <div class="bg-white px-4 py-4 sm:p-6 max-h-[60vh] overflow-y-auto">
                                    
                                    <div class="space-y-2">
                                        @foreach($allEntities as $entity)
                                        <!-- Ligne pour une Entité -->
                                        <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors {{ $selections[$entity->id]['checked'] ? 'bg-blue-50 border-blue-200' : '' }}">
                                            
                                            <!-- Checkbox + Nom -->
                                            <div class="flex items-center space-x-3 flex-grow">
                                                <input 
                                                    type="checkbox" 
                                                    wire:model.live="selections.{{ $entity->id }}.checked" 
                                                    id="entity_{{ $entity->id }}"
                                                    class="h-5 w-5 rounded border-gray-300 text-yellow-500 focus:ring-yellow-500 cursor-pointer"
                                                >
                                                <label for="entity_{{ $entity->id }}" class="text-sm font-medium text-gray-900 cursor-pointer w-full">
                                                    {{ $entity->name }} 
                                                    @if($entity->acronym) <span class="text-gray-500 text-xs">({{ $entity->acronym }})</span> @endif
                                                </label>
                                            </div>

                                            <!-- Sélecteur d'Action (Visible seulement si coché) -->
                                            <div class="w-1/3">
                                                @if(!empty($selections[$entity->id]['checked']))
                                                    <select 
                                                        wire:model="selections.{{ $entity->id }}.action" 
                                                        class="block w-full rounded-md border-0 py-1.5 pl-3 pr-8 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-yellow-500 sm:text-xs sm:leading-6 bg-white shadow-sm"
                                                    >
                                                        <option value="Faire le nécessaire">Faire le nécessaire</option>
                                                        <option value="Prendre connaissance">Prendre connaissance</option>
                                                        <option value="Prendre position">Prendre position</option>
                                                        <option value="Décider">Décider</option>
                                                    </select>
                                                @else
                                                    <span class="text-xs text-gray-400 italic">Non sélectionné</span>
                                                @endif
                                            </div>

                                        </div>
                                        @endforeach
                                    </div>

                                </div>

                                <!-- Footer -->
                                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-100">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-md bg-yellow-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 sm:ml-3 sm:w-auto">
                                        Valider les attributions
                                    </button>
                                    <button type="button" wire:click="closeModalTrois" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                        Annuler
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif


       @if($isOpen4)
            <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">

                <!-- L'arrière-plan sombre -->
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

                <!-- Le conteneur -->
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        
                        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-lg border border-gray-200">
                    
                            <form wire:submit.prevent="del">
                                
                                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <!-- Icone Attention -->
                                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                            <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Supprimer le brouillon</h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">
                                                    Voulez-vous vraiment supprimer ce brouillon ? Cette action est irréversible.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Pied du modal (Boutons) -->
                                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                                        Supprimer définitivement
                                    </button>
                                    <button type="button" wire:click="closeModalQuatre()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
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
</div>



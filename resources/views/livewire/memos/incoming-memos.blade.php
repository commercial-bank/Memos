{{-- Encapsulez tout dans un seul div parent --}}











<div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">


        @foreach ($memos as $memo)



                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 flex flex-col justify-between relative overflow-hidden">
                      <!-- Votre design de papier mémo et info expéditeur/auteur -->
                      <div class="absolute top-0 right-0 w-24 h-24 bg-blue-100 opacity-50 transform rotate-45 translate-x-12 -translate-y-12 flex items-center justify-center">
                          <svg class="w-12 h-12 text-blue-400 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                      </div>

                      <div>
                          <div class="flex items-center text-gray-700 mb-2">
                              <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                              <span class="text-sm font-medium">From: <span class="font-semibold">{{ $memo->from }}</span></span>
                          </div>

                          <div class="flex items-center text-gray-700 mb-2">
                              <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                              <span class="text-sm font-medium">Author: <span class="font-semibold">{{ $memo->author }}</span></span>
                          </div>

                          <h3 class="text-lg font-semibold text-gray-800 mt-4 mb-2 truncate">{{ $memo->subject }}</h3>
                      </div>

                        <div class="flex justify-between items-center mt-4">
                          <div class="flex items-center text-gray-500 text-sm">
                              <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                              Date: {{ $memo->date }}
                          </div>

                          <!-- Boutons d'action -->
                          <div class="flex space-x-2">
                              {{-- Appelez la nouvelle méthode et passez l'ID du mémo --}}
                              <button  command="show-modal" commandfor="dialog" class="p-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition duration-150 ease-in-out" title="Voir les détails">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                              </button>
                              {{-- Pour les autres boutons, vous devrez créer des événements similaires ou des propriétés pour leurs modals respectifs --}}
                              <button wire:click="openReplyMemoModal({{ $memo->id }})" class="p-2 rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition duration-150 ease-in-out" title="Répondre">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                              </button>
                              <button wire:click="openEditMemoModal({{ $memo->id }})" class="p-2 rounded-full bg-yellow-100 text-yellow-600 hover:bg-yellow-200 transition duration-150 ease-in-out" title="Modifier">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                              </button>
                              <button wire:click="openSendMemoModal({{ $memo->id }})" class="p-2 rounded-full bg-purple-100 text-purple-600 hover:bg-purple-200 transition duration-150 ease-in-out" title="Envoyer">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                              </button>
                              <button wire:click="openRejectMemoModal({{ $memo->id }})" class="p-2 rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition duration-150 ease-in-out" title="Rejeter">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                              </button>
                          </div>
                  </div>
            </div>

      
        @endforeach
        

  </div>
</div>



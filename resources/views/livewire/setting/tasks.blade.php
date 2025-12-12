<div class="h-full flex flex-col p-4 bg-gray-50">
    
    <!-- HEADER : Titre + Formulaire d'ajout -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Mes Tâches</h1>
            <p class="text-sm text-gray-500">Gérez vos priorités et vos mémos en attente.</p>
        </div>

        <!-- Formulaire rapide -->
        <form wire:submit.prevent="saveTask" class="flex gap-2 w-full md:w-auto">
            <input type="text" wire:model="title" placeholder="Nouvelle tâche..." 
                   class="border-gray-300 rounded-lg shadow-sm focus:border-yellow-500 focus:ring-yellow-500 text-sm py-2 px-4 w-64">
            
            <select wire:model="priority" class="border-gray-300 rounded-lg shadow-sm text-sm py-2 px-2">
                <option value="normal">Normal</option>
                <option value="urgent">Urgent</option>
                <option value="low">Faible</option>
            </select>
            
            <button type="submit" class="bg-[#daaf2c] hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow-sm transition">
                <i class="fas fa-plus"></i>
            </button>
        </form>
    </div>

    <!-- KANBAN BOARD -->
    <div class="flex-1 overflow-x-auto">
        <div class="flex gap-6 h-full min-w-[900px]">

            <!-- COLONNE 1 : À FAIRE -->
            <div class="w-1/3 flex flex-col bg-gray-100 rounded-xl p-3"
                 ondrop="drop(event, 'todo')" ondragover="allowDrop(event)">
                
                <div class="flex justify-between items-center mb-3 px-1">
                    <h3 class="font-bold text-gray-600">À faire</h3>
                    <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full">{{ $board['todo']->count() }}</span>
                </div>

                <div class="space-y-3 overflow-y-auto max-h-[600px] pr-1">
                    @foreach($board['todo'] as $task)
                        <div draggable="true" ondragstart="drag(event, '{{ $task->id }}')"
                             class="bg-white p-3 rounded-lg shadow-sm border border-gray-200 cursor-move hover:shadow-md transition group">
                            
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded
                                    {{ $task->priority == 'urgent' ? 'bg-red-100 text-red-600' : 'bg-blue-50 text-blue-600' }}">
                                    {{ $task->priority }}
                                </span>
                                <button wire:click="deleteTask({{ $task->id }})" class="text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <p class="text-sm font-medium text-gray-800">{{ $task->title }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- COLONNE 2 : EN COURS (+ MÉMOS) -->
            <div class="w-1/3 flex flex-col bg-yellow-50 rounded-xl p-3 border border-yellow-100"
                 ondrop="drop(event, 'in_progress')" ondragover="allowDrop(event)">
                
                <div class="flex justify-between items-center mb-3 px-1">
                    <h3 class="font-bold text-yellow-800">En cours & Mémos</h3>
                    <span class="bg-yellow-200 text-yellow-800 text-xs px-2 py-1 rounded-full">{{ $board['in_progress']->count() }}</span>
                </div>

                <div class="space-y-3 overflow-y-auto max-h-[600px] pr-1">
                    @foreach($board['in_progress'] as $item)
                        
                        <!-- CAS SPÉCIAL : C'est un MÉMO -->
                        @if(isset($item->is_memo))
                            <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-[#daaf2c] relative overflow-hidden">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="bg-[#daaf2c] text-white text-[10px] font-bold px-1.5 py-0.5 rounded">MÉMO</span>
                                    <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m') }}</span>
                                </div>
                                <p class="text-sm font-bold text-gray-800 mb-1">{{ $item->title }}</p>
                                <p class="text-xs text-gray-500 mb-2">Réf: {{ $item->reference }}</p>
                                
                                <!-- Bouton Action (Lien vers le détail du mémo à prévoir) -->
                                <button class="w-full py-1 text-xs font-bold text-[#daaf2c] border border-[#daaf2c] rounded hover:bg-[#daaf2c] hover:text-white transition">
                                    Traiter le document
                                </button>
                            </div>

                        <!-- CAS NORMAL : C'est une TÂCHE -->
                        @else
                            <div draggable="true" ondragstart="drag(event, '{{ $item->id }}')"
                                 class="bg-white p-3 rounded-lg shadow-sm border border-gray-200 cursor-move hover:shadow-md transition flex flex-col gap-2">
                                
                                <span class="w-fit text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-gray-100 text-gray-600">
                                    {{ $item->priority }}
                                </span>
                                <p class="text-sm font-medium text-gray-800">{{ $item->title }}</p>
                                
                                <!-- Bouton rapide pour terminer -->
                                <button wire:click="updateStatus({{ $item->id }}, 'done')" class="self-end text-xs text-green-600 hover:underline">
                                    <i class="fas fa-check"></i> Terminer
                                </button>
                            </div>
                        @endif

                    @endforeach
                </div>
            </div>

            <!-- COLONNE 3 : TERMINÉ -->
            <div class="w-1/3 flex flex-col bg-gray-100 rounded-xl p-3"
                 ondrop="drop(event, 'done')" ondragover="allowDrop(event)">
                
                <div class="flex justify-between items-center mb-3 px-1">
                    <h3 class="font-bold text-gray-600">Terminé</h3>
                    <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full">{{ $board['done']->count() }}</span>
                </div>

                <div class="space-y-3 overflow-y-auto max-h-[600px] pr-1">
                    @foreach($board['done'] as $task)
                        <div draggable="true" ondragstart="drag(event, '{{ $task->id }}')"
                             class="bg-white opacity-60 p-3 rounded-lg shadow-sm border border-gray-200 cursor-move">
                            <p class="text-sm text-gray-500 line-through">{{ $task->title }}</p>
                            <div class="text-right mt-1">
                                <span class="text-[10px] text-green-600 font-bold">Fait</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

<!-- SCRIPTS JAVASCRIPT SIMPLES POUR LE DRAG & DROP -->
<script>
    function allowDrop(ev) {
        ev.preventDefault(); // Nécessaire pour autoriser le drop
    }

    function drag(ev, id) {
        // On attache l'ID de la tâche à l'événement
        ev.dataTransfer.setData("taskId", id);
    }

    function drop(ev, newStatus) {
        ev.preventDefault();
        var taskId = ev.dataTransfer.getData("taskId");
        
        // Si on a un ID, on appelle la fonction Livewire updateStatus
        if (taskId) {
            @this.updateStatus(taskId, newStatus);
        }
    }
</script>
<div class="flex h-full gap-6">
    
    <!-- Sidebar -->
    <div class="w-72 hidden lg:flex flex-col gap-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <!-- Bouton Créatif : Ajout Rapide -->
            <div class="dropdown w-full relative group">
                <button class="w-full py-2.5 rounded-lg font-bold text-black mb-6 shadow-sm hover:shadow-md transition-all flex items-center justify-center gap-2"
                        style="background-color: #daaf2c;">
                    <i class="fas fa-plus"></i> Nouveau
                </button>
                <!-- Menu déroulant simple au survol (ou click avec alpine) -->
                <div class="hidden group-hover:block absolute top-10 left-0 w-full bg-white border border-gray-100 shadow-lg rounded-lg z-10">
                    <a href="#" class="block px-4 py-2 hover:bg-gray-50 text-sm">Déclarer un Intérim</a>
                    <a href="#" class="block px-4 py-2 hover:bg-gray-50 text-sm">Rédiger un Mémo</a>
                </div>
            </div>
            
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Affichage</h3>
                
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" wire:model.live="filters.interims" class="rounded text-[#daaf2c] focus:ring-[#daaf2c]">
                    <span class="w-3 h-3 rounded-full bg-orange-400 group-hover:ring-2 ring-orange-200"></span>
                    <span class="text-sm text-gray-600">Intérims & Absences</span>
                </label>
                
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" wire:model.live="filters.memos" class="rounded text-[#daaf2c] focus:ring-[#daaf2c]">
                    <span class="w-3 h-3 rounded-full bg-blue-500 group-hover:ring-2 ring-blue-200"></span>
                    <span class="text-sm text-gray-600">Mémos Initiés</span>
                </label>
                
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" wire:model.live="filters.courriers" class="rounded text-[#daaf2c] focus:ring-[#daaf2c]">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 group-hover:ring-2 ring-emerald-200"></span>
                    <span class="text-sm text-gray-600">Courriers Enregistrés</span>
                </label>
            </div>
        </div>

        <!-- KPI Rapides -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex-1">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Activité du mois</h3>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-blue-50 p-3 rounded-lg text-center">
                    <span class="block text-2xl font-bold text-blue-600">
                        {{ collect($data['days'])->pluck('events')->flatten(1)->where('type', 'memo')->count() }}
                    </span>
                    <span class="text-xs text-blue-400 uppercase">Mémos</span>
                </div>
                <div class="bg-orange-50 p-3 rounded-lg text-center">
                    <span class="block text-2xl font-bold text-orange-600">
                        {{ collect($data['days'])->pluck('events')->flatten(1)->where('type', 'interim')->unique('id')->count() }}
                    </span>
                    <span class="text-xs text-orange-400 uppercase">Intérims</span>
                </div>
            </div>
            
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Prochains événements</h4>
            <div class="space-y-3 overflow-y-auto max-h-[200px] pr-1">
                @php
                    $upcoming = collect($data['days'])
                        ->where('date', '>=', now()->format('Y-m-d'))
                        ->pluck('events')
                        ->flatten(1)
                        ->sortBy('date') // Note: les périodes n'ont pas de date unique ici, simplification
                        ->take(5);
                @endphp

                @forelse($upcoming as $event)
                    <div class="flex items-start gap-3 pb-3 border-b border-gray-50 last:border-0">
                        <div class="w-2 h-2 mt-1.5 rounded-full flex-shrink-0
                            {{ $event['type'] === 'interim' ? 'bg-orange-400' : '' }}
                            {{ $event['type'] === 'memo' ? 'bg-blue-500' : '' }}
                            {{ $event['type'] === 'courrier' ? 'bg-emerald-500' : '' }}
                        "></div>
                        <div>
                            <p class="text-xs text-gray-500 font-semibold mb-0.5">
                                {{ isset($event['start']) ? \Carbon\Carbon::parse($event['start'])->format('d/m') : \Carbon\Carbon::parse($event['date'])->format('d/m') }}
                            </p>
                            <h4 class="text-sm font-medium text-gray-800 leading-tight">{{ $event['title'] }}</h4>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-gray-400 italic">Rien de prévu prochainement.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Grille Calendrier -->
    <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden relative">
        
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-white z-10">
            <h2 class="text-xl font-bold text-gray-800 capitalize flex items-center gap-2">
                <i class="far fa-calendar-alt text-[#daaf2c]"></i>
                {{ $data['monthName'] }} <span class="text-gray-400 font-normal">{{ $data['year'] }}</span>
            </h2>
            <div class="flex items-center bg-gray-50 rounded-lg p-1 border border-gray-200">
                <button wire:click="prevMonth" class="p-2 hover:bg-white hover:shadow-sm rounded-md transition-all text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button wire:click="goToToday" class="px-4 text-xs font-bold uppercase tracking-wider text-gray-600 hover:text-[#daaf2c]">
                    Aujourd'hui
                </button>
                <button wire:click="nextMonth" class="p-2 hover:bg-white hover:shadow-sm rounded-md transition-all text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>

        <!-- Jours Semaine -->
        <div class="grid grid-cols-7 border-b border-gray-100 bg-gray-50/50">
            @foreach(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $dayName)
                <div class="py-3 text-center text-xs font-bold text-gray-400 uppercase tracking-wider">{{ $dayName }}</div>
            @endforeach
        </div>

        <!-- Grille -->
        <div class="grid grid-cols-7 flex-1 auto-rows-fr overflow-y-auto">
            @foreach($data['days'] as $day)
                <div class="relative border-b border-r border-gray-100 p-1 min-h-[110px] transition-all hover:bg-gray-50 flex flex-col gap-1 group
                    {{ !$day['isCurrentMonth'] ? 'bg-gray-50/30' : '' }}
                    {{ $day['isToday'] ? 'bg-yellow-50/20' : '' }}">
                    
                    <!-- Date Header -->
                    <div class="flex justify-between items-start px-1">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold
                            {{ $day['isToday'] ? 'bg-[#daaf2c] text-black shadow-sm' : ($day['isCurrentMonth'] ? 'text-gray-700' : 'text-gray-300') }}">
                            {{ $day['day'] }}
                        </span>
                        <!-- Petit bouton + qui apparait au survol pour ajouter un event ce jour là -->
                        <button class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-[#daaf2c] transition-opacity">
                            <i class="fas fa-plus-circle text-xs"></i>
                        </button>
                    </div>

                    <!-- Liste Events -->
                    <div class="flex flex-col gap-1 mt-1 overflow-hidden">
                        @foreach($day['events'] as $event)
                            <div class="text-[10px] px-1.5 py-1 rounded border truncate cursor-pointer hover:scale-[1.02] transition-transform shadow-sm
                                {{ $event['type'] === 'memo' ? 'bg-blue-50 text-blue-700 border-blue-100 border-l-2 border-l-blue-500' : '' }}
                                {{ $event['type'] === 'interim' ? 'bg-orange-50 text-orange-700 border-orange-100 border-l-2 border-l-orange-400' : '' }}
                                {{ $event['type'] === 'courrier' ? 'bg-emerald-50 text-emerald-700 border-emerald-100 border-l-2 border-l-emerald-500' : '' }}
                            " title="{{ $event['title'] }}">
                                
                                @if($event['type'] === 'interim')
                                    <i class="fas fa-user-friends mr-1 opacity-50"></i>
                                @elseif($event['type'] === 'memo')
                                    <span class="font-bold mr-1">{{ $event['time'] }}</span>
                                @endif
                                {{ $event['title'] }}
                            </div>
                        @endforeach
                        
                        <!-- Si trop d'events -->
                        @if(count($day['events']) > 3)
                           <div class="text-[9px] text-gray-400 text-center hover:text-gray-600 cursor-pointer">+ {{ count($day['events']) - 3 }} autres</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Loading State -->
        <div wire:loading class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center z-50">
             <div class="flex flex-col items-center">
                <svg class="animate-spin h-8 w-8 text-[#daaf2c] mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs font-bold text-gray-500">Mise à jour du planning...</span>
             </div>
        </div>
    </div>
</div>
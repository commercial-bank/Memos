<div class="flex items-center justify-center min-h-[400px] bg-slate-50">

  <!-- Card Container -->
  <div class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
    
    <!-- Header -->
    <div class="p-6 border-b border-slate-100 flex justify-between items-start">
      <div class="flex gap-4 items-center">
        <!-- Icon container -->
        <div class="h-12 w-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
          </svg>
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-800">Courriers & Mémos</h3>
          <p class="text-sm text-slate-500">Module de gestion</p>
        </div>
      </div>
      <!-- Status Dot -->
      <span class="flex h-3 w-3">
        <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-green-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
      </span>
    </div>

    <!-- Content / Stats -->
    <div class="p-6 grid grid-cols-2 gap-4">
      <div class="bg-slate-50 p-3 rounded-lg text-center hover:bg-blue-50 transition-colors group cursor-pointer">
        <span class="block text-2xl font-bold text-slate-700 group-hover:text-blue-600">12</span>
        <span class="text-xs text-slate-500 font-medium uppercase">À traiter</span>
      </div>
      <div class="bg-slate-50 p-3 rounded-lg text-center hover:bg-blue-50 transition-colors group cursor-pointer">
        <span class="block text-2xl font-bold text-slate-700 group-hover:text-blue-600">5</span>
        <span class="text-xs text-slate-500 font-medium uppercase">Urgents</span>
      </div>
    </div>

    <!-- Quick Actions Footer -->
    <div class="px-6 pb-6 pt-2">
      <div class="flex gap-2 mb-4">
        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Activités récentes</div>
      </div>
      <!-- Mini list item -->
      <div class="flex items-center gap-3 mb-4">
        <div class="w-2 h-2 rounded-full bg-orange-400"></div>
        <p class="text-sm text-slate-600 truncate">Mémo: Réunion budgétaire...</p>
        <span class="ml-auto text-xs text-slate-400">14:30</span>
      </div>

      <button class="w-full py-2.5 px-4 bg-slate-900 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors shadow-lg shadow-slate-300/50 flex items-center justify-center gap-2">
        <span>Accéder au module</span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
        </svg>
      </button>
    </div>

  </div>
</div>
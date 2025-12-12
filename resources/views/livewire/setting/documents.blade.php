<div class="flex flex-col h-full">
    <!-- Toolbar de recherche -->
    <div class="flex items-center justify-between mb-6 bg-white p-3 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center text-sm text-gray-500 ml-2">
            <span class="hover:text-[#daaf2c] cursor-pointer">Documents</span>
            <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="font-bold text-gray-900">Modèles Administratifs</span>
        </div>
        <div class="flex gap-3">
             <div class="relative">
                <input type="text" placeholder="Rechercher..." class="pl-9 pr-4 py-2 bg-gray-50 border-none rounded-lg text-sm focus:ring-2 focus:ring-[#daaf2c] w-64 transition-all">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <button class="bg-black text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
                <i class="fas fa-cloud-upload-alt mr-2"></i> Importer
            </button>
        </div>
    </div>

    <!-- Section Dossiers -->
    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Dossiers</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-8">
        <!-- Folder Item -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-[#daaf2c] hover:shadow-md transition-all cursor-pointer flex flex-col items-center justify-center gap-2 group">
            <svg class="w-12 h-12 text-yellow-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
            <span class="font-medium text-gray-700 text-sm group-hover:text-black">RH & Paie</span>
            <span class="text-[10px] text-gray-400">12 éléments</span>
        </div>
        
        <!-- Folder Item -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-[#daaf2c] hover:shadow-md transition-all cursor-pointer flex flex-col items-center justify-center gap-2 group">
             <svg class="w-12 h-12 text-yellow-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
            <span class="font-medium text-gray-700 text-sm group-hover:text-black">Finance</span>
             <span class="text-[10px] text-gray-400">8 éléments</span>
        </div>

         <!-- Folder Item Add -->
        <div class="border-2 border-dashed border-gray-300 p-4 rounded-xl flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-[#daaf2c] hover:bg-yellow-50/50 transition-colors text-gray-400 hover:text-[#daaf2c]">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            <span class="font-bold text-xs">Nouveau dossier</span>
        </div>
    </div>

    <!-- Section Fichiers Récents (Tableau) -->
    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Fichiers Récents</h3>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="p-4">Nom</th>
                    <th class="p-4">Propriétaire</th>
                    <th class="p-4">Date</th>
                    <th class="p-4 text-right">Taille</th>
                    <th class="p-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                <tr class="hover:bg-gray-50 transition-colors group">
                    <td class="p-4 flex items-center gap-3">
                        <!-- Icone PDF -->
                        <div class="w-8 h-8 rounded bg-red-100 flex items-center justify-center text-red-600">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <span class="font-medium text-gray-800">Règlement_Intérieur_2025.pdf</span>
                    </td>
                    <td class="p-4 text-gray-500">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/user3.png') }}" class="w-5 h-5 rounded-full">
                            Admin
                        </div>
                    </td>
                    <td class="p-4 text-gray-500">10 Déc 2025</td>
                    <td class="p-4 text-right text-gray-500">2.4 MB</td>
                    <td class="p-4 text-right">
                        <button class="text-gray-400 hover:text-[#daaf2c] transition-colors"><i class="fas fa-download"></i></button>
                    </td>
                </tr>
                 <tr class="hover:bg-gray-50 transition-colors group">
                    <td class="p-4 flex items-center gap-3">
                        <!-- Icone Word -->
                        <div class="w-8 h-8 rounded bg-blue-100 flex items-center justify-center text-blue-600">
                            <i class="fas fa-file-word"></i>
                        </div>
                        <span class="font-medium text-gray-800">Modele_Memo_V2.docx</span>
                    </td>
                    <td class="p-4 text-gray-500">
                         <div class="flex items-center gap-2">
                            <img src="{{ asset('images/user3.png') }}" class="w-5 h-5 rounded-full">
                            Admin
                        </div>
                    </td>
                    <td class="p-4 text-gray-500">08 Déc 2025</td>
                    <td class="p-4 text-right text-gray-500">145 KB</td>
                    <td class="p-4 text-right">
                        <button class="text-gray-400 hover:text-[#daaf2c] transition-colors"><i class="fas fa-download"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
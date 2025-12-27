<div class="min-h-screen bg-gray-50 py-8 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if($isViewingPdf)
                    <!-- ========================================== -->
                    <!-- VUE 3 : APERÇU RÉEL PDF (DOMPDF)           -->
                    <!-- ========================================== -->
                    <div class="animate-fade-in">
                        <!-- BARRE D'ACTIONS (Header style original) -->
                        <div class="mb-8 bg-white border border-gray-100 rounded-xl shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-all duration-300">
                            <button wire:click="closePdfView" type="button" class="group flex items-center text-gray-500 hover:text-black transition-colors focus:outline-none">
                                <div class="mr-3 h-10 w-10 rounded-full bg-gray-100 group-hover:bg-[#daaf2c]/20 group-hover:text-[#daaf2c] flex items-center justify-center transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                </div>
                                <div class="flex flex-col items-start text-left">
                                    <span class="font-bold text-base text-black">Retour</span>
                                    <span class="text-xs font-normal text-gray-400">Revenir à la liste</span>
                                </div>
                            </button>
                            
                            <div class="flex items-center justify-end space-x-3 w-full sm:w-auto">
                                <button wire:click="downloadMemoPDF" type="button" 
                                    class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-lg shadow-md text-white transform hover:-translate-y-0.5 transition-all duration-200"
                                    style="background-color: #ef4444;">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Télécharger PDF
                                </button>
                            </div>
                        </div>

                        <!-- ZONE VISIONNEUSE (Style Papier) -->
                        <div class="bg-gray-200 rounded-lg shadow-inner p-4 md:p-8 flex justify-center min-h-[80vh]">
                            <div class="w-full max-w-5xl bg-white shadow-2xl">
                                @if($pdfBase64)
                                    <iframe 
                                        src="data:application/pdf;base64,{{ $pdfBase64 }}#view=FitH" 
                                        class="w-full h-[100vh] border-none">
                                    </iframe>
                                @else
                                    <div class="flex flex-col items-center justify-center py-20">
                                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#daaf2c]"></div>
                                        <p class="mt-4 text-gray-500 font-bold">Génération du rendu final DomPDF...</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                @elseif($isCreatingReply)

                    <!-- INTERFACE DE CRÉATION DE LA RÉPONSE -->
                    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in-up">
                        
                        <!-- Barre d'actions -->
                        <div class="mb-8 bg-white border border-gray-100 rounded-xl shadow-sm p-4 flex justify-between items-center">
                            <button wire:click="cancelReply" type="button" class="flex items-center text-gray-500 hover:text-black">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                <span class="font-bold">Annuler la réponse</span>
                            </button>
                            
                            <button wire:click="saveReply" wire:loading.attr="disabled" class="bg-yellow-500 px-6 py-2 rounded-lg font-bold text-black shadow-md">
                                <span wire:loading.remove>Envoyer la Réponse</span>
                                <span wire:loading>Traitement...</span>
                            </button>
                        </div>

                        <!-- FEUILLE DE PAPIER (REPRODUCTION DE VOTRE DESIGN) -->
                        <div class="bg-white rounded-lg shadow-2xl overflow-hidden border border-gray-200">
                            <div class="px-8 py-6 flex justify-between items-center bg-black text-white border-b-4 border-yellow-500">
                                <h2 class="text-2xl font-bold uppercase">Réponse au Mémorandum</h2>
                                <p class="text-sm">Date : {{ now()->format('d/m/Y') }}</p>
                            </div>

                            <div class="p-8 space-y-10">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 uppercase">Concerne</label>
                                        <input type="text" wire:model="new_concern" class="w-full border-0 border-b-2 border-gray-200 focus:border-yellow-500 focus:ring-0 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 uppercase">Objet</label>
                                        <input type="text" wire:model="new_object" class="w-full border-0 border-b-2 border-gray-200 focus:border-yellow-500 focus:ring-0 py-2 font-bold">
                                    </div>
                                </div>

                                <!-- LISTE DES DESTINATAIRES (Intégrée) -->
                                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                                    <h3 class="text-sm font-bold mb-4">Destinataires de la réponse</h3>
                                    
                                    <div class="flex gap-4 mb-4">
                                        <select wire:model="newRecipientEntity" class="flex-1 rounded-md border-gray-300">
                                            <option value="">Sélectionner entité...</option>
                                            @foreach(\App\Models\Entity::all() as $ent)
                                                <option value="{{ $ent->id }}">{{ $ent->name }}</option>
                                            @endforeach
                                        </select>
                                        <select wire:model="newRecipientAction" class="flex-1 rounded-md border-gray-300">
                                            <option value="">Action...</option>
                                            @foreach($actionsList as $act)
                                                <option value="{{ $act }}">{{ $act }}</option>
                                            @endforeach
                                        </select>
                                        <button wire:click="addRecipient" type="button" class="bg-black text-white px-4 py-2 rounded-md">+</button>
                                    </div>

                                    <table class="w-full text-sm">
                                        @foreach($recipients as $index => $r)
                                            <tr class="border-b">
                                                <td class="py-2 font-bold">{{ $r['entity_name'] }}</td>
                                                <td class="py-2">{{ $r['action'] }}</td>
                                                <td class="py-2 text-right">
                                                    <button wire:click="removeRecipient({{ $index }})" class="text-red-500">Supprimer</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>

                                <!-- ÉDITEUR QUILL (Type Word) -->
                                <div class="pt-2">
                                    <!-- Label avec info utilisateur -->
                                    <label class="block text-xs font-bold uppercase tracking-wide mb-3 flex justify-between items-center text-gray-500">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Corps du Document
                                        </span>
                                        <span class="text-[10px] font-normal normal-case italic opacity-60">
                                            Les lignes rouges indiquent les sauts de page (Format A4)
                                        </span>
                                    </label>

                                    <div wire:ignore 
                                        class="flex flex-col items-center bg-gray-100 rounded-xl p-4 border border-gray-200 shadow-inner"
                                        x-data="{
                                            content: @entangle('new_content'),
                                            quill: null,
                                            initQuill() {
                                                // Enregistrement des Polices
                                                const Font = Quill.import('formats/font');
                                                Font.whitelist = ['tahoma', 'timesnewroman', , 'arial'];
                                                Quill.register(Font, true);

                                                // Configuration Tailles
                                                const Size = Quill.import('attributors/style/size');
                                                Size.whitelist = ['12pt', '14pt', '16pt', '18pt', '24pt', '32pt'];
                                                Quill.register(Size, true);

                                                this.quill = new Quill(this.$refs.quillEditor, {
                                                    theme: 'snow',
                                                    placeholder: 'Commencez à rédiger votre mémorandum...',
                                                    modules: { toolbar: '#toolbar-container' }
                                                });

                                                if (this.content) { this.quill.root.innerHTML = this.content; }
                                                this.quill.on('text-change', () => { this.content = this.quill.root.innerHTML; });
                                            }
                                        }"
                                        x-init="initQuill()">
                                        
                                        <!-- BARRE D'OUTILS TAILWIND -->
                                        <div id="toolbar-container" class="w-full max-w-4xl mb-6 bg-white rounded-t-lg shadow-sm flex flex-wrap items-center justify-center gap-1 border border-gray-200 p-2 z-20 sticky top-0">
                                            <span class="ql-formats border-l border-gray-200 pl-2">
                                                <select class="ql-header">
                                                    <option value="1">Titre Niveau 1</option>
                                                    <option value="2">Titre Niveau 2</option>
                                                    <option value="3">Titre Niveau 3</option>
                                                    <option value="4">Titre Niveau 4</option>
                                                    <option value="5">Titre Niveau 5</option>
                                                    <option value="6">Titre Niveau 6</option>
                                                    <option selected>Texte Normal</option>
                                                </select>
                                            </span>
                                            <span class="ql-formats">
                                                <select class="ql-font">
                                                    <option value="tahoma" selected>Tahoma</option>
                                                    <option value="arial">Arial</option>
                                                    <option value="timesnewroman">Times New Roman</option>
                                                </select>
                                                <select class="ql-size">
                                                    <option value="12pt" selected>12pt</option>
                                                    <option value="14pt">14pt</option>
                                                    <option value="16pt">16pt</option>
                                                    <option value="18pt">18pt</option>
                                                    <option value="18pt">24pt</option>
                                                    <option value="18pt">32pt</option>
                                                </select>
                                            </span>
                                            <span class="ql-formats border-l border-gray-200 pl-2">
                                                <button class="ql-bold"></button>
                                                <button class="ql-italic"></button>
                                                <button class="ql-underline"></button>
                                                <select class="ql-color"></select>
                                            </span>
                                            <span class="ql-formats border-l border-gray-200 pl-2">
                                                <button class="ql-align" value=""></button>
                                                <button class="ql-align" value="center"></button>
                                                <button class="ql-align" value="justify"></button>
                                            </span>
                                            <span class="ql-formats border-l border-gray-200 pl-2">
                                                <button class="ql-list" value="ordered"></button>
                                                <button class="ql-list" value="bullet"></button>
                                            </span>
                                        </div>

                                        <!-- ZONE D'ÉDITION (FEUILLE A4) -->
                                        <div class="relative w-full max-w-[21cm] bg-white shadow-2xl border border-gray-300 transition-all duration-300">
                                            
                                            <!-- CALQUE DES LIGNES DE SAUT DE PAGE (Tailwind) -->
                                            <!-- 1080px est une estimation de la hauteur utile A4 en tenant compte des marges -->
                                            <div class="absolute inset-0 pointer-events-none z-10 overflow-hidden" aria-hidden="true">
                                                
                                                <!-- Page 1 -> 2 -->
                                                <div class="absolute w-full border-t-2 border-dashed border-red-400 flex justify-end items-start" style="top: 1080px;">
                                                    <span class="bg-red-500 text-white text-[9px] px-2 py-0.5 font-bold uppercase tracking-wider shadow-md rounded-bl-md">
                                                        Début Page 2
                                                    </span>
                                                </div>

                                                <!-- Page 2 -> 3 -->
                                                <div class="absolute w-full border-t-2 border-dashed border-red-400 flex justify-end items-start" style="top: 2160px;">
                                                    <span class="bg-red-500 text-white text-[9px] px-2 py-0.5 font-bold uppercase shadow-md rounded-bl-md">
                                                        Début Page 3
                                                    </span>
                                                </div>

                                                <!-- Page 3 -> 4 -->
                                                <div class="absolute w-full border-t-2 border-dashed border-red-400 flex justify-end items-start" style="top: 3240px;">
                                                    <span class="bg-red-500 text-white text-[9px] px-2 py-0.5 font-bold uppercase shadow-md rounded-bl-md">
                                                        Début Page 4
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- ÉDITEUR RÉEL -->
                                            <div x-ref="quillEditor" class="text-gray-900 min-h-[29.7cm]"></div>
                                        </div>

                                        <!-- Footer Info -->
                                        <div class="w-full max-w-[21cm] mt-4 flex justify-between text-[10px] text-gray-400 font-medium px-2">
                                            <span>Format : A4 Portrait (210 x 297 mm)</span>
                                            <span>Police recommandée : Tahoma / 11-12pt</span>
                                        </div>
                                    </div>
                                    @error('new_content') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror        
                                </div>

                                <style>
                                    /* Intégration fine des styles Quill non gérables par Tailwind */
                                    .ql-container.ql-snow {
                                        border: none !important;
                                        font-family: 'Tahoma', sans-serif;
                                    }
                                    
                                    .ql-editor {
                                        padding: 50px 70px !important; /* Marges type Word */
                                        min-height: 29.7cm;
                                        font-family: 'Tahoma', sans-serif;
                                        line-height: 1.5;
                                        font-size: 12pt;
                                    }

                                    /* Support des polices personnalisées dans la barre Quill */
                                    .ql-font-tahoma { font-family: 'Tahoma', sans-serif !important; }
                                    .ql-font-timesnewroman { font-family: 'Times New Roman', serif !important; }
                                    .ql-font-arial { font-family: 'Arial', sans-serif !important; }

                                    /* Forcer l'affichage des noms de tailles dans la barre d'outils */
                                    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="12pt"]::before,
                                    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="12pt"]::before { content: '12pt'; }
                                    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="14pt"]::before,
                                    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="14pt"]::before { content: '14pt'; }
                                    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="16pt"]::before,
                                    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="16pt"]::before { content: '16pt'; }
                                    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="18pt"]::before,
                                    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="18pt"]::before { content: '18pt'; }
                                    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="24pt"]::before,
                                    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="24pt"]::before { content: '24pt'; }
                                    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="32pt"]::before,
                                    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="32pt"]::before { content: '32pt'; }
                                </style>

                                <!-- ATTACHMENTS SECTION -->
                                <div class="mt-8 border-t border-gray-100 pt-6">
                                    <label class="block text-xs font-bold uppercase tracking-wide mb-3 flex items-center gap-2" style="color: var(--c-grey);">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        Pièces Jointes (P.J.)
                                    </label>

                                    <div class="space-y-4">
                                        <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-yellow-50/20 hover:border-[#daaf2c] transition-colors relative">
                                            <div class="space-y-1 text-center">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <div class="flex text-sm text-gray-600 justify-center">
                                                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-bold text-charte-gold focus-within:ring-2 focus-within:ring-[#daaf2c]">
                                                        <span>Téléverser des fichiers</span>
                                                        <input id="file-upload" wire:model="attachments" type="file" class="sr-only" multiple>
                                                    </label>
                                                </div>
                                                <p class="text-xs text-gray-500">PDF, DOCX, PNG, JPG jusqu'à 10MB</p>
                                            </div>

                                            <div wire:loading wire:target="attachments" class="absolute inset-0 bg-white/80 flex items-center justify-center">
                                                <div class="flex items-center font-semibold text-charte-gold">
                                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Upload en cours...
                                                </div>
                                            </div>
                                        </div>
                                        @error('attachments.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                                        @if($attachments)
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                @foreach($attachments as $index => $file)
                                                    <div class="relative flex items-center p-3 border border-gray-200 rounded-lg bg-gray-50 group hover:border-[#daaf2c] transition-colors">
                                                        <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-500 font-bold text-xs uppercase">
                                                            {{ $file->extension() }}
                                                        </div>
                                                        <div class="ml-4 flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $file->getClientOriginalName() }}</p>
                                                            <p class="text-xs text-gray-500">{{ round($file->getSize() / 1024, 2) }} KB</p>
                                                        </div>
                                                        <button type="button" wire:click="removeAttachment({{ $index }})" class="ml-2 inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-gray-300 hover:bg-red-500">
                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
            
                @else

                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                        <!-- TABLEAU MODERNE -->
                        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Objet & Concerne</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destinataires</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pièces Jointes</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse ($memos as $memo)
                                            <tr class="hover:bg-gray-50 transition-colors duration-150 group">
                                                
                                                <!-- 1. DATE -->
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <div class="flex flex-col">
                                                        <span class="font-medium text-gray-900">{{ $memo->created_at->format('d/m/Y') }}</span>
                                                        <span class="text-xs">{{ $memo->created_at->format('H:i') }}</span>
                                                    </div>
                                                </td>

                                                <!-- 2. OBJET -->
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-col max-w-xs sm:max-w-sm md:max-w-md">
                                                        <span class="text-sm font-bold text-gray-800 truncate" title="{{ $memo->object }}">{{ $memo->object }}</span>
                                                        <span class="text-xs text-gray-500 truncate mt-1">Concerne: {{ $memo->concern }}</span>
                                                    </div>
                                                </td>

                                                <!-- 3. DESTINATAIRES -->
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-wrap gap-2">
                                                        @php 
                                                            $destinataires = $memo->destinataires; 
                                                            $count = $destinataires->count();
                                                            $displayLimit = 3;
                                                        @endphp

                                                        @if($count > 0)
                                                            @foreach($destinataires->take($displayLimit) as $dest)
                                                                @php
                                                                    $isActionRequired = Str::contains(Str::lower($dest->action), 'nécessaire');
                                                                    $badgeClasses = $isActionRequired 
                                                                        ? 'bg-orange-100 text-orange-800 border border-orange-200' 
                                                                        : 'bg-blue-100 text-blue-800 border border-blue-200';
                                                                @endphp

                                                                <div class="inline-flex flex-col items-start justify-center px-2.5 py-1 rounded-md text-xs font-medium {{ $badgeClasses }}" 
                                                                    title="Action attendue : {{ $dest->action }}">
                                                                    <span class="font-bold">
                                                                        {{ $dest->entity->ref ?? Str::limit($dest->entity->name, 15) }}
                                                                    </span>
                                                                    <span class="text-[10px] opacity-80 leading-tight">
                                                                        {{ Str::limit($dest->action, 20) }}
                                                                    </span>
                                                                </div>
                                                            @endforeach

                                                            @if($count > $displayLimit)
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                                                    +{{ $count - $displayLimit }}
                                                                </span>
                                                            @endif
                                                        @else
                                                            <span class="text-xs text-gray-400 italic">Non assigné</span>
                                                        @endif
                                                    </div>
                                                </td>

                                                <!-- COLONNE PIÈCES JOINTES -->
                                                <td class="px-6 py-4 whitespace-nowrap" x-data="{ openFiles: false }">
                                                    @php
                                                        $pj = $memo->pieces_jointes;
                                                        if (is_string($pj)) { $pj = json_decode($pj, true); }
                                                        $pj = is_array($pj) ? $pj : [];
                                                        $countPj = count($pj);
                                                    @endphp

                                                    @if($countPj > 0)
                                                        <button @click="openFiles = true" type="button" class="flex items-center space-x-1 text-gray-600 hover:text-blue-600 transition-colors">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                            <span class="text-sm font-bold">{{ $countPj }}</span>
                                                        </button>

                                                        <template x-teleport="body">
                                                            <div x-show="openFiles" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm" @click.self="openFiles = false">
                                                                <div class="bg-white rounded-lg shadow-2xl w-80 max-w-sm mx-4 overflow-hidden border border-gray-100">
                                                                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                                                                        <h3 class="text-sm font-bold text-gray-700">Pièces Jointes ({{ $countPj }})</h3>
                                                                        <button @click="openFiles = false" class="text-gray-400 hover:text-gray-600">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                                        </button>
                                                                    </div>
                                                                    <div class="max-h-64 overflow-y-auto p-2">
                                                                        <ul class="space-y-1">
                                                                            @foreach($pj as $file)
                                                                                @php
                                                                                    $filePath = is_string($file) ? $file : ($file['path'] ?? $file['url'] ?? $file[0] ?? '');
                                                                                    $fileName = is_string($file) ? basename($file) : ($file['original_name'] ?? $file['name'] ?? basename($filePath));
                                                                                @endphp
                                                                                @if($filePath)
                                                                                    <li>
                                                                                        <a href="{{ Storage::url($filePath) }}" target="_blank" class="flex items-center p-2 hover:bg-blue-50 rounded-md text-sm text-gray-700">
                                                                                            <div class="bg-blue-100 p-1.5 rounded-md mr-3 text-blue-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
                                                                                            <div class="flex-1 min-w-0"><p class="truncate font-medium">{{ Str::limit($fileName, 30) }}</p></div>
                                                                                        </a>
                                                                                    </li>
                                                                                @endif
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    @else
                                                        <span class="text-gray-300 text-xs">-</span>
                                                    @endif
                                                </td>

                                                <!-- 4. ACTIONS -->
                                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                    <div class="flex items-center justify-center space-x-3">

                                                        <!-- APERÇU (Toujours visible) -->
                                                        <button wire:click="viewMemo({{ $memo->id }})" class="text-gray-400 hover:text-blue-600 transition-colors" title="Aperçu">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                        </button>

                                                        @php
                                                            $monStatutDest = $memo->destinataires->where('entity_id', Auth::user()->entity_id)->first();
                                                            $estFiniPourMonEntite = $monStatutDest && in_array($monStatutDest->processing_status, ['traiter', 'decision_prise', 'repondu']);
                                                        @endphp

                                                        @if($estFiniPourMonEntite)
                                                            <!-- CAS : TRAITEMENT TERMINÉ (MODAL D'INFO) -->
                                                            <div x-data="{ infoOpen: false }">
                                                                <button @click="infoOpen = true" type="button" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-green-100 text-green-700 border border-green-200 hover:bg-green-200 transition-colors shadow-sm">
                                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                                    Traité
                                                                </button>

                                                                <template x-teleport="body">
                                                                    <div x-show="infoOpen" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                                                                        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm" @click="infoOpen = false"></div>
                                                                        <div class="relative bg-white rounded-xl shadow-2xl max-w-sm w-full overflow-hidden border border-gray-100" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                                                                            <div class="bg-green-600 px-4 py-3 flex items-center justify-between">
                                                                                <h3 class="text-white font-bold flex items-center gap-2 text-sm uppercase">Traitement Finalisé</h3>
                                                                                <button @click="infoOpen = false" class="text-white hover:text-gray-200"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                                                            </div>
                                                                            <div class="p-6 text-center">
                                                                                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4"><svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
                                                                                <p class="text-gray-800 font-bold mb-2">Action confirmée !</p>
                                                                                <p class="text-gray-500 text-xs leading-relaxed">
                                                                                    Dossier finalisé par votre entité. Il sera automatiquement classé dès que les autres entités destinataires auront également terminé leur traitement.
                                                                                </p>
                                                                            </div>
                                                                            <div class="bg-gray-50 px-4 py-3 text-center">
                                                                                <button @click="infoOpen = false" class="w-full bg-gray-800 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-gray-700 transition-colors uppercase tracking-wider">Compris</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        @else
                                                            <!-- CAS : ACTIONS DISPONIBLES -->
                                                            
                                                            <!-- FAVORIS -->
                                                            <button wire:click="toggleFavorite({{ $memo->id }})" class="transition-colors duration-200 {{ $memo->is_favorited ? 'text-yellow-400' : 'text-gray-300' }}" title="Favoris">
                                                                @if($memo->is_favorited)
                                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                                                @else
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                                                @endif
                                                            </button>

                                                            @php
                                                                $userPoste = Str::lower(Auth::user()->poste);
                                                                $isSecretaire = Str::contains($userPoste, 'secretaire');
                                                                $isManager = !$isSecretaire;
                                                            @endphp

                                                            <!-- TRANSMETTRE -->
                                                            <button wire:click="transMemo({{ $memo->id }})" wire:loading.attr="disabled" class="text-gray-400 hover:text-indigo-600 transition-colors" title="{{ $isSecretaire ? 'Enregistrer et Transmettre' : 'Transmettre' }}">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                                            </button>

                                                            @if($isManager)
                                                                <div class="h-4 w-px bg-gray-300 mx-1"></div>
                                                            
                                                                @php
                                                                    $myAction = $monStatutDest->action ?? '';
                                                                    $isDeciderEntity = Str::contains($myAction, 'Décider');
                                                                @endphp

                                                                @if($isDeciderEntity)
                                                                    <!-- DÉCIDER -->
                                                                    <button wire:click="openDecisionModal({{ $memo->id }}, 'accord')" class="text-gray-400 hover:text-green-600 transition-colors" title="Donner Accord">
                                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                                    </button>
                                                                    <button wire:click="openDecisionModal({{ $memo->id }}, 'refus')" class="text-gray-400 hover:text-red-600 transition-colors" title="Refuser">
                                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                                    </button>
                                                            
                                                                @else
                                                                    <!-- RÉPONDRE -->
                                                                    <button wire:click="replyMemo({{ $memo->id }})" class="text-gray-400 hover:text-purple-600 transition-colors" title="Répondre"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg></button>

                                                                    <!-- TERMINER -->
                                                                    <button wire:click="openCloseModal({{ $memo->id }})" class="text-gray-400 hover:text-green-600 transition-colors" title="Terminer le traitement"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></button>
                                                                @endif
                                                            @endif
                                                        @endif

                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-12 text-center">
                                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                                        <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                        <p class="text-lg font-medium text-gray-900">Aucun Mémo Entrant dans votre entité pour le moment</p>
                                                        <p class="text-sm">Les mémos entrants dans votre entité et qui vous ont été coté s'afficheront ici..</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- PAGINATION -->
                            <div class="px-6 py-4 border-t border-gray-200">
                                {{ $memos->links() }} 
                            </div>
                        </div>
                    </div>
                @endif

                

                <!-- ============================================== -->
                <!-- MODAL 1 : ENREGISTREMENT (Pour Secrétaires)    -->

                @if($isRegistrationModalOpen)
                    <div class="fixed inset-0 z-[110] overflow-y-auto" aria-labelledby="modal-reg-title" role="dialog" aria-modal="true">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            
                            <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity" wire:click="closeRegistrationModal"></div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                                
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                        </div>
                                        
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-reg-title">
                                                Enregistrement du Memo
                                            </h3>
                                            <div class="mt-4 space-y-3">
                                                
                                                <!-- 1. RÉFÉRENCE & DATE (Côte à côte pour gagner de la place) -->
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700">Référence *</label>
                                                        <input type="text" 
                                                            wire:model="reg_reference" 
                                                            readonly
                                                            class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-100 cursor-not-allowed select-none"
                                                            style="outline: none;">
                                                        @error('reg_reference') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700">Date Enreg. *</label>
                                                        <input type="text" wire:model="reg_date" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                                                        @error('reg_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>

                                                <!-- 2. ENTITÉ EXPÉDITRICE -->
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Entité Expéditrice *</label>
                                                    <input type="text" 
                                                        wire:model="reg_expediteur" 
                                                        readonly
                                                        class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-100 cursor-not-allowed" 
                                                        placeholder="Entité d'origine">
                                                    <p class="text-[10px] text-gray-400 mt-0.5">Pré-rempli automatiquement selon l'expéditeur du mémo.</p>
                                                    @error('reg_expediteur') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>                  

                                                <!-- 3. NATURE -->
                                                <div>
                                                    
                                                    <input type="hidden" wire:model="reg_nature" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500" placeholder="Ex: Note de service, Lettre...">
                                                    @error('reg_nature') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>

                                                <!-- 4. OBJET -->
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Objet *</label>
                                                    <textarea 
                                                        wire:model="reg_objet" 
                                                        readonly
                                                        rows="2" 
                                                        class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-100 cursor-not-allowed"></textarea>
                                                    @error('reg_objet') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button type="button" wire:click="saveRegistrationAndContinue" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                        <span wire:loading.remove wire:target="saveRegistrationAndContinue">Enregistrer et Continuer &rarr;</span>
                                        <span wire:loading wire:target="saveRegistrationAndContinue">...</span>
                                    </button>
                                    <button type="button" wire:click="closeRegistrationModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                        Annuler
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif


                <!-- ============================================== -->
                <!-- MODAL 2 : TRANSMISSION / COTATION (Pour tous)  -->
                <!-- ============================================== -->
                @if($isTransModalOpen)
                    <div class="fixed inset-0 z-[120] overflow-y-auto" aria-labelledby="modal-trans-title" role="dialog" aria-modal="true">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            
                            <!-- Overlay -->
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                                wire:click="closeTransModal"></div>

                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                            <!-- Contenu du Modal -->
                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                                
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        
                                        <!-- Icone Avion Bleu -->
                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                            </svg>
                                        </div>
                                        
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-trans-title">
                                                Transmettre le mémo
                                            </h3>
                                            
                                            <div class="mt-4">
                                                <!-- Message d'instruction -->
                                                <p class="text-sm text-gray-500 mb-4">
                                                    Veuillez sélectionner le(s) destinataire(s) du groupe <span class="font-bold text-gray-800">{{ $targetRoleName }}</span>.
                                                </p>

                                                <!-- 1. CHAMP COMMENTAIRE / NOTE (Historique) -->
                                                <div class="mb-4">
                                                    <label for="comment" class="block text-sm font-medium text-gray-700">Note / Annotation (Optionnel)</label>
                                                    <textarea 
                                                        wire:model="comment" 
                                                        id="comment" 
                                                        rows="2" 
                                                        class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md placeholder-gray-400"
                                                        placeholder="Ex: Pour attribution, Pour avis, Vu et validé..."></textarea>
                                                </div>

                                                <!-- 2. LISTE DES DESTINATAIRES (Checkboxes) -->
                                                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-md bg-gray-50">
                                                    <ul class="divide-y divide-gray-200">
                                                        @foreach($targetRecipients as $recipient)
                                                            <li class="relative flex items-start py-3 px-4 hover:bg-white transition cursor-pointer">
                                                                <div class="min-w-0 flex-1 text-sm">
                                                                    <label for="recipient-{{ $recipient->id }}" class="font-medium text-gray-700 select-none cursor-pointer block w-full">
                                                                        {{ $recipient->first_name }} {{ $recipient->last_name }}
                                                                        <span class="block text-gray-500 text-xs mt-0.5">{{ $recipient->poste }}</span>
                                                                    </label>
                                                                </div>
                                                                <div class="ml-3 flex items-center h-5">
                                                                    <input id="recipient-{{ $recipient->id }}" 
                                                                        value="{{ $recipient->id }}" 
                                                                        wire:model="selectedRecipients" 
                                                                        type="checkbox" 
                                                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded cursor-pointer">
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                
                                                @error('selectedRecipients') 
                                                    <span class="text-red-500 text-xs mt-2 block font-medium">
                                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                        {{ $message }}
                                                    </span> 
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer du Modal -->
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <!-- Bouton Valider -->
                                    <button type="button" 
                                            wire:click="confirmTransmission" 
                                            wire:loading.attr="disabled"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                        
                                        <span wire:loading.remove wire:target="confirmTransmission">
                                            Transmettre <span class="ml-1">&rarr;</span>
                                        </span>
                                        <span wire:loading wire:target="confirmTransmission" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            Traitement...
                                        </span>
                                    </button>
                                    
                                    <!-- Bouton Annuler -->
                                    <button type="button" 
                                            wire:click="closeTransModal" 
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                        Annuler
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                @endif

                <!-- ============================================== -->
                <!-- MODAL 3 : CONFIRMATION DE CLÔTURE          -->
                <!-- ============================================== -->
                @if($isCloseModalOpen)
                    <div class="fixed inset-0 z-[130] overflow-y-auto" aria-labelledby="modal-close-title" role="dialog" aria-modal="true">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            
                            <!-- Overlay -->
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                                wire:click="cancelCloseModal"></div>

                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                            <!-- Contenu du Modal -->
                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                                
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        
                                        <!-- Icone Check Vert -->
                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-close-title">
                                                Clôturer le dossier
                                            </h3>
                                            
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">
                                                    Êtes-vous sûr de vouloir terminer le traitement de ce mémo ?<br>
                                                </p>

                                                <!-- Champ commentaire optionnel -->
                                                <div class="mt-4">
                                                    <label for="close-comment" class="block text-sm font-medium text-gray-700">Observation finale (Optionnel)</label>
                                                    <textarea 
                                                        wire:model="closingComment" 
                                                        id="close-comment" 
                                                        rows="2" 
                                                        class="mt-1 shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                                        placeholder="Ex: Dossier traité, Vu et classé..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer du Modal -->
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    
                                    <!-- Bouton Confirmer -->
                                    <button type="button" 
                                            wire:click="confirmCloseMemo" 
                                            wire:loading.attr="disabled"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                        
                                        <span wire:loading.remove wire:target="confirmCloseMemo">
                                            Confirmer et Terminer
                                        </span>
                                        <span wire:loading wire:target="confirmCloseMemo">
                                            Traitement...
                                        </span>
                                    </button>
                                    
                                    <!-- Bouton Annuler -->
                                    <button type="button" 
                                            wire:click="cancelCloseModal" 
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                        Annuler
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                @endif

                <!-- ============================================== -->
                <!-- MODAL 4 : CONFIRMATION DE DÉCISION             -->
                <!-- ============================================== -->
                @if($isDecisionModalOpen)
                    <div class="fixed inset-0 z-[140] overflow-y-auto" aria-labelledby="modal-decision-title" role="dialog" aria-modal="true">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('isDecisionModalOpen', false)"></div>

                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                                
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        
                                        <!-- Icone dynamique selon le choix -->
                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full {{ $decisionChoice === 'accord' ? 'bg-green-100' : 'bg-red-100' }} sm:mx-0 sm:h-10 sm:w-10">
                                            @if($decisionChoice === 'accord')
                                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            @else
                                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                            <h3 class="text-lg leading-6 font-bold {{ $decisionChoice === 'accord' ? 'text-green-700' : 'text-red-700' }}" id="modal-decision-title">
                                                {{ $decisionChoice === 'accord' ? 'Confirmer l\'Accord' : 'Confirmer le Refus' }}
                                            </h3>
                                            
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">
                                                    Voulez-vous vraiment valider/Refuser cette décision ? Cette action sera enregistrée dans l'historique du mémo.
                                                </p>

                                                <div class="mt-4">
                                                    <label class="block text-sm font-medium text-gray-700">Commentaire (Optionnel)</label>
                                                    <textarea 
                                                        wire:model="decisionComment" 
                                                        rows="2" 
                                                        class="mt-1 shadow-sm focus:ring-{{ $decisionChoice === 'accord' ? 'green' : 'red' }}-500 focus:border-{{ $decisionChoice === 'accord' ? 'green' : 'red' }}-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                                        placeholder="Précisez votre décision..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button type="button" 
                                            wire:click="submitDecision({{ $memo_id }}, '{{ $decisionChoice }}')" 
                                            wire:loading.attr="disabled"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 {{ $decisionChoice === 'accord' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }} text-base font-medium text-white focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                        
                                        <span wire:loading.remove>Confirmer</span>
                                        <span wire:loading>Traitement...</span>
                                    </button>
                                    
                                    <button type="button" 
                                            wire:click="$set('isDecisionModalOpen', false)" 
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                        Annuler
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                @endif
    </div>
<div>  
    
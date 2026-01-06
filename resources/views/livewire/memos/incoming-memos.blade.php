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
                
                @elseif($isEditing)

                    <!-- ========================================== -->
                    <!-- VUE : ÉDITION DU MÉMO (Style Papier A4) -->
                    <!-- ========================================== -->
                    <div class="max-w-5xl mx-auto animate-fade-in-up">
                        
                        <!-- Barre d'actions supérieure -->
                        <div class="mb-8 bg-white border border-gray-100 rounded-xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <button wire:click="cancelEdit" type="button" class="group flex items-center text-gray-500 hover:text-black transition-colors">
                                <div class="mr-3 h-10 w-10 rounded-full bg-gray-100 group-hover:bg-yellow-100 group-hover:text-yellow-700 flex items-center justify-center transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                </div>
                                <div class="flex flex-col items-start text-left">
                                    <span class="font-bold text-base text-black">Retour</span>
                                    <span class="text-xs text-gray-400">Annuler les modifications</span>
                                </div>
                            </button>
                            
                            <div class="flex items-center space-x-3">
                                <button wire:click="cancelEdit" type="button" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Annuler
                                </button>
                                
                                <button wire:click="save" wire:loading.attr="disabled" type="button" 
                                    class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-lg shadow-md text-white bg-yellow-500 hover:bg-yellow-600 transition-all">
                                    <span wire:loading.remove wire:target="save">Mettre à jour le dossier</span>
                                    <span wire:loading wire:target="save">Traitement...</span>
                                </button>
                            </div>
                        </div>

                        <!-- LE FORMULAIRE STYLE MÉMORANDUM -->
                        <div class="bg-white rounded-lg shadow-2xl overflow-hidden border border-gray-200">
                            
                            <!-- Entête Papier -->
                            <div class="px-8 py-6 flex justify-between items-center bg-gray-900 text-white border-b-4 border-yellow-500">
                                <div>
                                    <h2 class="text-2xl font-bold uppercase" style="font-family: 'Times New Roman', serif;">Mémorandum</h2>
                                    <p class="text-xs font-bold tracking-widest mt-1 text-yellow-500">MODE ÉDITION - RÉF: {{ $memo_id }}</p>
                                </div>
                                <div class="text-right opacity-80 text-sm">
                                    Date initiale : {{ $date }}
                                </div>
                            </div>

                            <div class="p-8 md:p-12 space-y-10">
                                <!-- Concern & Objet -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold uppercase text-gray-400">Pour (Concerne)</label>
                                        <input type="text" wire:model="concern" class="w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-black focus:ring-0 focus:border-yellow-500 sm:text-lg transition-colors">
                                        @error('concern') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold uppercase text-gray-400">Objet</label>
                                        <input type="text" wire:model="object" class="w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-black font-bold focus:ring-0 focus:border-yellow-500 sm:text-lg transition-colors">
                                        @error('object') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Liste de Distribution -->
                                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                    <h3 class="text-sm font-bold uppercase mb-4 text-gray-700 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                        Liste de Distribution
                                    </h3>
                                    <div class="flex flex-col md:flex-row gap-3 mb-4">
                                        <select wire:model="newRecipientEntity" class="flex-1 rounded-md border-gray-300 text-sm focus:ring-yellow-500">
                                            <option value="">-- Choisir Entité --</option>
                                            @foreach($entities as $entity) <option value="{{ $entity->id }}">{{ $entity->name }}</option> @endforeach
                                        </select>
                                        <select wire:model="newRecipientAction" class="flex-1 rounded-md border-gray-300 text-sm focus:ring-yellow-500">
                                            <option value="">-- Choisir Action --</option>
                                            @foreach($actionsList as $act) <option value="{{ $act }}">{{ $act }}</option> @endforeach
                                        </select>
                                        <button wire:click="addRecipient" type="button" class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm font-bold">Ajouter</button>
                                    </div>

                                    @if(count($recipients) > 0)
                                        <div class="bg-white border border-gray-200 rounded-md overflow-hidden shadow-sm">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <tbody class="divide-y divide-gray-200">
                                                    @foreach($recipients as $idx => $r)
                                                        <tr>
                                                            <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $r['entity_name'] }}</td>
                                                            <td class="px-4 py-2 text-sm text-yellow-700 font-bold italic">{{ $r['action'] }}</td>
                                                            <td class="px-4 py-2 text-right">
                                                                <button wire:click="removeRecipient({{ $idx }})" class="text-gray-400 hover:text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
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
                                            content: @entangle('content'),
                                            quill: null,
                                            initQuill() {
                                                // Enregistrement des Polices
                                                const Font = Quill.import('formats/font');
                                                Font.whitelist = ['helvetica', 'arial', 'roboto', 'tahoma', 'timesnewroman', 'georgia', 'inter'];
                                                Quill.register(Font, true);

                                                // Configuration Tailles
                                                const Size = Quill.import('attributors/style/size');
                                                Size.whitelist = ['10px', '12px', '14px', '16px', '18px', '20px', '24px'];
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
                                            <span class="ql-formats">
                                                <select class="ql-font">
                                                    <option value="tahoma" selected>Tahoma</option>
                                                    <option value="arial">Arial</option>
                                                    <option value="timesnewroman">Times New Roman</option>
                                                </select>
                                                <select class="ql-size">
                                                    <option value="12px">12px</option>
                                                    <option value="14px" selected>14px</option>
                                                    <option value="16px">16px</option>
                                                    <option value="18px">18px</option>
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
                                </div>

                                <style>
                                    /* Intégration fine des styles Quill non gérables par Tailwind */
                                    .ql-container.ql-snow {
                                        border: none !important;
                                    }
                                    
                                    .ql-editor {
                                        padding: 50px 70px !important; /* Marges type Word */
                                        min-height: 29.7cm;
                                        font-family: 'Tahoma', sans-serif;
                                        line-height: 1.5;
                                        font-size: 14px;
                                    }

                                    /* Support des polices personnalisées dans la barre Quill */
                                    .ql-font-tahoma { font-family: 'Tahoma', sans-serif !important; }
                                    .ql-font-timesnewroman { font-family: 'Times New Roman', serif !important; }
                                    .ql-font-arial { font-family: 'Arial', sans-serif !important; }

                                    /* Forcer l'affichage des noms de tailles dans la barre d'outils */
                                    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="12px"]::before,
                                    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="12px"]::before { content: '12px'; }
                                    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="14px"]::before,
                                    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="14px"]::before { content: '14px'; }
                                    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="16px"]::before,
                                    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="16px"]::before { content: '16px'; }
                                    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="18px"]::before,
                                    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="18px"]::before { content: '18px'; }
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
                                        <!-- Zone de Drop/Upload -->
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

                                            <div wire:loading wire:target="attachments" class="absolute inset-0 bg-white/80 flex items-center justify-center z-10">
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

                                        <!-- LISTE DES FICHIERS -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            
                                            <!-- 1. AFFICHAGE DES FICHIERS DÉJÀ ENREGISTRÉS (Base de données) -->
                                            @foreach($existingAttachments as $index => $item)
                                                @php
                                                    // On extrait le chemin (string) que l'item soit une chaîne ou un tableau
                                                    $filePath = is_string($item) ? $item : ($item['path'] ?? '');
                                                    // On récupère l'extension de manière sécurisée
                                                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                                                    // On récupère le nom du fichier pour l'affichage
                                                    $fileName = basename($filePath);
                                                @endphp
                                                
                                                <div class="relative flex items-center p-3 border border-blue-200 rounded-lg bg-blue-50/30 group hover:border-blue-400 transition-colors">
                                                    <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center bg-white border border-blue-100 text-blue-500 font-bold text-[10px] uppercase">
                                                        {{ $extension ?: '?' }}
                                                    </div>
                                                    <div class="ml-4 flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate" title="{{ $fileName }}">
                                                            {{ $fileName }}
                                                        </p>
                                                        <p class="text-[10px] text-blue-600 font-bold uppercase tracking-tighter">Fichier déjà enregistré</p>
                                                    </div>
                                                    
                                                    <button type="button" 
                                                        wire:click="removeExistingAttachment({{ $index }})" 
                                                        class="ml-2 inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-gray-400 hover:bg-red-500 transition-colors">
                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endforeach

                                            <!-- 2. AFFICHAGE DES NOUVEAUX FICHIERS (En cours d'upload) -->
                                            @if($attachments)
                                                @foreach($attachments as $index => $file)
                                                    <div class="relative flex items-center p-3 border border-yellow-200 rounded-lg bg-yellow-50/30 group hover:border-[#daaf2c] transition-colors">
                                                        <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-500 font-bold text-[10px] uppercase">
                                                            {{ $file->extension() }}
                                                        </div>
                                                        <div class="ml-4 flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $file->getClientOriginalName() }}</p>
                                                            <p class="text-[10px] text-green-600 font-bold uppercase">Nouveau fichier</p>
                                                        </div>
                                                        <button type="button" wire:click="removeAttachment({{ $index }})" class="ml-2 inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-gray-300 hover:bg-red-500 transition-colors">
                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @else
                    <!-- EN-TÊTE & RECHERCHE -->
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Mémos Sortants</h2>
                            <p class="text-sm text-gray-500">Mémos pour lesquels votre validation est requise.</p>
                        </div>

                        <div class="relative w-full md:w-96">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input 
                                wire:model.live.debounce.300ms="search" 
                                type="text" 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm transition duration-150 ease-in-out shadow-sm" 
                                placeholder="Rechercher par objet, concerné..."
                            >
                            <!-- Spinner de chargement -->
                            <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="animate-spin h-5 w-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- TABLEAU MODERNE -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Objet & Concerne</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destinataires</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut Workflow</th>
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

                                        <!-- 3. DESTINATAIRES (Badges avec Action) -->
                                            <td class="px-6 py-4">
                                                <div class="flex flex-wrap gap-2">
                                                    @php 
                                                        // Récupération de la relation chargée dans le contrôleur
                                                        $destinataires = $memo->destinataires; 
                                                        $count = $destinataires->count();
                                                        $displayLimit = 3; // Augmenté un peu pour la lisibilité
                                                    @endphp

                                                    @if($count > 0)
                                                        @foreach($destinataires->take($displayLimit) as $dest)
                                                            @php
                                                                // Logique de couleur selon l'action
                                                                $isActionRequired = Str::contains(Str::lower($dest->action), 'nécessaire');
                                                                $badgeClasses = $isActionRequired 
                                                                    ? 'bg-orange-100 text-orange-800 border border-orange-200' 
                                                                    : 'bg-blue-100 text-blue-800 border border-blue-200';
                                                            @endphp

                                                            <div class="inline-flex flex-col items-start justify-center px-2.5 py-1 rounded-md text-xs font-medium {{ $badgeClasses }}" 
                                                                title="Action attendue : {{ $dest->action }}">
                                                                
                                                                <!-- Nom de l'entité (REF ou Nom tronqué) -->
                                                                <span class="font-bold">
                                                                    {{ $dest->entity->ref ?? Str::limit($dest->entity->name, 15) }}
                                                                </span>
                                                                
                                                                <!-- L'action affichée en tout petit en dessous -->
                                                                <span class="text-[10px] opacity-80 leading-tight">
                                                                    {{ Str::limit($dest->action, 20) }}
                                                                </span>
                                                            </div>
                                                        @endforeach

                                                        @if($count > $displayLimit)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200" title="Et {{ $count - $displayLimit }} autres...">
                                                                +{{ $count - $displayLimit }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="text-xs text-gray-400 italic flex items-center gap-1">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                            Non assigné
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4" x-data="{ openReason: false }">
                                                <div class="flex flex-col items-start gap-1">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border  cursor-default">
                                                        {{ $memo->status }}
                                                    </span>
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
                                                    <!-- 1. Le bouton Trombone (Déclencheur) -->
                                                    <button 
                                                        @click="openFiles = true" 
                                                        type="button"
                                                        class="flex items-center space-x-1 text-gray-600 hover:text-blue-600 transition-colors focus:outline-none">
                                                        
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                        <span class="text-sm font-bold">{{ $countPj }}</span>
                                                    </button>

                                                    <!-- 2. Le Mini-Modal (S'affiche par dessus TOUT le site) -->
                                                    <div 
                                                        x-show="openFiles" 
                                                        style="display: none;"
                                                        class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm"
                                                        @click.self="openFiles = false"
                                                        x-transition:enter="transition ease-out duration-200"
                                                        x-transition:enter-start="opacity-0"
                                                        x-transition:enter-end="opacity-100"
                                                        x-transition:leave="transition ease-in duration-150"
                                                        x-transition:leave-start="opacity-100"
                                                        x-transition:leave-end="opacity-0">
                                                        
                                                        <!-- Contenu du Popup -->
                                                        <div class="bg-white rounded-lg shadow-2xl w-80 max-w-sm mx-4 overflow-hidden border border-gray-100">
                                                            
                                                            <!-- En-tête Popup -->
                                                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                                                                <h3 class="text-sm font-bold text-gray-700">Pièces Jointes ({{ $countPj }})</h3>
                                                                <button @click="openFiles = false" class="text-gray-400 hover:text-gray-600">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                                </button>
                                                            </div>

                                                            <!-- Liste des fichiers (Scrollable) -->
                                                            <div class="max-h-64 overflow-y-auto p-2">
                                                                <ul class="space-y-1">
                                                                    @foreach($pj as $file)
                                                                        @php
                                                                            $filePath = is_string($file) ? $file : ($file['path'] ?? $file['url'] ?? $file[0] ?? '');
                                                                            $fileName = is_string($file) ? basename($file) : ($file['original_name'] ?? $file['name'] ?? basename($filePath));
                                                                        @endphp

                                                                        @if($filePath)
                                                                            <li>
                                                                                <a href="{{ Storage::url($filePath) }}" target="_blank" class="flex items-center p-2 hover:bg-blue-50 rounded-md text-sm text-gray-700 transition-colors group">
                                                                                    <div class="bg-blue-100 p-1.5 rounded-md mr-3 text-blue-600 group-hover:bg-blue-200">
                                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                                    </div>
                                                                                    <div class="flex-1 min-w-0">
                                                                                        <p class="truncate font-medium">{{ Str::limit($fileName, 30) }}</p>
                                                                                        <p class="text-[10px] text-gray-400">Cliquez pour ouvrir</p>
                                                                                    </div>
                                                                                    <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                                                </a>
                                                                            </li>
                                                                        @endif
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-gray-300 text-xs">-</span>
                                                @endif
                                            </td>

                                            <!-- 4. ACTIONS (Boutons Icones) -->
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <div class="flex items-center justify-center space-x-3">
                                                    
                                                    <!-- CALCUL DES DROITS POUR CETTE LIGNE -->
                                                    @php
                                                        // 1. Vérification si l'utilisateur a le droit de traiter le mémo (présence dans treatment_holders)
                                                        $isTreatmentHolder = false;
                                                        if ($memo->treatment_holders) {
                                                            $isTreatmentHolder = in_array(auth()->id(), (array)$memo->treatment_holders);
                                                        }

                                                        // 2. Logique de remplacement (pour les badges P/O et droits spécifiques)
                                                        $repRights = $this->getReplacementRights($memo);
                                                        $isRep = ($repRights !== null && $repRights['is_active']);
                                                        $repActions = $isRep ? $repRights['actions_allowed'] : [];
                                                    @endphp

                                                    <!-- =========================================================== -->
                                                    <!-- ACTIONS TOUJOURS VISIBLES (CONSULTATION)                    -->
                                                    <!-- =========================================================== -->

                                                    <!-- 1. ACTION : VOIR -->
                                                    <button wire:click="viewMemo({{ $memo->id }})" class="text-gray-400 hover:text-blue-600 transition-colors" title="Aperçu">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    </button>

                                                    <!-- 2. ACTION : FAVORIS -->
                                                    <button wire:click="toggleFavorite({{ $memo->id }})" 
                                                        class="transition-colors duration-200 {{ $memo->is_favorited ? 'text-yellow-400 hover:text-yellow-500' : 'text-gray-300 hover:text-yellow-400' }}" 
                                                        title="{{ $memo->is_favorited ? 'Retirer des favoris' : 'Ajouter aux favoris' }}">
                                                        @if($memo->is_favorited)
                                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                                        @else
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                                        @endif
                                                    </button>
                                                    


                                                    <!-- =========================================================== -->
                                                    <!-- ACTIONS CONDITIONNELLES (UNIQUEMENT SI DROIT DE TRAITER)    -->
                                                    <!-- =========================================================== -->
                                                    @if($isTreatmentHolder)

                                                        <!-- 3. ACTION : MODIFIER -->
                                                        <button wire:click="editMemo({{ $memo->id }})" class="text-gray-400 hover:text-yellow-600 transition-colors" title="Modifier">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                        </button>

                                                        {{-- CAS SECRÉTAIRE --}}
                                                        @if(auth()->user()->poste?->value == "Secretaire")
                                                            <button wire:click="transMemo({{ $memo->id }})" class="p-2 rounded-full bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition" title="Enregistrer et Transmettre">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                                            </button>  

                                                        {{-- CAS MANAGER / DÉCIDEUR --}}
                                                        @else
                                                            <!-- ATTRIBUER & ENVOYER -->
                                                            <button wire:click="assignMemo({{ $memo->id }})" class="text-gray-400 hover:text-green-600 transition-colors relative group" title="Attribuer & Envoyer">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                                                @if($isRep)<span class="absolute -top-2 -right-2 text-[9px] bg-orange-100 text-orange-600 px-1 rounded border border-orange-200">P/O</span>@endif
                                                            </button>
                                                    
                                                            <!-- RETOURNER -->
                                                            <button wire:click="askReject({{ $memo->id }}, 'return')" class="text-gray-400 hover:text-orange-500 transition-colors relative" title="Retourner à l'auteur">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 0118 0z"></path></svg>
                                                                @if($isRep)<span class="absolute -top-2 -right-2 text-[9px] bg-orange-100 text-orange-600 px-1 rounded border border-orange-200">P/O</span>@endif
                                                            </button>

                                                            <!-- REJETER (ARCHIVER) -->
                                                            <button wire:click="askReject({{ $memo->id }}, 'archive')" class="text-gray-400 hover:text-red-600 transition-colors relative" title="Rejeter et Archiver">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                                @if($isRep)<span class="absolute -top-2 -right-2 text-[9px] bg-red-100 text-red-600 px-1 rounded border border-red-200">P/O</span>@endif
                                                            </button>
                                                        @endif

                                                    @else {{-- Fin du check isTreatmentHolder --}}

                                                        <!-- HISTORIQUE -->
                                                        <button wire:click="viewHistory({{ $memo->id }})" class="text-gray-400 hover:text-purple-600 transition-colors" title="Historique & Workflow">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                        </button>
                                                    @endif

                                                </div>
                                            </td>
                                            
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center text-gray-500">
                                                    <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    <p class="text-lg font-medium text-gray-900">Aucun Mémo Sortant de votre entité pour le moment</p>
                                                    <p class="text-sm">Les mémos sortants de votre entité et qui vous ont été envoyés s'afficheront ici..</p>
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
                @endif

    </div>

    {{-- MODALS --}}
    
     

    @if($isOpenReject)
        <!-- Modal Rejet / Retour -->
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" wire:click="closeRejectModal"></div>
            
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    
                    <!-- Bordure dynamique : rouge pour rejet, orange pour retour -->
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border {{ $reject_mode === 'archive' ? 'border-red-200' : 'border-orange-200' }}">
                        
                        <!-- Header Dynamique -->
                        <div class="{{ $reject_mode === 'archive' ? 'bg-red-50 border-red-100' : 'bg-orange-50 border-orange-100' }} px-4 py-4 border-b flex items-center gap-3">
                            <div class="{{ $reject_mode === 'archive' ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600' }} rounded-full p-2">
                                @if($reject_mode === 'archive')
                                    <!-- Icône Stop/Attention -->
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                @else
                                    <!-- Icône Flèche Retour -->
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 0118 0z"></path></svg>
                                @endif
                            </div>
                            <h3 class="text-lg font-bold {{ $reject_mode === 'archive' ? 'text-red-800' : 'text-orange-800' }}">
                                {{ $reject_mode === 'archive' ? 'Rejeter et Archiver' : 'Retourner pour correction' }}
                            </h3>
                        </div>

                        <div class="p-6">
                            <div class="text-sm text-gray-500 mb-4">
                                @if($reject_mode === 'archive')
                                    <p>Vous êtes sur le point de <span class="font-bold text-red-600">rejeter définitivement</span> ce mémo. Il sera archivé et le circuit sera interrompu.</p>
                                @else
                                    <p>Vous allez renvoyer ce mémo à <span class="font-bold text-orange-600">son initiateur</span>. Il pourra apporter des corrections et  le transmettre à nouveau dans le circuit de validation.</p>
                                @endif
                                <p class="mt-2 font-bold text-gray-700 italic">Cette action nécessite un motif explicite.</p>
                            </div>

                            <!-- Champ Motif -->
                            <div>
                                <label for="reject_reason" class="block text-sm font-bold text-gray-700 mb-1">
                                    Motif du {{ $reject_mode === 'archive' ? 'rejet' : 'retour' }} <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="reject_comment" id="reject_reason" rows="4" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:ring-1 {{ $reject_mode === 'archive' ? 'focus:border-red-500 focus:ring-red-500' : 'focus:border-orange-500 focus:ring-orange-500' }} sm:text-sm placeholder-gray-400" 
                                    placeholder="{{ $reject_mode === 'archive' ? 'Ex: Projet annulé par la direction...' : 'Ex: Veuillez corriger la pièce jointe n°2...' }}"></textarea>
                                @error('reject_comment') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                            <button wire:click="processReject" type="button" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white sm:ml-3 sm:w-auto sm:text-sm transition-colors {{ $reject_mode === 'archive' ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-orange-600 hover:bg-orange-700 focus:ring-orange-500' }}">
                                Confirmer le {{ $reject_mode === 'archive' ? 'Rejet' : 'Retour' }}
                            </button>
                            <button wire:click="closeRejectModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL HISTORIQUE --}}
    @if($isOpenHistory)
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" wire:click="closeHistoryModal"></div>
            
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-200">
                        
                        <div class="bg-gray-50 px-4 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Fil de discussion & Historique global
                            </h3>
                            <button type="button" wire:click="closeHistoryModal" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                        </div>

                        <div class="px-6 py-6 bg-white max-h-[70vh] overflow-y-auto">
                            @forelse($memoHistory as $log)
                                @php
                                    // On vérifie si ce log appartient au mémo d'origine ou à une réponse
                                    $isReply = ($log->memo_id != $this->memo_id);
                                    
                                    // Logique de couleur pour les points de la timeline
                                    $dotColor = 'bg-blue-500'; // Défaut
                                    $visa = Str::lower($log->visa);
                                    if(Str::contains($visa, 'accord') || Str::contains($visa, 'signé')) $dotColor = 'bg-green-500';
                                    if(Str::contains($visa, 'rejeter') || Str::contains($visa, 'clôturé')) $dotColor = 'bg-red-500';
                                    if(Str::contains($visa, 'réponse')) $dotColor = 'bg-purple-600';
                                @endphp

                                <div class="relative pl-8 pb-8 group last:pb-0 border-l-2 border-gray-100 ml-2">
                                    
                                    <!-- Point sur la timeline -->
                                    <div class="absolute -left-[9px] top-0 h-4 w-4 rounded-full border-2 border-white {{ $dotColor }} shadow-sm"></div>

                                    <div class="flex flex-col mb-1">
                                        <div class="flex justify-between items-center">
                                            <!-- Indicateur : Original vs Réponse -->
                                            @if($isReply)
                                                <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded border border-purple-200">
                                                    ↪ Élément de Réponse
                                                </span>
                                            @else
                                                <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded border border-gray-200">
                                                    Mémo Principal
                                                </span>
                                            @endif
                                            <span class="text-[11px] text-gray-400 font-mono">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                        </div>

                                        <h4 class="text-sm font-bold text-gray-900 mt-1">
                                            {{ $log->user->first_name }} {{ $log->user->last_name }}
                                            <span class="text-xs font-normal text-gray-500"> — {{ $log->user->poste }}</span>
                                        </h4>
                                    </div>

                                    <!-- Action / Visa -->
                                    <div class="mb-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold {{ $dotColor }} text-white">
                                            {{ $log->visa }}
                                        </span>
                                        
                                        @if($isReply)
                                            <span class="ml-2 text-[10px] text-gray-400 italic">
                                                (Sur mémo réponse ID #{{ $log->memo_id }})
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Commentaire -->
                                    @if($log->workflow_comment && $log->workflow_comment !== 'R.A.S')
                                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-sm text-gray-600 leading-relaxed shadow-sm">
                                            <p>{{ $log->workflow_comment }}</p>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-10">
                                    <p class="text-gray-400 italic">Aucun historique trouvé pour ce dossier.</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                            <button type="button" wire:click="closeHistoryModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:w-auto sm:text-sm">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    

    @if($isOpen3)
       <!-- Modal Envoi & Workflow -->
       <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" wire:click="closeModalTrois"></div>
            
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    
                    <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-200">
                        <form wire:submit.prevent="sendMemo">
                            
                            <!-- Header -->
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                    Transmission du Mémo
                                </h3>
                                <button type="button" wire:click="closeModalTrois" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            <div class="px-6 py-6 space-y-6">

                                <!-- 1. CHOIX DU TYPE DE CIRCUIT -->
                                <div>
                                    <label class="text-sm font-bold text-gray-700 block mb-2">Type de circuit</label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Option Standard -->
                                        <label class="cursor-pointer relative">
                                            <input type="radio" wire:model.live="memo_type" value="standard" class="peer sr-only">
                                            <div class="p-4 rounded-lg border-2 border-gray-200 hover:border-blue-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition-all text-center h-full flex flex-col items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400 peer-checked:text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                                <span class="block text-sm font-bold text-gray-700 peer-checked:text-blue-800">Standard</span>
                                                <span class="block text-[10px] text-gray-500 mt-1">Vers N+1 uniquement</span>
                                            </div>
                                        </label>

                                        <!-- Option Projet -->
                                        <label class="cursor-pointer relative">
                                            <input type="radio" wire:model.live="memo_type" value="projet" class="peer sr-only">
                                            <div class="p-4 rounded-lg border-2 border-gray-200 hover:border-purple-300 peer-checked:border-purple-600 peer-checked:bg-purple-50 transition-all text-center h-full flex flex-col items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400 peer-checked:text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                <span class="block text-sm font-bold text-gray-700 peer-checked:text-purple-800">Mode Projet</span>
                                                <span class="block text-[10px] text-gray-500 mt-1">Multi-collaborateurs (hors N+1)</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <hr class="border-gray-100">

                                <!-- ZONE D'AFFICHAGE DYNAMIQUE SELON LE TYPE -->
                                <div class="min-h-[100px]">
                                    
                                    <!-- A. AFFICHAGE STANDARD (N+1) -->
                                    @if($memo_type === 'standard')
                                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-100 animate-fade-in-down">
                                            
                                            @if($isSecretary || (auth()->user()->poste == 'Directeur' && !auth()->user()->manager_id))
                                                <!-- CAS : Secrétaire OU Directeur sans Manager (Liste de sélection) -->
                                                <p class="text-xs font-bold text-blue-500 uppercase mb-3">
                                                    {{ auth()->user()->poste == 'Directeur' ? 'Transmettre aux Secrétariats' : 'Destinataire(s) du circuit standard' }}
                                                </p>
                                                
                                                <select wire:model.live="selected_standard_users" multiple class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-32">
                                                    @foreach($standardRecipientsList as $uData)
                                                        <option value="{{ $uData['original']->id }}">
                                                            {{ $uData['original']->first_name }} {{ $uData['original']->last_name }} 
                                                            ({{ $uData['original']->poste }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <p class="mt-2 text-[10px] text-blue-400 italic">Maintenez Ctrl pour sélectionner plusieurs personnes.</p>
                                                @error('selected_standard_users') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                                            @elseif($managerData)
                                                <!-- CAS : Manager Direct classique -->
                                                <p class="text-xs font-bold text-blue-500 uppercase mb-3">Destinataire Final (N+1)</p>
                                                <div class="flex items-start">
                                                    <div class="flex-shrink-0">
                                                        <div class="h-10 w-10 rounded-full {{ $managerData['is_replaced'] ? 'bg-orange-200 text-orange-700' : 'bg-blue-200 text-blue-700' }} flex items-center justify-center font-bold shadow-sm">
                                                            {{ substr($managerData['effective']->first_name, 0, 1) }}{{ substr($managerData['effective']->last_name, 0, 1) }}
                                                        </div>
                                                    </div>
                                                    <div class="ml-3 flex-1">
                                                        <p class="text-sm font-bold text-gray-900">
                                                            {{ $managerData['effective']->first_name }} {{ $managerData['effective']->last_name }}
                                                        </p>
                                                        @if($managerData['is_replaced'])
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 mb-1">Intérimaire / Remplaçant</span>
                                                            <p class="text-xs text-gray-500 italic">Remplace {{ $managerData['original']->first_name }} {{ $managerData['original']->last_name }}</p>
                                                        @else
                                                            <p class="text-xs text-gray-500">{{ $managerData['original']->poste }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <!-- CAS : Erreur / Aucun destinataire -->
                                                <div class="text-red-500 text-sm bg-red-50 p-3 rounded border border-red-100 flex items-center gap-2">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    Aucun destinataire ou manager configuré pour votre compte.
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- B. AFFICHAGE PROJET (LISTE SANS N+1) -->
                                    @if($memo_type === 'projet')
                                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-100 animate-fade-in-down">
                                            <p class="text-xs font-bold text-purple-600 uppercase mb-2">Collaborateurs du projet</p>
                                            
                                            <label class="block text-xs text-gray-500 mb-1">Sélectionnez les destinataires (Maintenez Ctrl pour plusieurs)</label>
                                            
                                            <select wire:model.live="selected_project_users" multiple class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm h-32">
                                                @foreach($projectUsersList as $userData)
                                                    <option value="{{ $userData['original']->id }}">
                                                        {{ $userData['original']->first_name }} {{ $userData['original']->last_name }} 
                                                        ({{ $userData['original']->departement ?? 'N/A' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('selected_project_users') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                                            <!-- Récapitulatif avec alertes remplacements -->
                                            @if(!empty($selected_project_users))
                                                <div class="mt-3 space-y-2 max-h-32 overflow-y-auto">
                                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Destinataires réels :</p>
                                                    
                                                    @foreach($selected_project_users as $selectedId)
                                                        @php
                                                            // On retrouve les données dans la collection préparée
                                                            $uInfo = $projectUsersList->first(function($item) use ($selectedId) {
                                                                return $item['original']->id == $selectedId;
                                                            });
                                                        @endphp

                                                        @if($uInfo)
                                                            <div class="flex items-center justify-between bg-white p-2 rounded border {{ $uInfo['is_replaced'] ? 'border-yellow-300 bg-yellow-50' : 'border-gray-200' }}">
                                                                <div class="flex items-center gap-2">
                                                                    <div class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold">
                                                                        {{ substr($uInfo['original']->first_name, 0, 1) }}
                                                                    </div>
                                                                    <span class="text-xs font-medium text-gray-700">
                                                                        {{ $uInfo['original']->first_name }} {{ $uInfo['original']->last_name }}
                                                                    </span>
                                                                </div>

                                                                @if($uInfo['is_replaced'])
                                                                    <div class="text-[10px] text-right">
                                                                        <span class="text-yellow-600 font-bold flex items-center gap-1">
                                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                                                            Remplacé par
                                                                        </span>
                                                                        <span class="text-gray-900">{{ $uInfo['effective']->first_name }} {{ $uInfo['effective']->last_name }}</span>
                                                                    </div>
                                                                @else
                                                                    <span class="text-[10px] text-green-600 bg-green-50 px-1.5 py-0.5 rounded">Dispo</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                </div>

                                <!-- VISA & COMMENTAIRE (Commun) -->
                                <div class="space-y-4 pt-2">
                                    <hr class="border-gray-100">
                                    
                                    <!-- Visa -->
                                    

                                    <!-- Commentaire -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire</label>
                                        <textarea wire:model="workflow_comment" rows="2" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md placeholder-gray-400" placeholder="Note optionnelle..."></textarea>
                                        @error('workflow_comment') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Erreur Générale -->
                                @error('general')
                                    <div class="bg-red-50 border-l-4 border-red-500 p-4">
                                        <p class="text-sm text-red-700">{{ $message }}</p>
                                    </div>
                                @enderror

                            </div>

                            <!-- Footer Actions -->
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                                <button type="submit" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm">
                                    <span wire:loading.remove>Transmettre</span>
                                    <span wire:loading>Envoi...</span>
                                </button>
                                <button type="button" wire:click="closeModalTrois" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
       <!-- Modal Suppression -->
       <div class="relative z-50" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-red-600 mb-2">Supprimer le brouillon ?</h3>
                        <p class="text-gray-500 mb-6">Cette action est irréversible.</p>
                        <div class="flex justify-end gap-3">
                             <button wire:click="closeModalQuatre" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">Annuler</button>
                             <button wire:click="del" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Supprimer</button>
                        </div>
                    </div>
                </div>
            </div>
       </div>
    @endif

    @if($isOpenTrans)
        <!-- Modal Transmission Secrétaire -->
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" wire:click="closeTransModal"></div>
                
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        
                        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-indigo-200">
                            
                            <!-- Header -->
                            <div class="bg-indigo-50 px-4 py-4 border-b border-indigo-100 flex items-center gap-3">
                                <div class="bg-indigo-100 rounded-full p-2">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                </div>
                                <h3 class="text-lg font-bold text-indigo-800">Enregistrement</h3>
                            </div>

                            <div class="p-6 space-y-6">
                                
                                <!-- SECTION 1 : RÉFÉRENCE EDITABLE (NOUVEAU) -->
                                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                                    <label class="block text-sm font-bold text-yellow-800 mb-2">
                                        Référence Générée (Chrono)
                                    </label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <span class="text-gray-500 sm:text-sm">Ref:</span>
                                        </div>
                                        <input type="text" 
                                            wire:model="generatedReference" 
                                            class="block w-full rounded-md border-yellow-400 pl-10 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 font-mono font-bold text-gray-900 bg-white" 
                                            placeholder="0000/...">
                                    </div>
                                    <p class="mt-2 text-xs text-yellow-700">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Vous pouvez modifier cette référence manuellement si nécessaire.
                                    </p>
                                    @error('generatedReference') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                                </div>

                                <!-- SECTION 2 : DESTINATAIRES -->
                                <div>
                                    <p class="text-sm text-gray-500 mb-2">Transmission aux secrétariats :</p>
                                    <div class="bg-gray-50 rounded-lg p-3 max-h-32 overflow-y-auto border border-gray-200">
                                        @if(!empty($transRecipients))
                                            <ul class="divide-y divide-gray-200">
                                                @foreach($transRecipients as $recipient)
                                                    <li class="py-2 flex items-center justify-between">
                                                        <div class="flex items-center">
                                                            <div class="h-6 w-6 rounded-full bg-indigo-200 flex items-center justify-center text-[10px] font-bold text-indigo-700 mr-2">
                                                                {{ substr($recipient['effective']->first_name, 0, 1) }}
                                                            </div>
                                                            <div>
                                                                <p class="text-xs font-bold text-gray-800">
                                                                    {{ $recipient['effective']->first_name }} {{ $recipient['effective']->last_name }}
                                                                </p>
                                                                <p class="text-[9px] text-gray-500">
                                                                    {{ $recipient['original']->entity->ref ?? 'Entité' }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <i class="fas fa-check text-green-500 text-xs"></i>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>

                            </div>

                            <!-- Footer Actions -->
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                                <button wire:click="confirmTrans" wire:loading.attr="disabled" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                    <span wire:loading.remove>Valider & Transmettre</span>
                                    <span wire:loading>Traitement...</span>
                                </button>
                                <button wire:click="closeTransModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                    Annuler
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    @endif

</div>
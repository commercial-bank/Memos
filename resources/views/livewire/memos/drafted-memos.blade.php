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
            <!-- VUE 2 : ÉDITION (TON DESIGN ORIGINAL)      -->
            <!-- ========================================== -->
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in-up">
            
            <!-- Barre d'actions -->
            <div class="mb-8 bg-white border border-gray-100 rounded-xl shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-all duration-300">
                
                <button wire:click="cancelEdit" type="button" class="group flex items-center text-gray-500 hover:text-black transition-colors">
                    <div class="mr-3 h-10 w-10 rounded-full bg-gray-100 group-hover:bg-[#daaf2c]/20 group-hover:text-[#daaf2c] flex items-center justify-center transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col items-start">
                        <span class="font-bold text-base text-black">Retour</span>
                        <span class="text-xs font-normal" style="color: var(--c-grey);">Vers la liste</span>
                    </div>
                </button>
                
                <div class="flex items-center justify-end space-x-3 w-full sm:w-auto border-t sm:border-t-0 border-gray-100 pt-3 sm:pt-0">
                    <button wire:click="cancelEdit" type="button" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200">
                        Annuler
                    </button>
                    
                    <button wire:click="save" wire:loading.attr="disabled" type="button" 
                        class="relative inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-lg shadow-md text-black transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#daaf2c]"
                        style="background-color: var(--c-gold);">
                        
                        <span wire:loading.remove wire:target="save" class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Enregistrer
                        </span>

                        <span wire:loading wire:target="save" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Traitement...
                        </span>
                    </button>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-md bg-red-50 p-4 border-l-4 border-red-500 shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Attention</h3>
                            <ul class="mt-2 list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="text-sm text-red-700">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- FEUILLE DE PAPIER -->
            <div class="bg-white rounded-lg shadow-2xl overflow-hidden relative" style="border: 1px solid #e5e7eb;">
                
                <div class="px-8 py-6 flex justify-between items-center" 
                     style="background-color: var(--c-black); color: white; border-bottom: 4px solid var(--c-gold);">
                    <div>
                        <h2 class="text-2xl font-bold tracking-wider uppercase" style="font-family: 'Times New Roman', serif;">Mémorandum</h2>
                        <p class="text-xs font-bold tracking-widest mt-1" style="color: var(--c-gold);">INTERNE / CONFIDENTIEL</p>
                    </div>
                    <div class="text-right opacity-80">
                        <p class="text-sm">Date : {{ now()->format('d/m/Y') }}</p>
                    </div>
                </div>

                <div class="p-8 md:p-12 space-y-10">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-2">
                            <label for="concern" class="block text-xs font-bold uppercase tracking-wide mb-1" style="color: var(--c-grey);">Pour (Concerne)</label>
                            <input type="text" wire:model="concern" id="concern" 
                                class="block w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-black placeholder-gray-300 sm:text-lg transition-colors focus:ring-0 focus:border-[#daaf2c]" 
                                placeholder="Ex: Direction Générale...">
                            @error('concern') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="object" class="block text-xs font-bold uppercase tracking-wide mb-1" style="color: var(--c-grey);">Objet</label>
                            <input type="text" wire:model="object" id="object" 
                                class="block w-full border-0 border-b-2 border-gray-200 bg-transparent py-2 px-0 text-black font-bold placeholder-gray-300 sm:text-lg transition-colors focus:ring-0 focus:border-[#daaf2c]" 
                                placeholder="Sujet principal...">
                            @error('object') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- DISTRIBUTION LIST -->
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <h3 class="text-sm font-bold uppercase tracking-wide mb-4 flex items-center" style="color: var(--c-black);">
                            <svg class="w-4 h-4 mr-2" style="color: var(--c-gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            Liste de Distribution
                        </h3>

                        <div class="flex flex-col md:flex-row gap-4 mb-4">
                            <div class="flex-1">
                                <select wire:model="newRecipientEntity" class="block w-full rounded-md border-gray-300 shadow-sm focus-gold text-sm">
                                    <option value="">-- Sélectionner un destinataire --</option>
                                    @foreach($entities as $entity)
                                        <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                                    @endforeach
                                </select>
                                @error('newRecipientEntity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex-1">
                                <select wire:model="newRecipientAction" class="block w-full rounded-md border-gray-300 shadow-sm focus-gold text-sm">
                                    <option value="">-- Action requise --</option>
                                    @foreach($actionsList as $action)
                                        <option value="{{ $action }}">{{ $action }}</option>
                                    @endforeach
                                </select>
                                @error('newRecipientAction') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <button wire:click="addRecipient" type="button" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Ajouter
                            </button>
                        </div>

                        @if(count($recipients) > 0)
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300 bg-white">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="py-2 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase">Entité</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Action</th>
                                            <th class="relative py-2 pl-3 pr-4 sm:pr-6"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($recipients as $index => $recipient)
                                            <tr>
                                                <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm font-medium text-gray-900">{{ $recipient['entity_name'] }}</td>
                                                <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-500">
                                                    <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-black ring-1 ring-inset ring-[#daaf2c]/50">
                                                        {{ $recipient['action'] }}
                                                    </span>
                                                </td>
                                                <td class="relative whitespace-nowrap py-3 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                    <button wire:click="removeRecipient({{ $index }})" class="text-gray-400 hover:text-red-600">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
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

                <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 flex justify-between items-center text-xs text-gray-500">
                    <span>Auteur: <strong class="text-gray-900">{{ Auth::user()->name }}</strong></span>
                    <span>Document généré par le système</span>
                </div>
            </div>

        @else
            <!-- ========================================== -->
            <!-- VUE 1 : LISTE (TON DESIGN ORIGINAL)        -->
            <!-- ========================================== -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Mes Mémos</h2>
                    <p class="text-sm text-gray-500">Gérez vos mémos en attente d'envoi.</p>
                </div>
                <div class="relative w-full md:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm shadow-sm" placeholder="Rechercher par objet, concerné...">
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Objet & Concerne</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destinataires</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pièces Jointes</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($memos as $memo)
                                <tr class="hover:bg-gray-50 transition-colors duration-150 group">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex flex-col"><span class="font-medium text-gray-900">{{ $memo->created_at->format('d/m/Y') }}</span><span class="text-xs">{{ $memo->created_at->format('H:i') }}</span></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col max-w-xs md:max-w-md"><span class="text-sm font-bold text-gray-800 truncate">{{ $memo->object }}</span><span class="text-xs text-gray-500 truncate mt-1">Concerne: {{ $memo->concern }}</span></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($memo->destinataires->take(3) as $dest)
                                                @php $badgeClasses = Str::contains(Str::lower($dest->action), 'nécessaire') ? 'bg-orange-100 text-orange-800 border border-orange-200' : 'bg-blue-100 text-blue-800 border border-blue-200'; @endphp
                                                <div class="inline-flex flex-col items-start px-2.5 py-1 rounded-md text-xs font-medium {{ $badgeClasses }}"><span class="font-bold">{{ $dest->entity->ref ?? 'N/A' }}</span><span class="text-[10px] opacity-80 leading-tight">{{ Str::limit($dest->action, 15) }}</span></div>
                                            @endforeach
                                            @if($memo->destinataires->count() > 3) <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">+{{ $memo->destinataires->count() - 3 }}</span> @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" x-data="{ openFiles: false }">
                                        @php $pj = is_string($memo->pieces_jointes) ? json_decode($memo->pieces_jointes, true) : ($memo->pieces_jointes ?? []); @endphp
                                        @if(count($pj) > 0)
                                            <button @click="openFiles = true" type="button" class="flex items-center space-x-1 text-gray-600 hover:text-blue-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg><span class="text-sm font-bold">{{ count($pj) }}</span></button>
                                            <div x-show="openFiles" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm" @click.self="openFiles = false">
                                                <div class="bg-white rounded-lg shadow-2xl w-80 max-w-sm mx-4 overflow-hidden border border-gray-100">
                                                    <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center"><h3 class="text-sm font-bold text-gray-700">Pièces Jointes</h3><button @click="openFiles = false"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button></div>
                                                    <div class="max-h-64 overflow-y-auto p-2">
                                                        <ul class="space-y-1">
                                                            @foreach($pj as $file)
                                                                <li><a href="{{ Storage::url(is_string($file) ? $file : ($file['path'] ?? '')) }}" target="_blank" class="flex items-center p-2 hover:bg-blue-50 rounded-md text-sm text-gray-700"><div class="bg-blue-100 p-1.5 rounded-md mr-3 text-blue-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div><div class="flex-1 min-w-0"><p class="truncate font-medium">{{ basename(is_string($file) ? $file : ($file['path'] ?? '')) }}</p></div></a></li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        @else <span class="text-gray-300 text-xs">-</span> @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex items-center justify-center space-x-3">
                                            <button wire:click="viewMemo({{ $memo->id }})" class="text-gray-400 hover:text-blue-600" title="Aperçu Réel PDF"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></button>
                                            <button wire:click="editMemo({{ $memo->id }})" class="text-gray-400 hover:text-yellow-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                                            <button wire:click="assignMemo({{ $memo->id }})" class="text-gray-400 hover:text-green-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg></button>
                                            <button wire:click="deleteMemo({{ $memo->id }})" class="text-gray-400 hover:text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <!-- Icône Crayon / Rédaction (Heroicons) -->
                                            <div class="bg-yellow-50 p-4 rounded-full mb-4">
                                                <svg class="h-12 w-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </div>
                                            
                                            <p class="text-lg font-bold text-gray-800">Aucun Mémo Rédigé</p>
                                            <p class="text-sm text-gray-500 max-w-xs mx-auto mt-1">
                                                Vous n'avez pas encore initié de mémo. Les mémos que vous créerez s'afficheront ici.
                                            </p>
                                            
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">{{ $memos->links() }}</div>
            </div>
        @endif
    </div>

    <!-- STYLES QUILL & ANIMATIONS -->
    <style>
        .ql-container.ql-snow { border: none !important; }
        .ql-editor { padding: 50px 70px !important; min-height: 29.7cm; font-family: 'Tahoma', sans-serif; font-size: 14px; line-height: 1.5; }
        .ql-font-tahoma { font-family: 'Tahoma', sans-serif !important; }
        .ql-font-timesnewroman { font-family: 'Times New Roman', serif !important; }
        .ql-font-arial { font-family: 'Arial', sans-serif !important; }
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    

    {{-- MODALS (Transmission / Suppression) --}}
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
                                            
                                            @if($isSecretary)
                                                <!-- CAS 1 : L'UTILISATEUR EST UNE SECRÉTAIRE (Sélection multiple dans la hiérarchie) -->
                                                <p class="text-xs font-bold text-blue-500 uppercase mb-3">
                                                    Hiérarchie de l'Entité (Sélectionnez le(s) destinataire(s))
                                                </p>

                                                <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                                                    @forelse($standardRecipientsList as $recipient)
                                                        <label class="relative flex items-start p-3 rounded-lg bg-white border border-gray-100 cursor-pointer hover:border-blue-300 hover:bg-blue-50/50 transition-all group">
                                                            <div class="flex items-center h-5">
                                                                <input type="checkbox" wire:model="selected_standard_users" value="{{ $recipient['original']->id }}" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                            </div>
                                                            <div class="ml-3 flex-1">
                                                                <div class="flex items-center justify-between">
                                                                    <span class="text-sm font-bold text-gray-900 group-hover:text-blue-700">
                                                                        {{ $recipient['original']->first_name }} {{ $recipient['original']->last_name }}
                                                                    </span>
                                                                    @if($recipient['is_replaced'])
                                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-700 uppercase">
                                                                            Remplacé
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                <p class="text-[11px] text-gray-500">{{ $recipient['original']->poste }}</p>
                                                                
                                                                @if($recipient['is_replaced'])
                                                                    <div class="mt-1 flex items-center gap-1 text-[10px] text-gray-400 italic">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                                        Délégué à : {{ $recipient['effective']->first_name }} {{ $recipient['effective']->last_name }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </label>
                                                    @empty
                                                        <p class="text-xs text-gray-500 italic">Aucun responsable hiérarchique trouvé.</p>
                                                    @endforelse
                                                </div>
                                                @error('selected_standard_users') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                                            @else
                                                <!-- CAS 2 : UTILISATEUR CLASSIQUE (Affichage fixe du N+1) -->
                                                <p class="text-xs font-bold text-blue-500 uppercase mb-3">
                                                    Destinataire Final (N+1)
                                                </p>
                                                
                                                @if($managerData)
                                                    <div class="flex items-start">
                                                        <!-- AVATAR -->
                                                        <div class="flex-shrink-0">
                                                            <div class="h-10 w-10 rounded-full {{ $managerData['is_replaced'] ? 'bg-orange-200 text-orange-700' : 'bg-blue-200 text-blue-700' }} flex items-center justify-center font-bold shadow-sm">
                                                                {{ substr($managerData['effective']->first_name, 0, 1) }}{{ substr($managerData['effective']->last_name, 0, 1) }}
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="ml-3 flex-1">
                                                            <!-- NOM -->
                                                            <p class="text-sm font-bold text-gray-900">
                                                                {{ $managerData['effective']->first_name }} {{ $managerData['effective']->last_name }}
                                                            </p>
                                                            
                                                            <!-- CONTEXTE -->
                                                            @if($managerData['is_replaced'])
                                                                <div class="flex flex-col mt-1">
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 w-fit mb-1">
                                                                        Intérimaire / Remplaçant
                                                                    </span>
                                                                    <p class="text-xs text-gray-500 flex items-center gap-1">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                                                        Remplace <span class="font-semibold">{{ $managerData['original']->first_name }} {{ $managerData['original']->last_name }}</span> (Absent)
                                                                    </p>
                                                                </div>
                                                            @else
                                                                <p class="text-xs text-gray-500">{{ $managerData['original']->poste ?? 'Supérieur Hiérarchique' }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="flex items-center gap-2 text-red-500 bg-red-50 p-3 rounded border border-red-100">
                                                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        <div class="text-sm">
                                                            <span class="font-bold">Erreur :</span> Aucun manager n'est associé à votre compte.
                                                        </div>
                                                    </div>
                                                @endif
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
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Votre Visa / Action</label>
                                        <div class="grid grid-cols-3 gap-3">
                                            @foreach(['Vu' => 'gray', 'Vu & Accord' => 'green', "Vu & Pas d'accord" => 'red'] as $visa => $color)
                                                <label class="cursor-pointer">
                                                    <input type="radio" wire:model="selected_visa" value="{{ $visa }}" class="peer sr-only">
                                                    <div class="rounded-md border border-gray-200 p-2 hover:bg-{{ $color }}-50 peer-checked:border-{{ $color }}-500 peer-checked:bg-{{ $color }}-50 peer-checked:ring-1 peer-checked:ring-{{ $color }}-500 transition-all text-center">
                                                        <span class="text-xs font-medium text-{{ $color }}-900">{{ $visa }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('selected_visa') <span class="text-red-500 text-xs mt-1">Le visa est obligatoire.</span> @enderror
                                    </div>

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
       <!-- MODAL SUPPRESSION (Garder ton design original) -->
       <div class="relative z-[150]" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4 text-center">
                <div class="relative bg-white rounded-lg shadow-xl sm:w-full sm:max-w-lg p-6">
                    <h3 class="text-lg font-bold text-red-600 mb-2">Supprimer le brouillon ?</h3>
                    <p class="text-gray-500 mb-6">Cette action est irréversible.</p>
                    <div class="flex justify-end gap-3"><button wire:click="closeModalQuatre" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">Annuler</button><button wire:click="del" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-bold">Supprimer</button></div>
                </div>
            </div>
       </div>
    @endif

</div>
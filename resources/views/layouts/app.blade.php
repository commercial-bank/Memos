<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/zonetext.js','resources/css/sidebar.css','resources/css/navbar.css','resources/css/dashcard.css'])
        @livewireStyles
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <!-- Style de l'éditeur -->
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
        <!-- Script de l'éditeur -->
        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    async function prepareAndDownloadPDF() {
        const container = document.getElementById('export-container');
        const page1 = document.getElementById('page-1');
        const contentArea = document.querySelector('#content-area > div'); // La div qui contient les <p>
        const signatures = document.getElementById('signatures-section');
        const goldFrame = page1.querySelector('.gold-frame');

        // 1. Nettoyage préventif (au cas où on clique 2 fois)
        const existingPage2 = document.getElementById('page-2');
        if (existingPage2) existingPage2.remove();

        // 2. Calcul de la limite de hauteur (Bas du cadre doré - marge signature)
        // Hauteur A4 ~ 1122px (96dpi). Cadre doré ~ 1050px.
        // On définit une ligne de flottaison sûre.
        const SAFE_HEIGHT = 1020; 

        // 3. Vérification : Est-ce que ça dépasse ?
        // scrollHeight donne la hauteur totale réelle du contenu
        if (goldFrame.scrollHeight > 1100) { // Si le contenu dépasse la hauteur d'une page A4
            
            console.log("Détection de dépassement. Création de la Page 2...");

            // --- CRÉATION PAGE 2 ---
            let page2 = document.createElement('div');
            page2.id = 'page-2';
            // Mêmes classes que Page 1 + marge top pour l'espace visuel
            page2.className = "page-a4 bg-white w-[210mm] h-[297mm] shadow-2xl p-[10mm] text-black text-[13px] leading-snug relative text-left mx-auto mt-10";
            
            // Structure HTML Page 2 (Cadre + Header SIMPLIFIÉ sans tableau)
            page2.innerHTML = `
                <div class="border-[3px] border-[#D4AF37] rounded-tr-[60px] rounded-bl-[60px] p-8 h-full flex flex-col relative">
                    
                    <!-- HEADER RÉPÉTÉ (Logo + Titre uniquement) -->
                    <div class="flex flex-col items-center justify-center mb-6 text-center">
                        <div class="mb-2">
                            <div class="w-17 h-16 flex items-center justify-center mx-auto mb-1">
                                <img src="{{ asset('images/logo.jpg') }}" alt="logo" class="w-full h-full object-contain">
                            </div>
                        </div>
                        <h1 class="font-['Arial'] font-extrabold text-2xl uppercase mt-2 italic inline-block">
                            Memorandum
                        </h1>
                    </div>

                    <!-- Indication suite -->
                    <div class="text-xs text-gray-400 text-center italic mb-4">(Suite)</div>

                    <!-- CONTENEUR POUR LE TEXTE DÉPLACÉ -->
                    <div id="content-page-2" class="flex-grow px-2 text-justify space-y-3 text-[14px] leading-relaxed font-serif text-gray-900">
                    fgfgfgfgfgfgf
                    </div>

                    <!-- CONTENEUR POUR SIGNATURES -->
                    <div id="signatures-page-2" class="mt-auto">fggfgfgfgfgfg</div>
                </div>
            `;

            container.appendChild(page2);

            // --- DÉPLACEMENT DU CONTENU ---
            const children = Array.from(contentArea.children); // Les paragraphes <p>
            const destContent = page2.querySelector('#content-page-2');
            let move = false;
            
            // On calcule la position du bas du cadre Page 1
            const page1Rect = goldFrame.getBoundingClientRect();
            // Limite visuelle (bas de page - place pour signature si elle restait là)
            const limit = page1Rect.top + SAFE_HEIGHT; 

            children.forEach(child => {
                const rect = child.getBoundingClientRect();
                
                // Si on a déjà commencé à déplacer OU si cet élément dépasse la limite
                if (move || rect.bottom > limit) {
                    move = true;
                    contentArea.removeChild(child); // Enlever de page 1
                    destContent.appendChild(child); // Mettre dans page 2
                }
            });

            // --- DÉPLACEMENT DES SIGNATURES ---
            // Les signatures vont TOUJOURS à la fin, donc sur la Page 2
            signatures.remove();
            page2.querySelector('#signatures-page-2').appendChild(signatures);
        }

        // 4. GÉNÉRATION PDF
        // Petite pause pour que le DOM se mette à jour
        await new Promise(r => setTimeout(r, 500));

        const element = document.getElementById('export-container');
        const opt = {
            margin:       0,
            filename:     'Memorandum.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, letterRendering: true },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak:    { mode: ['css', 'legacy'] } // css permet de respecter les div séparées
        };

        html2pdf().set(opt).from(element).save().then(() => {
            // Optionnel : Recharger la page ou fermer la modale après téléchargement
            // location.reload(); 
        });
    }
</script>
    </head>
    <body class="font-sans antialiased">

        {{ $slot }}

        <!-- Système de Notification Flash (Toast) -->
        <div 
            x-data="{ show: false, message: '' }"
            x-on:notify.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 5000)"
            class="fixed bottom-5 right-5 z-50"
            style="display: none;"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
        >
            <div class="bg-green-500 text-white px-6 py-4 rounded shadow-lg flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span x-text="message"></span>
            </div>
        </div> 
        
     @livewireScripts   
    </body>
</html>

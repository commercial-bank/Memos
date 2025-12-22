<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- =========================================================
         MÉTA-DONNÉES & RÉFÉRENCEMENT
         ========================================================= -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CBC_WorkFlow_Docs') }}</title>

    <!-- FAVICON -->
    <link rel="icon" href="{{ asset('images/lo.png') }}?v=3" type="image/jpg">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

    <!-- =========================================================
         RESSOURCES EXTERNES (FONTS & ICONS)
         ========================================================= -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- === CSS QUILL 2.0.2 === -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />

    <!-- =========================================================
         STYLES PERSONNALISÉS (CSS)
         ========================================================= -->
    <style>
       
    .page-a4 {
        width: 210mm;
        height: 297mm;
        min-height: 297mm;
        max-height: 297mm;
        padding: 10mm;
        background: white;
        margin: 0 auto 20px auto; /* Espace entre les pages à l'écran */
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
        position: relative;
        overflow: hidden; /* Empêche le contenu de déborder visuellement */
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
    }

    /* Le cadre doré doit s'adapter à la flexbox */
    .gold-frame {
        flex: 1; /* Prend toute la place restante */
        display: flex;
        flex-direction: column;
        border: 3px solid #D4AF37;
        border-radius: 0 60px 0 60px;
        padding: 15mm; /* Marge interne au cadre */
        box-sizing: border-box;
    }

    .content-target {
        flex-grow: 1; /* Pousse le footer vers le bas */
        overflow: hidden; /* Important pour le calcul JS */
        font-family: 'Times New Roman', Times, serif;
        font-size: 12pt;
        line-height: 1.5;
        text-align: justify;
    }

    .footer-section {
        margin-top: auto; /* Force le collage en bas */
        width: 100%;
    }

    @media print {
        body { background: none; }
        .page-a4 {
            margin: 0;
            box-shadow: none;
            page-break-after: always;
        }
        /* Cacher la toolbar et les boutons au moment de l'impression */
        .print-hidden { display: none !important; }
    }
</style>


    <style>
    /* Simulation des pages A4 dans le navigateur */
    .memo-page {
        width: 210mm;
        height: 297mm;
        padding: 10mm;
        margin: 10px auto;
        background: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
        position: relative;
        overflow: hidden; /* Important : cache ce qui dépasse avant le split */
        box-sizing: border-box;
    }

    /* Le cadre doré (doit être sur chaque page) */
    .gold-frame-page {
        border: 3px solid #D4AF37;
        border-radius: 0 60px 0 60px;
        padding: 20mm;
        height: 100%;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        position: relative;
    }

    .content-flow {
        font-family: serif;
        font-size: 14px;
        line-height: 1.6;
        text-align: justify;
        color: #1a1a1a;
    }

    @media print {
        .memo-page { margin: 0; box-shadow: none; page-break-after: always; }
        .no-print { display: none; }
    }
</style>

<style>
    /* Assurez-vous que ces styles sont présents */
.page-a4 {
    width: 210mm;
    height: 297mm;
    min-height: 297mm;
    max-height: 297mm;
    overflow: hidden; /* Important pour que scrollHeight dépasse clientHeight */
    position: relative;
    background: white;
    margin-bottom: 20px;
    box-sizing: border-box;
    display: block; /* Évitez flex ici pour la racine de la page */
}

.gold-frame {
    /* Retirez h-full si présent ou forcez la hauteur fixe */
    height: 277mm; /* 297mm - les marges de 10mm haut/bas */
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
}

.content-target {
    /* Ne pas mettre overflow-hidden ici pendant le calcul, 
       sinon le parent ne verra pas le débordement */
    flex-grow: 1;
    text-align: justify;
    word-break: break-word;
}

</style>

    <!-- Vite Resources (Styles) -->
    @vite([
        'resources/css/app.css', 
        'resources/css/sidebar.css',
        'resources/css/navbar.css',
        'resources/css/dashcard.css',
        'resources/css/theme.css',
        'resources/css/previewA4.css'
    ])

    @livewireStyles
</head>

<body class="font-sans antialiased">

    <!-- CONTENU PRINCIPAL -->
    {{ $slot }}

    <!-- =========================================================
         COMPOSANTS UI (TOAST NOTIFICATIONS)
         ========================================================= -->
    <div 
        x-data="{ show: false, message: '' }"
        x-on:notify.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 5000)"
        class="fixed bottom-5 right-5 z-50"
        style="display: none;"
        x-show="show"
        x-transition.duration.300ms
    >
        <div class="bg-green-500 text-white px-6 py-4 rounded shadow-lg flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span x-text="message"></span>
        </div>
    </div> 

    <!-- =========================================================
         SCRIPTS ET LIBRAIRIES
         ========================================================= -->
    
    <!-- Scripts Livewire -->
    @livewireScripts   

    <!-- Librairies Tierces -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Quill JS (Version 2.0.2) -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    
    <!-- Vite Resources (JS) -->
    @vite(['resources/js/app.js'])

    <!-- SCRIPT DE GÉNÉRATION PDF -->
    <script>
        function paginateMemo() {
    const container = document.getElementById('pages-container');
    const sourceContent = document.getElementById('source-content');
    const maxHeight = 650; // Hauteur maximale du contenu sur la page 1 (en px)
    const maxNextHeight = 850; // Hauteur maximale sur les pages suivantes (plus d'espace car pas de tableau)

    // On récupère tous les éléments enfants du contenu (p, h1, li, etc.)
    const elements = Array.from(sourceContent.children);
    sourceContent.innerHTML = ''; // On vide le conteneur source

    let currentPageNum = 1;
    let currentPageContent = document.getElementById('content-page-1');

    elements.forEach((el) => {
        currentPageContent.appendChild(el);

        // On vérifie si on dépasse la hauteur autorisée
        let currentLimit = (currentPageNum === 1) ? maxHeight : maxNextHeight;

        if (currentPageContent.offsetHeight > currentLimit) {
            // Si on dépasse, on crée une nouvelle page
            currentPageNum++;
            
            // Création de la structure de la nouvelle page (Copie de la Page 1 sans le tableau)
            const newPage = createNewPage(currentPageNum);
            container.appendChild(newPage);
            
            // On déplace l'élément qui a fait déborder vers la nouvelle page
            currentPageContent = newPage.querySelector('.content-area-dynamic');
            currentPageContent.appendChild(el);
        }
    });
}

function createNewPage(num) {
    const template = document.getElementById('page-template');
    const clone = template.content.cloneNode(true);
    clone.querySelector('.page-number').innerText = "Page " + num;
    return clone;
}
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
    Alpine.data('memoPagination', () => ({
        init() {
            // On attend que Quill et le contenu initial soient bien chargés
            setTimeout(() => {
                this.paginate();
            }, 300);
        },

        paginate() {
            const source = this.$refs.rawContent;
            const container = this.$refs.pagesContainer;
            const template = document.getElementById('page-template');

            if (!source || !container || !template) return;

            // Nettoyage du conteneur avant génération
            container.innerHTML = '';

            let pageNum = 1;
            // Création de la première page (avec tout : tableau, objet, etc.)
            let currentPage = this.createPage(template, pageNum, true);
            container.appendChild(currentPage);

            let currentTarget = currentPage.querySelector('.content-target');
            
            // On convertit les enfants du contenu brut en tableau
            const nodes = Array.from(source.children);

            nodes.forEach((node) => {
                const clone = node.cloneNode(true);
                currentTarget.appendChild(clone);

                /**
                 * LOGIQUE DE DÉBORDEMENT
                 * On compare la hauteur totale du contenu (scrollHeight) 
                 * à la hauteur fixe autorisée de la page (clientHeight).
                 */
                if (currentPage.scrollHeight > currentPage.clientHeight) {
                    // Si ça dépasse, on retire le dernier élément ajouté
                    currentTarget.removeChild(clone);

                    // On crée une nouvelle page (Page 2, 3...)
                    pageNum++;
                    currentPage = this.createPage(template, pageNum, false);
                    container.appendChild(currentPage);

                    // La nouvelle cible est le content-target de la nouvelle page
                    currentTarget = currentPage.querySelector('.content-target');
                    currentTarget.appendChild(clone);
                }
            });
            
            // Mise à jour finale des numéros de page si nécessaire
            this.updatePageNumbers(container);
        },

        createPage(template, num, isFirst) {
            const fragment = template.content.cloneNode(true);
            const pageDiv = fragment.querySelector('.page-a4');
            
            // GESTION DU CONTENU SELON LE NUMÉRO DE PAGE
            if (!isFirst) {
                // 1. Supprimer le tableau des destinataires
                const recipientSection = pageDiv.querySelector('.recipient-section');
                if (recipientSection) recipientSection.remove();

                // 2. Supprimer la section Objet / Concerne
                const objectSection = pageDiv.querySelector('.object-section');
                if (objectSection) objectSection.remove();

                // 3. Modifier l'en-tête (Garder uniquement le logo)
                const headerSection = pageDiv.querySelector('.header-section');
                if (headerSection) {
                    // On supprime les titres (H1, H2) mais on garde le logo (la première div)
                    const titles = headerSection.querySelectorAll('h1, h2');
                    titles.forEach(t => t.remove());
                    headerSection.classList.remove('mb-4'); // Réduire l'espace
                    headerSection.classList.add('mb-2');
                }

                // 4. Masquer le QR Code sur les pages suivantes (optionnel)
                const qrSection = pageDiv.querySelector('.qr-section');
                if (qrSection) qrSection.style.visibility = 'hidden';
            }

            // Mise à jour du numéro de page dans le footer
            const pageNumDisplay = pageDiv.querySelector('.page-number');
            if (pageNumDisplay) pageNumDisplay.innerText = num;

            return pageDiv;
        },

        updatePageNumbers(container) {
            const allPages = container.querySelectorAll('.page-a4');
            const total = allPages.length;
            allPages.forEach((page, idx) => {
                const span = page.querySelector('.page-number');
                if (span) span.innerText = `${idx + 1} / ${total}`;
            });
        }
    }));
});
    </script>

</body>
</html>
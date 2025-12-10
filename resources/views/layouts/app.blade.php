<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CBC_WorkFlow_Docs') }}</title>

    <!-- === AJOUT DU LOGO (FAVICON) ICI === -->
    <!-- Remplacez 'images/logo.png' par le chemin réel de votre image dans le dossier public -->
    <!-- 2. Fallback PNG (Si le SVG échoue ou n'existe pas) -->
    <link rel="icon" href="{{ asset('images/lo.png') }}?v=3" type="image/jpg">
    
    <!-- Optionnel : Pour les iPhone/iPad -->
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

    <!-- 1. Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- 2. CSS Libraries (Quill) -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <!-- 3. Styles personnalisés pour l'éditeur -->
    <style>
        /* ... (votre style existant reste inchangé) ... */
        /* Correctifs pour que les tableaux s'affichent bien */
        .ql-editor table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        .ql-editor td { border: 1px solid #ccc; padding: 8px; }
        
        /* Style A4 pour l'éditeur */
        .ql-container.ql-snow { border: none !important; }
        .ql-editor { 
            min-height: 29.7cm; /* Hauteur A4 */
            padding: 2.5cm 2cm; /* Marges standard */
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        /* Toolbar style Word */
        .ql-toolbar.ql-snow {
            background: #f3f4f6;
            border: 1px solid #e5e7eb !important;
            border-radius: 8px 8px 0 0;
            position: sticky;
            top: 0;
            z-index: 30;
            padding: 12px !important;
        }
        .ql-formats { margin-right: 24px !important; border-right: 1px solid #e5e7eb; padding-right: 12px; }
        .ql-formats:last-child { border-right: none; }
    </style>

    <!-- 4. Vite Resources (CSS & JS Application) -->
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/js/zonetext.js',
        'resources/css/sidebar.css',
        'resources/css/navbar.css',
        'resources/css/dashcard.css',
        'resources/css/theme.css'
    ])

    <!-- 5. Livewire Styles -->
    @livewireStyles
</head>

<body class="font-sans antialiased">

    <!-- Contenu Principal -->
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
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span x-text="message"></span>
        </div>
    </div> 

    <!-- SCRIPTS EN BAS DE PAGE (Pour la performance) -->

    <!-- Livewire -->
    @livewireScripts   

    <!-- External Libraries -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>

    <!-- Custom Scripts -->
    <script>
        function downloadMemoPDF() {
            // 1. On cible uniquement la feuille blanche
            const element = document.getElementById('page-1');
            
            if (!element) {
                console.error("L'élément #page-1 est introuvable pour la génération PDF.");
                return;
            }

            // 2. Configuration du PDF
            const opt = {
                margin:       0,
                filename:     'Memorandum.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            // 3. Génération et téléchargement
            var clone = element.cloneNode(true);
            clone.classList.remove('shadow-2xl'); // Enlève l'ombre pour le PDF propre
            
            html2pdf().set(opt).from(clone).save();
        }
    </script>
</body>
</html>
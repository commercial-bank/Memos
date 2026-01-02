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

    <!-- TomSelect CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <!-- =========================================================
         STYLES PERSONNALISÉS (CSS)
         ========================================================= -->
    

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

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('searchableSelect', (config) => ({
            instance: null,
            init() {
                // Initialisation de TomSelect
                this.instance = new TomSelect(this.$el, {
                    placeholder: config.placeholder || 'Rechercher...',
                    allowEmptyOption: true,
                    maxOptions: 1000, // Permet de gérer de gros volumes
                    controlInput: '<input>',
                    render: {
                        no_results: (data, escape) => '<div class="no-results">Aucun résultat pour "' + escape(data.input) + '"</div>',
                    }
                });

                // Synchronisation avec Livewire quand la valeur change
                this.instance.on('change', (value) => {
                    this.$dispatch('input', value);
                });
            },
            // Important pour les listes dépendantes (ex: Sous-Direction qui change selon la Direction)
            updateOptions() {
                if(this.instance) {
                    this.instance.clearOptions();
                    this.instance.sync();
                }
            }
        }))
    })
</script>
<script>
    // Appliquer au chargement
    if ({{ session('dark_mode') ? 'true' : 'false' }}) {
        document.documentElement.classList.add('dark');
    }

    // Écouter les changements en direct
    window.addEventListener('dark-mode-toggled', event => {
        if (event.detail.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    });
</script>
</body>
</html>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CBC_WorkFlow_Docs') }}</title>

    <!-- FAVICON -->
    <link rel="icon" href="{{ asset('images/lo.png') }}?v=3" type="image/jpg">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

    <!-- FONTS & ICONS -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- === CSS QUILL 2.0.2 (UNIQUEMENT CELUI-CI) === -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />

    <!-- Style pour les Tableaux Quill -->
    <style>
        .ql-editor table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        .ql-editor td, .ql-editor th { border: 1px solid #000; padding: 8px; }
        
        .ql-container.ql-snow { border: none !important; }
        .ql-editor { 
            min-height: 29.7cm; 
            padding: 2.5cm 2cm; 
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .ql-toolbar.ql-snow {
            background: #f3f4f6;
            border: 1px solid #e5e7eb !important;
            border-radius: 8px 8px 0 0;
            position: sticky;
            top: 0;
            z-index: 30;
        }
        
        /* Scrollbar Personnalisée */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #D4AF37; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #b5952f; }
    </style>

    <!-- Vite Resources -->
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        // 'resources/js/zonetext.js', // Vérifiez si ce fichier ne charge pas un autre Quill !
        'resources/css/sidebar.css',
        'resources/css/navbar.css',
        'resources/css/dashcard.css',
        'resources/css/theme.css'
    ])

    @livewireStyles
</head>

<body class="font-sans antialiased">

    {{ $slot }}

    <!-- Toast Notification -->
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

    @livewireScripts   

    <!-- External Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- === JS QUILL 2.0.2 (UNIQUEMENT CELUI-CI, A LA FIN) === -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    

    <!-- Script PDF -->
    <script>
        function downloadMemoPDF() {
            const element = document.getElementById('page-1');
            if (!element) return;
            const opt = {
                margin: 0,
                filename: 'Memorandum.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            var clone = element.cloneNode(true);
            clone.classList.remove('shadow-2xl');
            html2pdf().set(opt).from(clone).save();
        }
    </script>

    
</body>
</html>
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
        <script>
            function downloadPDF() {
                const element = document.getElementById('memo-to-print');
                
                // Options mises à jour
                const opt = {
                    margin:       0, 
                    filename:     'Memorandum.pdf',
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { 
                        scale: 2, 
                        useCORS: true,
                        letterRendering: true // Améliore le rendu du texte
                    },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
                    
                    // AJOUTEZ CETTE SECTION POUR GÉRER LES COUPURES
                    pagebreak: { 
                        mode: ['avoid-all', 'css', 'legacy'],
                        avoid: 'tr' // Évite aussi de couper les lignes du tableau
                    }
                };

                html2pdf().set(opt).from(element).save();
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

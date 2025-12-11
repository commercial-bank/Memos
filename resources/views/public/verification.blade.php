<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Officielle - {{ $memo->numero_ref }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Polices -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Merriweather:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; }
        .font-legal { font-family: 'Merriweather', serif; }
        .paper-texture {
            background-color: #ffffff;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .seal-shadow { box-shadow: 0 4px 15px rgba(217, 119, 6, 0.2); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-3xl paper-texture shadow-2xl rounded-sm border-t-8 border-yellow-600 relative overflow-hidden">
        
        <!-- EN-TÊTE -->
        <div class="bg-gray-900 text-white p-8 text-center relative">
            <!-- Ligne dorée en haut -->
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-500 via-yellow-300 to-yellow-500"></div>
            
            <!-- Conteneur du Logo -->
            <div class="inline-block p-3 rounded-full border-2 border-yellow-500/50 mb-3 bg-gray-800 seal-shadow">
                <!-- Remplacement de la balise <i> par <img> -->
                <!-- Ajustez h-12 et w-12 selon la taille voulue -->
                <img src="{{ asset('images/log-removebg-preview.png') }}" 
                    alt="Logo Officiel" 
                    class="h-12 w-12 object-contain">
            </div>

            <h1 class="text-2xl md:text-3xl font-legal font-bold tracking-wide text-gray-100">Certificat d'Authenticité</h1>
            <p class="text-xs text-gray-400 uppercase tracking-widest mt-2">Système de validation centralisé</p>
        </div>

        <!-- CORPS -->
        <div class="p-8 md:p-12 space-y-10">

            <!-- 1. IDENTIFICATION -->
            <div class="text-center border-b border-gray-200 pb-8">
                <p class="text-xs font-bold text-gray-400 uppercase mb-2">Objet du document</p>
                <h2 class="text-xl font-legal text-gray-900 font-bold leading-relaxed">« {{ $memo->object }} »</h2>
                <div class="mt-4 inline-flex items-center gap-2 bg-gray-100 px-3 py-1 rounded text-xs text-gray-600 font-mono">
                    <i class="fas fa-barcode"></i> Réf: {{ $memo->reference }}
                </div>
            </div>

            <!-- 2. LES SIGNATAIRES -->
            @if($signatures->count() > 0)
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-yellow-700 uppercase tracking-wider border-b border-yellow-100 pb-2 mb-4">
                    <i class="fas fa-pen-nib mr-2"></i> Décision & Signature
                </h3>
                @foreach($signatures as $history)
                <div class="bg-yellow-50/50 border border-yellow-100 rounded-lg p-5 flex items-start gap-4 transition-all hover:shadow-md">
                    <div class="flex-shrink-0 h-12 w-12 rounded-full bg-gray-900 text-yellow-500 flex items-center justify-center font-legal font-bold text-lg shadow-sm border-2 border-yellow-500">
                        {{ substr($history->user->first_name, 0, 1) }} {{ substr($history->user->last_name, 0, 1) }}
                    </div>
                    <div class="flex-grow">
                        <p class="text-gray-900 text-lg font-legal">
                            <span class="font-bold">{{ $history->user->first_name }} {{ $history->user->last_name }}</span>
                            <span class="text-base text-gray-600 font-normal">a signé en qualité de</span>
                            <span class="font-bold text-gray-900 underline decoration-yellow-500/50 decoration-2 underline-offset-2">
                                {{ $history->user->poste ?? 'Fonction non définie' }}
                            </span>.
                        </p>
                        <div class="flex items-center gap-4 mt-2">
                            <span class="text-xs font-bold text-yellow-700 bg-yellow-100 px-2 py-0.5 rounded uppercase">
                                <i class="fas fa-check-circle mr-1"></i> Signé électroniquement
                            </span>
                            <span class="text-xs text-gray-400 font-mono">{{ $history->created_at->format('d/m/Y à H:i') }}</span>
                        </div>
                        @if($history->workflow_comment)
                            <p class="mt-2 text-sm text-gray-600 italic border-l-2 border-yellow-300 pl-3">"{{ $history->workflow_comment }}"</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- 3. LES VISAS (SEUL LES VALIDÉS) -->
            @if($visas->count() > 0)
            <div class="space-y-4 pt-4">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 pb-2 mb-4">
                    <i class="fas fa-eye mr-2"></i> Circuit de Contrôle (Visas)
                </h3>

                <ul class="relative border-l-2 border-gray-200 ml-3 space-y-8">
                    @foreach($visas as $history)
                        @php
                            // Détermination de la couleur selon l'action (UI Dynamique)
                            $visaType = strtoupper($history->visa);
                            if (str_contains($visaType, 'REJET')) {
                                $badgeColor = 'bg-red-100 text-red-700 border-red-200';
                                $dotColor = 'bg-red-500';
                            } elseif (str_contains($visaType, 'ACCORD')) {
                                $badgeColor = 'bg-green-100 text-green-700 border-green-200';
                                $dotColor = 'bg-green-500';
                            } else {
                                $badgeColor = 'bg-blue-50 text-blue-700 border-blue-200'; // Transmit, Enregistré
                                $dotColor = 'bg-blue-400';
                            }
                        @endphp

                        <li class="ml-8 relative">
                            <!-- Point sur la timeline -->
                            <span class="absolute -left-[41px] top-1 h-5 w-5 rounded-full {{ $dotColor }} border-4 border-white shadow-sm"></span>
                            
                            <!-- Identité -->
                            <div class="flex flex-col sm:flex-row sm:items-baseline gap-1">
                                <p class="text-gray-800 text-base">
                                    <span class="font-bold font-legal">{{ $history->user->first_name }} {{ $history->user->last_name }}</span>
                                    <span class="text-gray-500 text-xs uppercase tracking-wide ml-1">({{ $history->user->poste ?? 'N/A' }})</span>
                                </p>
                            </div>

                            <!-- Action Visa -->
                            <div class="mt-2 flex items-center gap-3">
                                <span class="text-gray-600 text-sm italic">a visé :</span>
                                <span class="inline-block {{ $badgeColor }} text-xs font-bold px-3 py-1 rounded-full border shadow-sm">
                                    {{ $history->visa }}
                                </span>
                                <span class="text-xs text-gray-400 ml-auto font-mono">
                                    {{ $history->created_at->format('d/m/Y') }}
                                </span>
                            </div>

                            <!-- Affichage du Commentaire (Si présent) -->
                            @if($history->workflow_comment)
                                <div class="mt-3 bg-gray-50 p-3 rounded-md border border-gray-200 text-sm text-gray-600 relative">
                                    <!-- Petite flèche CSS -->
                                    <div class="absolute -top-2 left-4 w-3 h-3 bg-gray-50 border-l border-t border-gray-200 transform rotate-45"></div>
                                    <p class="italic">" {{ $history->workflow_comment }} "</p>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

        </div>

        <!-- FOOTER -->
        <div class="bg-gray-50 p-6 text-center border-t border-gray-200">
            <p class="text-[10px] text-gray-400 uppercase tracking-widest">Document certifié par l'infrastructure sécurisée</p>
            <p class="text-[9px] text-gray-300 font-mono mt-1">Token ID: {{ substr($memo->qr_code, 0, 20) }}...</p>
        </div>

    </div>
</body>
</html>
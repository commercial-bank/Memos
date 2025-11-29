<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Document - Système Mémo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8 px-4 flex items-center justify-center">

    <!-- CHANGEMENT ICI : max-w-3xl au lieu de max-w-md pour élargir la carte -->
    <div class="w-full max-w-3xl bg-white shadow-2xl rounded-2xl overflow-hidden border border-gray-200">
        
        <!-- EN-TÊTE : Statut du Document -->
        <div class="bg-green-50 p-8 text-center border-b border-green-100 relative overflow-hidden">
            <!-- Décoration d'arrière plan -->
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-green-100 rounded-full opacity-50"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-green-100 rounded-full opacity-50"></div>
            
            <div class="relative z-10">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white shadow-sm mb-4 text-green-600 ring-8 ring-green-100/50">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 uppercase tracking-wide">Document Authentique</h1>
                <p class="text-sm text-green-700 font-medium mt-2 bg-green-200 inline-block px-4 py-1.5 rounded-full">
                    Certifié Conforme par le Système Central
                </p>
            </div>
        </div>

        <!-- SECTION 1 : Informations Clés (Disposées en grille sur grand écran) -->
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Colonne Gauche : Objet (Prend 2/3 de l'espace) -->
                <div class="md:col-span-2">
                    <p class="text-xs text-gray-400 uppercase tracking-widest font-bold mb-2">Objet du Mémo</p>
                    <p class="text-gray-900 font-bold text-xl leading-snug border-l-4 border-green-500 pl-4 py-1">
                        Mise en place de la nouvelle infrastructure serveur pour le département IT et migration des données sensibles.
                    </p>
                </div>

                <!-- Colonne Droite : Métadonnées (Prend 1/3 de l'espace) -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex flex-col justify-center space-y-4">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold">Référence Unique</p>
                        <p class="font-mono text-base font-bold text-gray-700">#00892-XT</p>
                    </div>
                    <div class="w-full h-px bg-gray-200"></div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold">Date de Création</p>
                        <p class="font-mono text-base font-bold text-gray-700">29/11/2025</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2 : Signatures Juridiques (Côte à côte sur grand écran) -->
        <div class="border-t border-gray-100 px-8 py-6 bg-slate-50">
            <h3 class="text-xs font-bold text-gray-400 uppercase mb-6 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Signatures Officielles & Validation
            </h3>
            
            <!-- Grille de signatures : 1 colonne sur mobile, 2 sur PC -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Signature 1 : Sous-Directeur -->
                <div class="bg-white p-5 rounded-xl border border-green-200 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-green-500"></div>
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center font-bold text-green-700 text-sm border border-green-100">SD</div>
                            <div>
                                <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Sous-Directeur</p>
                                <p class="text-base font-bold text-gray-800">Marc ATANGANA</p>
                                <p class="text-[10px] text-gray-400 font-mono mt-1">ID: X7K9-P2M4-VALID</p>
                            </div>
                        </div>
                        <div class="text-center bg-green-50 px-3 py-1 rounded">
                            <span class="text-xl">✅</span>
                            <span class="text-[9px] text-green-700 font-bold block mt-1">SIGNÉ</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-xs text-gray-500 italic">"Vu et validé pour compétence."</span>
                        <span class="text-[10px] text-gray-400 font-medium">29/11 à 14:30</span>
                    </div>
                </div>

                <!-- Signature 2 : Directeur -->
                <div class="bg-white p-5 rounded-xl border border-green-200 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-green-500"></div>
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center font-bold text-green-700 text-sm border border-green-100">DIR</div>
                            <div>
                                <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Directeur</p>
                                <p class="text-base font-bold text-gray-800">Jean DUPONT</p>
                                <p class="text-[10px] text-gray-400 font-mono mt-1">ID: A1B2-C3D4-CERT</p>
                            </div>
                        </div>
                        <div class="text-center bg-green-50 px-3 py-1 rounded">
                            <span class="text-xl">✅</span>
                            <span class="text-[9px] text-green-700 font-bold block mt-1">APPROUVÉ</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-xs text-gray-500 italic">"Bon pour accord."</span>
                        <span class="text-[10px] text-gray-400 font-medium">30/11 à 09:15</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- SECTION 3 : Historique (Timeline) -->
        <div class="p-8">
            <h3 class="text-xs font-bold text-gray-400 uppercase mb-8 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Traçabilité Complète
            </h3>
            
            <!-- Timeline Container -->
            <div class="relative border-l-2 border-gray-200 ml-3 space-y-10">
                
                <!-- Étape 3 (La plus récente) -->
                <div class="ml-8 relative group">
                    <span class="absolute -left-[39px] top-1 h-5 w-5 rounded-full bg-green-500 border-4 border-white shadow transition-transform group-hover:scale-125"></span>
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                        <div>
                            <h4 class="text-base font-bold text-gray-800">Jean DIRECTEUR</h4>
                            <p class="text-xs text-gray-500 font-medium">Direction Générale</p>
                        </div>
                        <span class="text-[11px] text-gray-500 bg-gray-100 px-3 py-1 rounded-full font-mono">30/11/2025 - 09:15</span>
                    </div>
                    
                    <div class="mt-3 inline-flex items-center gap-2 bg-green-50 text-green-700 text-xs font-bold px-3 py-1.5 rounded-lg border border-green-100">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        Action : Validation Finale
                    </div>
                    <p class="text-sm text-gray-600 italic mt-3 bg-gray-50 p-3 rounded-lg border-l-4 border-green-300">
                        "Validé. Procéder au déploiement immédiat."
                    </p>
                </div>

                <!-- Étape 2 -->
                <div class="ml-8 relative group">
                    <span class="absolute -left-[39px] top-1 h-5 w-5 rounded-full bg-blue-500 border-4 border-white shadow transition-transform group-hover:scale-125"></span>
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                        <div>
                            <h4 class="text-base font-bold text-gray-800">Paul MANAGER</h4>
                            <p class="text-xs text-gray-500 font-medium">Service Informatique</p>
                        </div>
                        <span class="text-[11px] text-gray-500 bg-gray-100 px-3 py-1 rounded-full font-mono">29/11/2025 - 16:00</span>
                    </div>
                    <div class="mt-3 inline-flex items-center gap-2 bg-blue-50 text-blue-700 text-xs font-bold px-3 py-1.5 rounded-lg border border-blue-100">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        Action : Visa / Avis Favorable
                    </div>
                </div>

                <!-- Étape 1 (Début) -->
                <div class="ml-8 relative group">
                    <span class="absolute -left-[39px] top-1 h-5 w-5 rounded-full bg-gray-300 border-4 border-white shadow transition-transform group-hover:scale-125"></span>
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                        <div>
                            <h4 class="text-base font-bold text-gray-800">Marc EMPLOYÉ</h4>
                            <p class="text-xs text-gray-500 font-medium">Développeur</p>
                        </div>
                        <span class="text-[11px] text-gray-500 bg-gray-100 px-3 py-1 rounded-full font-mono">29/11/2025 - 10:00</span>
                    </div>
                    <div class="mt-3 inline-flex items-center gap-2 bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                        Action : Création du Mémo
                    </div>
                </div>

            </div>
        </div>

        <!-- Pied de page -->
        <div class="bg-gray-50 py-6 text-center border-t border-gray-200">
            <p class="text-[10px] text-gray-400 uppercase tracking-widest font-medium">
                Document généré électroniquement par SecureMemo System • 2025
            </p>
            <p class="text-[10px] text-gray-300 mt-1">ID Unique: 892-XT-SECURE-HASH-256</p>
        </div>

    </div>

</body>
</html>
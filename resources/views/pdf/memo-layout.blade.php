<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Mémorandum</title>
    <style>
        /* --- CONFIGURATION GLOBALE PDF --- */
        @page {
            margin: 150px 20mm 110px 20mm; 
            size: A4 portrait;
        }

        body {
            /* Police principale : Calibri (comme le mémo original) */
            font-family: 'Calibri', 'Carlito', 'Arial', sans-serif;
            font-size: 11pt; /* Taille standard Word */
            color: #000;
            line-height: 1.15; /* Interligne standard Word */
            margin: 0; padding: 0;
        }

        /* --- 1. LE CADRE JAUNE (Fixe, arrière-plan) --- */
        #document-frame {
            position: fixed;
            top: -140px;    
            bottom: -100px; 
            left: -10mm;    
            right: -10mm;
            border: 3px solid #daaf2c;
            border-radius: 0 50px 0 50px;
            z-index: -10; 
            pointer-events: none;
        }

        /* --- 2. LOGO (Fixe, toutes les pages) --- */
        header {
            position: fixed;
            top: -130px; 
            left: -10mm; 
            right: -10mm;
            height: 90px;
            text-align: center;
            z-index: 1;
        }

        .logo-img { 
            height: 70px; 
            display: block; 
            margin: 0 auto; 
        }

        /* --- 3. FOOTER (Fixe, toutes les pages) --- */
        footer {
            position: fixed;
            bottom: -90px;
            left: -10mm; 
            right: -10mm;
            height: 70px;
            text-align: center;
            z-index: 1;
        }
        .qr-placeholder { width: 50px; height: 50px; margin: 0 auto; display: block; }
        .qr-placeholder-empty { width: 50px; height: 50px; border: 1px dashed #ccc; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 10px; }
        .ref-text { font-size: 8px; color: #666; font-style: italic; margin-top: 2px; font-family: Arial, sans-serif; }

        /* --- 4. BLOC TITRE PAGE 1 --- */
        .page-one-header {
            text-align: center;
            margin-bottom: 25px;
            margin-top: -30px; 
        }

        .direction { 
            /* Police du logo/titre : Style Géométrique */
            font-family: 'Century Gothic', 'AvantGarde', 'Tw Cen MT', sans-serif;
            font-size: 10px; 
            font-weight: bold; 
            text-transform: uppercase; 
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }
        
        .main-title { 
            /* Police Mémorandum : Style Calibri Italique Gras */
            font-family: 'Calibri', 'Carlito', sans-serif;
            font-size: 18pt; 
            font-weight: bold; 
            text-transform: uppercase; 
            font-style: italic; 
            margin-top: 5px;
        }

        /* --- TABLEAUX --- */
        .header-row-table { width: 100%; border-collapse: collapse; font-weight: bold; font-size: 11pt; margin-bottom: 2px; table-layout: fixed; }
        .header-row-table td { padding: 0 0 5px 0; border: none; vertical-align: bottom; }
        
        .w-35 { width: 35%; }
        .w-30 { width: 30%; }
        .text-center { text-align: center; }
        .pl-8 { padding-left: 32px; }

        table.dist-table { width: 100%; border-collapse: collapse; font-size: 10pt; table-layout: fixed; }
        table.dist-table td { border: 1px solid #000; padding: 4px 8px; vertical-align: middle; background-color: transparent; }
        
        .col-label { width: 30%; font-weight: bold; }
        .col-check { width: 25%; }
        .col-dest { width: 45%; font-weight: bold; text-align: center; } 
        /* Note: Sur le screenshot, la colonne destinataire n'a pas de fond gris, j'ai retiré le background-color */
        
        .checkbox { display: inline-block; width: 10px; height: 10px; border: 1px solid #000; margin-right: 5px; position: relative; top: 1px; }
        .checkbox.checked { background-color: #000; }

        /* --- CONTENU --- */
        .meta-box { margin: 20px 0; }
        .meta-row { margin-bottom: 8px; font-size: 11pt; }
        
        /* Soulignement gras pour Objet et Concerne */
        .meta-label { font-weight: bold; text-decoration: underline; margin-right: 5px; }
        .meta-value { font-weight: bold; }

        .content-body { 
            text-align: justify; 
            font-size: 12pt; /* Taille passée à 12 */
            line-height: 1.15; 
            margin-top: 20px; 
            /* Police Times New Roman avec repli sur serif standard */
            font-family: 'Times New Roman', Times, serif;
        }
        
        /* Gestion des paragraphes pour imiter Word */
        .content-body p { margin-bottom: 10pt; }

        main { display: block; }
    </style>
</head>
<body>

    <div id="document-frame"></div>

    <header>
        @if(isset($logo) && $logo)
            <img src="{{ $logo }}" class="logo-img" alt="Logo">
        @else
            <div style="height:60px;"></div>
        @endif
    </header>

   <footer>
        <div class="qr-placeholder">
            @if(isset($memo->qr_code))
                {{-- On force le SVG, on l'encode en base64 et on l'affiche comme une image --}}
                <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(50)->generate(route('memo.verify', $memo->qr_code))) }}" 
                    width="50" 
                    height="50" 
                    alt="QR Code">
            @else
                <div class="qr-placeholder-empty">QR</div>
            @endif
        </div>

        <div class="ref-text">{{ $memo->numero_ref  }} | Généré le {{ now()->format('d/m/Y') }}</div>
    </footer>

    <main>
        
        <div class="page-one-header">
            <div class="direction">
                {{ $memo->user->entity->name  }}
            </div>
            <div class="main-title">Mémorandum</div>
        </div>

        <table class="header-row-table">
            <tr>
                <td class="w-35"></td> 
                <td class="w-30 text-center">Prière de :</td>
                <td class="w-35 pl-8">Destinataires :</td>
            </tr>
        </table>

        <table class="dist-table">
            <tr>
                <td class="col-label">Date : {{ $memo->created_at->format('Y') }}</td>
                <td class="col-check">
                    <span class="checkbox {{ isset($recipientsByAction['Faire le nécessaire']) ? 'checked' : '' }}"></span> 
                    Faire le nécessaire
                </td>
                <td class="col-dest">
                    @foreach($recipientsByAction['Faire le nécessaire'] ?? [] as $dest)
                        {{ $dest->entity->ref ?? \Illuminate\Support\Str::limit($dest->entity->name, 15) }}@if(!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
            <tr>
                <td class="col-label">N° : {{ $memo->reference ?? '___/REF/___' }}</td>
                <td class="col-check">
                    <span class="checkbox {{ isset($recipientsByAction['Prendre connaissance']) ? 'checked' : '' }}"></span> 
                    Prendre connaissance
                </td>
                <td class="col-dest">
                    @foreach($recipientsByAction['Prendre connaissance'] ?? [] as $dest)
                        {{ $dest->entity->ref ?? \Illuminate\Support\Str::limit($dest->entity->name, 15) }}@if(!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
            <tr>
                <td class="col-label">Emetteur : {{ $memo->reference ? Str::afterLast($memo->reference, '/') : 'En attente' }}</td> <!-- Correction : Souvent l'émetteur est le sigle du service/direction, pas le nom de la personne -->
                <td class="col-check">
                    <span class="checkbox {{ isset($recipientsByAction['Prendre position']) ? 'checked' : '' }}"></span> 
                    Prendre position
                </td>
                <td class="col-dest">
                    @foreach($recipientsByAction['Prendre position'] ?? [] as $dest)
                        {{ $dest->entity->ref ?? \Illuminate\Support\Str::limit($dest->entity->name, 15) }}@if(!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
            <tr>
                <td class="col-label">Service : {{ $memo->user->service ?? 'DGR' }}</td>
                <td class="col-check">
                    <span class="checkbox {{ isset($recipientsByAction['Décider']) ? 'checked' : '' }}"></span> 
                    Décider
                </td>
                <td class="col-dest">
                    @foreach($recipientsByAction['Décider'] ?? [] as $dest)
                        {{ $dest->entity->ref ?? \Illuminate\Support\Str::limit($dest->entity->name, 15) }}@if(!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
        </table>

        <div class="meta-box">
            <div class="meta-row">
                <span class="meta-label">Objet :</span>
                <span class="meta-value">{{ $memo->object }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Concerne :</span>
                <span>{{ $memo->concern }}</span>
            </div>
        </div>

        <div class="content-body">
            {!! $memo->content !!}
        </div>

    </main>

</body>
</html>
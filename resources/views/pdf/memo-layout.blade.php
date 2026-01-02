<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Mémorandum</title>
    <style>
        /* =========================================================
           1. CONFIGURATION GLOBALE
           ========================================================= */
        @page {
            margin: 140px 20mm 110px 20mm;
            size: A4 portrait;
        }

        body {
            font-family: 'Tahoma', sans-serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }

        /* =========================================================
           2. STRUCTURE & ÉLÉMENTS FIXES
           ========================================================= */
        #document-frame {
            position: fixed;
            top: -130px;
            bottom: -110px; /* Légèrement plus bas que le contenu du footer */
            left: -10mm;
            right: -10mm;
            border: 3px solid #daaf2c;
            border-radius: 0 150px 0 150px;
            z-index: -100; /* On le pousse au fond du fond */
        }

        header {
            position: fixed;
            top: -125px;
            left: 0;
            right: 0;
            height: 80px;
            text-align: center;
        }

        .logo-img { height: 70px; }

        

        .qr-placeholder { width: 55px; height: 55px; margin: 0 auto; }
        .qr-placeholder img { width: 50px; height: 50px; }
        
        .ref-text { font-size: 8px; color: #666; margin-top: 5px; }

        /* AJOUT : STYLE POUR LA NUMÉROTATION DES PAGES */
        .page-number {
            position: absolute;
            right: 0;
            bottom: 0;
            font-size: 9px;
            color: #666;
            font-style: italic;
        }

        .page-number:after {
            content: "Page " counter(page);
        }

        /* =========================================================
           3. TITRES ET TABLEAUX
           ========================================================= */
        .page-one-header { text-align: center; margin-bottom: 20px; margin-top: -20px; }
        .direction { font-size: 10px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; }
        .main-title { font-size: 18pt; font-weight: bold; text-transform: uppercase; font-style: italic; }

        .header-row-table {
            width: 100%;
            border-collapse: collapse;
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 2px;
            table-layout: fixed;
        }

        table.dist-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            table-layout: fixed;
        }

        table.dist-table td { border: 1px solid #000; padding: 5px 8px; vertical-align: middle; }

        .col-label { width: 35%; font-weight: bold; }
        .col-check { width: 40%; }
        .col-dest  { width: 25%; font-weight: bold; text-align: center; }

        .checkbox { display: inline-block; width: 10px; height: 10px; border: 1px solid #000; margin-right: 5px; }
        .checkbox.checked { background-color: #000; }

        /* =========================================================
           4. CONTENU ET ZONE DE VALIDATION
           ========================================================= */
        .meta-box { margin: 25px 0; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .meta-row { margin-bottom: 8px; }
        .meta-label { font-weight: bold; text-decoration: underline; }
        .meta-value { font-weight: bold; }

        .content-body {
            text-align: justify;
            font-size: 11pt;
            line-height: 1.4;
            margin-top: 30px;
        }

        .content-body .direction {
            font-size: 12pt;
        }

        .validation-block {
            margin-top: 50px;
            width: 350px;
            float: right;
            text-align: left;
            padding: 10px;
            border-left: 2px solid #daaf2c;
            page-break-inside: avoid;
        }

        /* Augmentation de la marge du bas pour laisser de la place au bloc de validation */
        @page {
            margin: 140px 20mm 160px 20mm; /* Marge du bas augmentée */
            size: A4 portrait;
        }


        /* Ajustement du footer pour contenir le bloc + le QR code */
        footer {
            position: fixed;
            bottom: -85px; /* Remonté pour être au-dessus de la bordure jaune */
            left: 0;
            right: 0;
            height: 120px;
            z-index: 999; /* On force le passage au premier plan */
            text-align: center;
        }

        /* Style spécifique pour le bloc de validation dans le footer */
        .footer-validation {
            text-align: right;
            border-right: 3px solid #daaf2c;
            padding-right: 15px;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        /* On utilise un conteneur pour aligner QR et Validation côte à côte */
        .footer-container {
            width: 100%;
        }

        .footer-left {
            float: left;
            width: 30%;
            text-align: left;
        }

        .footer-right {
            float: right;
            width: 65%;
            text-align: right;
            border-right: 3px solid #daaf2c;
            padding-right: 10px;
        }

        .validation-text { font-size: 9pt; color: #333; }
        .validator-name { font-size: 10pt; font-weight: bold; text-transform: uppercase; display: block; }
        .validator-quality { font-size: 9pt; font-style: italic; color: #555; }

        /* Réajustement de la position du QR code pour qu'il soit sous la validation */
        .qr-placeholder { 
            width: 50px; 
            height: 50px; 
            margin: 0 auto; 
            display: block;
        }

        .validation-text { font-size: 10pt; color: #333; margin-bottom: 5px; }
        .validator-name { font-size: 11pt; font-weight: bold; text-transform: uppercase; display: block; margin: 5px 0; }
        .validator-quality { font-size: 10pt; font-style: italic; color: #555; }

        .clearfix { clear: both; }

        .w-35 { width: 35%; } .w-40 { width: 40%; } .w-25 { width: 25%; }
        .text-center { text-align: center; }
        .pl-8 { padding-left: 32px; }
    </style>
</head>

<body>

    <div id="document-frame"></div>

    <header>
        @if(isset($logo))
            <img src="{{ $logo }}" class="logo-img">
        @endif
    </header>

    <footer>
        <!-- BLOC DE VALIDATION DÉPLACÉ ICI -->
        <!-- BLOC DE VALIDATION DYNAMIQUE -->
        @if(!empty($memo->signature_dir) && isset($director))
            <div class="footer-validation">
                <div class="validation-text">Le présent document a été validé par :</div>
                <span class="validator-name">
                    Monsieur {{ $director->first_name }} {{ $director->last_name }}
                </span>
                <div class="validator-quality">
                    en qualité de Directeur {{ $director->entity->ref  }}
                </div>
            </div>
        @endif

        <div class="qr-placeholder">
            @if(isset($memo->qr_code))
                <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(50)->generate(route('memo.verify', $memo->qr_code))) }}" 
                     width="50" height="50">
            @else
                <div class="qr-placeholder-empty">QR</div>
            @endif
        </div>
        <div class="ref-text">
            {{ $memo->numero_ref }} | Généré le {{ now()->format('d/m/Y') }}
        </div>
        
        <!-- NUMÉROTATION DE PAGE -->
        <div class="page-number"></div>
    </footer>

    <main>
        <div class="page-one-header">
            <div class="direction">{{ $memo->user->entity->name }}</div>
            <div class="main-title">Mémorandum</div>
        </div>

        <table class="header-row-table">
            <tr>
                <td class="w-35"></td>
                <td class="w-40 text-center">Prière de :</td>
                <td class="w-25 pl-8">Destinataires :</td>
            </tr>
        </table>

        <table class="dist-table">
            <tr>
                <td class="col-label">Date : {{ $memo->created_at->format('d/m/Y') }}</td>
                <td class="col-check">
                    <span class="checkbox {{ isset($recipientsByAction['Faire le nécessaire']) ? 'checked' : '' }}"></span> Faire le nécessaire
                </td>
                <td class="col-dest">
                    @foreach($recipientsByAction['Faire le nécessaire'] ?? [] as $dest)
                        {{ $dest->entity->ref ?? Str::limit($dest->entity->name, 12) }}@if(!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
            <tr>
                <td class="col-label">N° : {{ $memo->reference ?? '___/REF/___' }}</td>
                <td class="col-check">
                    <span class="checkbox {{ isset($recipientsByAction['Prendre connaissance']) ? 'checked' : '' }}"></span> Prendre connaissance
                </td>
                <td class="col-dest">
                    @foreach($recipientsByAction['Prendre connaissance'] ?? [] as $dest)
                        {{ $dest->entity->ref ?? Str::limit($dest->entity->name, 12) }}@if(!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
            <tr>
                <td class="col-label">Emetteur : {{ $memo->reference ? Str::afterLast($memo->reference, '/') : 'DGR' }}</td>
                <td class="col-check">
                    <span class="checkbox {{ isset($recipientsByAction['Prendre position']) ? 'checked' : '' }}"></span> Prendre position
                </td>
                <td class="col-dest">
                    @foreach($recipientsByAction['Prendre position'] ?? [] as $dest)
                        {{ $dest->entity->ref ?? Str::limit($dest->entity->name, 12) }}@if(!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
            <tr>
                <td class="col-label">Service : {{ $memo->user->service ?? 'DGR' }}</td>
                <td class="col-check">
                    <span class="checkbox {{ isset($recipientsByAction['Décider']) ? 'checked' : '' }}"></span> Décider
                </td>
                <td class="col-dest">
                    @foreach($recipientsByAction['Décider'] ?? [] as $dest)
                        {{ $dest->entity->ref ?? Str::limit($dest->entity->name, 12) }}@if(!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
        </table>

        <div class="meta-box">
            <div class="meta-row">
                <span class="meta-label">Objet :</span> <span class="meta-value">{{ $memo->object }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Concerne :</span> <span>{{ $memo->concern }}</span>
            </div>
        </div>

        <div class="content-body">
            {!! $memo->content !!}
        </div>

        <div class="clearfix"></div>

    </main>

</body>
</html>
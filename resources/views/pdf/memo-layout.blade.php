<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Mémorandum</title>
    <style>
        /* --- CONFIGURATION GLOBALE PDF --- */
        @page {
            /* 
               Marge du haut à 150px pour sécuriser le logo sur les pages 2, 3, etc.
            */
            margin: 150px 20mm 110px 20mm; 
            size: A4 portrait;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
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
        .ref-text { font-size: 8px; color: #666; font-style: italic; margin-top: 2px; }

        /* --- 4. BLOC TITRE PAGE 1 --- */
        .page-one-header {
            text-align: center;
            margin-bottom: 25px;
            
            /* --- CORRECTION ICI --- */
            /* On remonte ce bloc vers le haut pour coller au logo */
            /* Ajustez -30px à -40px ou -50px si vous voulez encore plus proche */
            margin-top: -30px; 
        }

        .direction { 
            font-size: 9px; 
            font-weight: bold; 
            text-transform: uppercase; 
            margin-bottom: 20px; /* Espace entre DIRECTION et MÉMORANDUM */
        }
        
        .main-title { 
            font-family: 'Times New Roman', serif; 
            font-size: 22px; 
            font-weight: 900; 
            text-transform: uppercase; 
            font-style: italic; 
        }

        /* --- TABLEAUX --- */
        .header-row-table { width: 100%; border-collapse: collapse; font-weight: bold; font-size: 13px; margin-bottom: 2px; table-layout: fixed; }
        .header-row-table td { padding: 0 0 5px 0; border: none; vertical-align: bottom; }
        
        .w-35 { width: 35%; }
        .w-30 { width: 30%; }
        .text-center { text-align: center; }
        .pl-8 { padding-left: 32px; }

        table.dist-table { width: 100%; border-collapse: collapse; font-size: 11px; table-layout: fixed; }
        table.dist-table td { border: 2px solid #000; padding: 5px 8px; vertical-align: middle; background-color: transparent; }
        
        .col-label { width: 30%; font-weight: bold; }
        .col-check { width: 25%; }
        .col-dest { width: 45%; background-color: #f2f2f2; text-align: center; font-weight: bold; }
        
        .checkbox { display: inline-block; width: 10px; height: 10px; border: 2px solid #000; margin-right: 5px; position: relative; top: 2px; }
        .checkbox.checked { background-color: #000; }

        /* --- CONTENU --- */
        .meta-box { margin: 20px 0; }
        .meta-row { margin-bottom: 8px; font-size: 12px; }
        .meta-label { font-weight: bold; text-decoration: underline; margin-right: 5px; }
        .meta-value { font-weight: bold; text-transform: uppercase; }

        .content-body { text-align: justify; font-size: 12px; line-height: 1.5; margin-top: 20px; }
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
        @if(isset($qrCode) && $qrCode)
            <img src="{{ $qrCode }}" class="qr-placeholder" alt="QR">
        @else
            <div class="qr-placeholder-empty">QR</div>
        @endif
        <div class="ref-text">FOR-ME-07-V1 | Généré le {{ now()->format('d/m/Y') }}</div>
    </footer>

    <main>
        
        <!-- Bloc Titre Page 1 (Remonté vers le logo grâce au margin-top négatif) -->
        <div class="page-one-header">
            <div class="direction">
                {{ $memo->user->entity->name ?? 'DIRECTION TRANSFORMATION DIGITALE ET SYSTÈME D\'INFORMATION' }}
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
                <td class="col-label">Date : {{ $date ?? now()->format('d/m/Y') }}</td>
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
                <td class="col-label">Emetteur : {{ $memo->user->first_name }} {{ $memo->user->last_name }}</td>
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
                <td class="col-label">Service : {{ $memo->user->service ?? '' }}</td>
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
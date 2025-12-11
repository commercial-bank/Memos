<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Mémorandum</title>
    <style>
        /* --- CONFIGURATION A4 --- */
        @page { size: A4 portrait; margin: 0; }
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f0f0f0; }

        .page-container {
            width: 210mm;
            min-height: 297mm;
            background-color: white;
            margin: 20px auto;
            position: relative;
        }

        /* CADRE JAUNE */
        #document-frame {
            position: absolute;
            top: 10mm; bottom: 10mm; left: 10mm; right: 10mm;
            border: 3px solid #daaf2c;
            border-radius: 0 50px 0 50px;
            pointer-events: none; z-index: 10;
        }

        /* HEADER */
        header {
            position: absolute;
            top: 15mm; left: 15mm; right: 15mm;
            height: 140px;
            text-align: center;
            background-color: white;
            z-index: 20;
        }
        .logo-img { height: 60px; display: block; margin: 0 auto 5px auto; }
        
        /* MODIFICATION ICI : Espacement augmenté */
        .direction { 
            font-size: 9px; 
            font-weight: bold; 
            text-transform: uppercase; 
            margin-bottom: 20px; /* Augmenté de 5px à 20px */
        }
        
        .main-title { font-family: 'Times New Roman', serif; font-size: 22px; font-weight: 900; text-transform: uppercase; font-style: italic; }

        /* FOOTER */
        footer {
            position: absolute; bottom: 15mm; left: 15mm; right: 15mm;
            text-align: center; z-index: 20; background-color: white;
        }
        .qr-placeholder { width: 50px; height: 50px; border: 1px dashed #ccc; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 10px; }
        .ref-text { font-size: 8px; color: #666; font-style: italic; margin-top: 2px; }

        /* CONTENU PRINCIPAL */
        main {
            position: relative;
            z-index: 15;
            padding-left: 20mm; padding-right: 20mm;
            padding-top: 210px; 
            padding-bottom: 100px;
        }

        /* TABLEAU D'ALIGNEMENT */
        .header-row-table {
            width: 100%;
            border-collapse: collapse;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 2px;
            color: black;
            table-layout: fixed;
        }
        .header-row-table td { padding: 0 0 5px 0; border: none; vertical-align: bottom; }

        .w-35 { width: 35%; }
        .w-30 { width: 30%; }
        .text-center { text-align: center; }
        .pl-8 { padding-left: 32px; }

        /* TABLEAU PRINCIPAL */
        table.dist-table { width: 100%; border-collapse: collapse; font-size: 11px; table-layout: fixed; }
        table.dist-table td { border: 2px solid #000; padding: 5px 8px; vertical-align: middle; }
        
        .col-label { width: 30%; font-weight: bold; }
        .col-check { width: 25%; }
        .col-dest { width: 45%; background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .checkbox { display: inline-block; width: 10px; height: 10px; border: 2px solid #000; margin-right: 5px; position: relative; top: 2px; }

        /* TEXTE */
        .content-body { text-align: justify; font-size: 12px; line-height: 1.5; margin-top: 20px; }
        .meta-box { margin: 20px 0; }
        .meta-row { margin-bottom: 8px; font-size: 12px; }
        .meta-label { font-weight: bold; text-decoration: underline; margin-right: 5px; }
        .meta-value { font-weight: bold; text-transform: uppercase; }

        @media print {
            body { background-color: white; }
            .page-container { margin: 0; box-shadow: none; }
            #document-frame, header, footer { position: fixed; }
        }
    </style>
</head>
<body>

<div class="page-container">
    <div id="document-frame"></div>

    <header>
        <img src="{{asset('images/logo.jpg')}}" class="logo-img" alt="Logo">
        <div class="direction">DIRECTION TRANSFORMATION DIGITALE ET SYSTÈME D'INFORMATION</div>
        <div class="main-title">Mémorandum</div>
    </header>

    <footer>
        <div class="qr-placeholder">QR CODE</div>
        <div class="ref-text">FOR-ME-07-V1 | Généré le 12/12/2025</div>
    </footer>

    <main>
        <table class="header-row-table">
            <tr>
                <td class="w-35"></td> 
                <td class="w-30 text-center">Prière de :</td>
                <td class="w-35 pl-8">Destinataires :</td>
            </tr>
        </table>

        <table class="dist-table">
            <tr>
                <td class="col-label">Date : 12/12/2025</td>
                <td class="col-check"><span class="checkbox"></span> Faire le nécessaire</td>
                <td class="col-dest">Toutes les Directions</td>
            </tr>
            <tr>
                <td class="col-label">N° : 2998/REF/TEST</td>
                <td class="col-check"><span class="checkbox"></span> Prendre connaissance</td>
                <td class="col-dest">Direction Générale</td>
            </tr>
            <tr>
                <td class="col-label">Emetteur : Jean DUPONT</td>
                <td class="col-check"><span class="checkbox"></span> Prendre position</td>
                <td class="col-dest"></td>
            </tr>
            <tr>
                <td class="col-label">Service : DSI</td>
                <td class="col-check"><span class="checkbox"></span> Décider</td>
                <td class="col-dest"></td>
            </tr>
        </table>

        <div class="meta-box">
            <div class="meta-row">
                <span class="meta-label">Objet :</span>
                <span class="meta-value">Mise en œuvre des nouvelles procédures</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Concerne :</span>
                <span>L'ensemble du personnel de la banque.</span>
            </div>
        </div>

        <div class="content-body">
            <p>L'article 10 du règlement R-2016/04 relatif au contrôle interne prescrit aux établissements assujettis de mettre en œuvre pour chaque risque un système d'identification.</p>
            <p>En application de cette disposition, la DGR en collaboration avec les autres entités métiers a élaboré le document de cartographie des risques.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.</p>
        </div>
    </main>
</div>

</body>
</html>
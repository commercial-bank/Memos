<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Mémo - {{ $memo->reference }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12pt; line-height: 1.5; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #daaf2c; padding-bottom: 10px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; text-transform: uppercase; }
        .meta-table { width: 100%; margin-bottom: 30px; }
        .meta-table td { padding: 5px; vertical-align: top; }
        .label { font-weight: bold; width: 120px; }
        .content { text-align: justify; margin-bottom: 50px; min-height: 200px; }
        .signatures { width: 100%; margin-top: 50px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .box-signature { height: 100px; border: 1px dashed #ccc; margin-top: 10px; padding-top: 40px; color: #999; font-size: 10pt; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; height: 30px; border-top: 1px solid #eee; text-align: center; font-size: 9pt; color: #777; padding-top: 10px; }
        .status-stamp { position: absolute; top: 150px; right: 50px; border: 3px solid #ccc; color: #ccc; padding: 10px 20px; font-weight: bold; text-transform: uppercase; transform: rotate(-15deg); font-size: 20px; opacity: 0.5; }
        .status-signed { border-color: green; color: green; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="logo">NOTE DE SERVICE</div>
        <div style="font-size: 10pt; color: #777;">République du Cameroun</div>
    </div>

    <!-- Tampon si signé -->
    @if(!empty($memo->signature_dir))
        <div class="status-stamp status-signed">VALIDÉ & SIGNÉ</div>
    @endif

    <!-- Infos Méta -->
    <table class="meta-table">
        <tr>
            <td class="label">RÉFÉRENCE :</td>
            <td><strong>{{ $memo->reference ?? 'EN COURS D\'ATTRIBUTION' }}</strong></td>
            <td class="label">DATE :</td>
            <td>{{ $memo->created_at->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">DE :</td>
            <td>{{ $memo->user->first_name }} {{ $memo->user->last_name }}<br><small><i>{{ $memo->user->poste }}</i></small></td>
            <td class="label">POUR :</td>
            <td>
                <!-- Liste des destinataires -->
                Direction Générale
            </td>
        </tr>
        <tr>
            <td class="label">OBJET :</td>
            <td colspan="3" style="border-bottom: 1px solid #eee;"><strong>{{ $memo->object }}</strong></td>
        </tr>
    </table>

    <!-- Contenu -->
    <div class="content">
        {!! nl2br(e($memo->content)) !!}
    </div>

    <!-- Signatures -->
    <table class="signatures">
        <tr>
            <td>
                <strong>L'Initiateur</strong>
                <div class="box-signature">
                    Signé numériquement par<br>
                    {{ $memo->user->last_name }}
                </div>
            </td>
            <td>
                <strong>La Direction</strong>
                <div class="box-signature" style="{{ !empty($memo->signature_dir) ? 'border: 2px solid green; color: green;' : '' }}">
                    @if(!empty($memo->signature_dir))
                        APPROUVÉ<br>
                        {{ \Carbon\Carbon::parse($memo->updated_at)->format('d/m/Y') }}
                    @else
                        En attente de visa
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        Document généré automatiquement par le Système de Gestion Électronique de Documents (GED)
        | ID Unique: {{ $memo->id }}
    </div>

</body>
</html>
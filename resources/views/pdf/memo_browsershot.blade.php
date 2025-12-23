<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: white;
            -webkit-print-color-adjust: exact;
        }
        .page-a4 {
            width: 210mm;
            height: 296mm; /* Un peu moins que 297 pour éviter les sauts de page vides */
            padding: 10mm;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        .gold-frame {
            flex-grow: 1;
            border: 4px solid #D4AF37;
            border-top-right-radius: 60px;
            border-bottom-left-radius: 60px;
            padding: 15mm;
            display: flex;
            flex-direction: column;
        }
        .content-html {
            font-family: 'serif';
            font-size: 14px;
            line-height: 1.6;
            text-align: justify;
        }
        /* Assure que les tableaux dans le contenu Quill ne débordent pas */
        .content-html table { width: 100% !important; border-collapse: collapse; }
        .content-html td { border: 1px solid #ccc; padding: 4px; }
    </style>
</head>
<body>
    <div class="page-a4">
        <div class="gold-frame">
            <!-- HEADER -->
            <div class="text-center mb-6">
                <img src="{{ public_path('images/logo.jpg') }}" class="w-20 h-20 mx-auto mb-2 object-contain">
                <h2 class="font-bold text-xs uppercase text-gray-800">{{ $user_entity_name }}</h2>
                <h1 class="text-3xl font-extrabold uppercase mt-4 italic border-b-4 border-black inline-block px-6 pb-2">
                    Memorandum
                </h1>
            </div>

            <!-- INFOS DESTINATAIRES -->
            <div class="mb-6">
                <table class="w-full border-collapse border-2 border-black text-xs">
                    @foreach(['Faire le nécessaire', 'Prendre connaissance', 'Prendre position', 'Décider'] as $action)
                        @php $group = $recipientsByAction[$action] ?? collect([]); @endphp
                        <tr>
                            <td class="border-2 border-black p-2 font-bold w-1/3 bg-gray-50">
                                @if($loop->first) 
                                    Date : {{ $memo->created_at->format('d/m/Y') }} 
                                @elseif($loop->iteration == 2) 
                                    N° : {{ $memo->reference ?? 'En attente' }} 
                                @else 
                                    &nbsp; 
                                @endif
                            </td>
                            <td class="border-2 border-black p-2 w-1/4">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 border-2 border-black mr-2 {{ $group->count() > 0 ? 'bg-black' : '' }}"></div>
                                    {{ $action }}
                                </div>
                            </td>
                            <td class="border-2 border-black p-2 font-bold text-center">
                                {{ $group->map(fn($d) => $d->entity->ref ?? $d->entity->name)->join(', ') }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>

            <!-- OBJET & CONCERNE -->
            <div class="mb-8 space-y-2">
                <p class="text-lg"><span class="font-bold underline">Objet :</span> <span class="uppercase font-extrabold">{{ $memo->object }}</span></p>
                <p class="text-lg"><span class="font-bold underline">Concerne :</span> <span class="capitalize">{{ $memo->concern }}</span></p>
            </div>

            <!-- CORPS DU MÉMO -->
            <div class="content-html flex-grow">
                {!! $memo->content !!}
            </div>

            <!-- PIED DE PAGE & SIGNATURES (Optionnel) -->
            <div class="mt-auto pt-6 flex justify-between items-end border-t border-gray-100">
                <div class="text-[10px] text-gray-400 italic">
                    Réf Système: {{ $memo->numero_ref }} / {{ Auth::user()->user_name }}
                </div>
                @if($memo->qr_code)
                    <div class="w-20 h-20 bg-gray-100 flex items-center justify-center">
                        {{-- On laisse Browsershot générer le QR code ou on l'affiche ici --}}
                        <img src="data:image/png;base64, {!! base64_encode(QrCode::size(80)->generate(route('memo.verify', $memo->qr_code))) !!}">
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
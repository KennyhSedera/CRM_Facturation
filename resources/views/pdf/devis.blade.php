<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis {{ $quote->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { margin-bottom: 10px; }
        .header, .section, .conditions, .totals { margin-bottom: 15px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #000; padding: 6px; text-align: left; }
        .right { text-align: right; }
        .center { text-align: center; }
        .borderless td { border: none; }
        .totals td { font-weight: bold; }
        .qr-code { margin-top: 20px; }
    </style>
</head>
<body>

    <h2>8.2 Structure PDF Template</h2>

    <div class="header">
        <table class="borderless" style="width: 100%;">
            <tr>
                <td><strong>DEVIS N°:</strong> {{ $quote->number }}</td>
                <td class="right"><strong>Date:</strong> {{ \Carbon\Carbon::parse($quote->date)->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="borderless" style="width: 100%;">
            <tr>
                <td>
                    <strong>INSTALLATEUR:</strong><br>
                    {{ $quote->user->name }}<br>
                    [Nom Société]<br>
                    [Adresse]<br>
                    [Téléphone]
                </td>
                <td>
                    <strong>CLIENT:</strong><br>
                    {{ $quote->client->name }}<br>
                    {{ $quote->client->phone }}<br>
                    {{ $quote->client->address }}
                </td>
            </tr>
        </table>
    </div>

    <p><strong>OBJET:</strong> Installation solaire résidentielle</p>

    <table class="table">
        <thead>
            <tr>
                <th>DÉSIGNATION</th>
                <th class="center">QTE</th>
                <th class="right">PRIX UNIT</th>
                <th class="right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->price, 0, ',', ' ') }} CFA</td>
                    <td class="right">{{ number_format($item->price * $item->quantity, 0, ',', ' ') }} CFA</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $sousTotal = $quote->items->sum(fn($i) => $i->price * $i->quantity);
        $tva = $sousTotal * 0.18;
        $total = $sousTotal + $tva;
    @endphp

    <table class="table totals" style="width: 100%;">
        <tr>
            <td colspan="3" class="right">SOUS-TOTAL:</td>
            <td class="right">{{ number_format($sousTotal, 0, ',', ' ') }} CFA</td>
        </tr>
        <tr>
            <td colspan="3" class="right">TVA (18%):</td>
            <td class="right">{{ number_format($tva, 0, ',', ' ') }} CFA</td>
        </tr>
        <tr>
            <td colspan="3" class="right">TOTAL:</td>
            <td class="right">{{ number_format($total, 0, ',', ' ') }} CFA</td>
        </tr>
    </table>

    <div class="conditions">
        <p><strong>CONDITIONS:</strong></p>
        <ul>
            <li>Devis valable 30 jours</li>
            <li>Accompte: 50% à la commande</li>
            <li>Garantie: 2 ans installation, 25 ans panneaux</li>
            <li>Délai: 5-7 jours ouvrables</li>
        </ul>
    </div>

    <div class="qr-code">
        <p><strong>[QR Code:</strong> Lien vérification devis]</p>
    </div>

</body>
</html>

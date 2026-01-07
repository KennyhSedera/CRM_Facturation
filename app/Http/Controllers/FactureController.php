<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FactureController extends Controller
{
    public function sendFacture(Request $request)
    {
        $chatId = $request->input('message.chat.id');
        $quoteId = 1; // exemple

        $quote = Quote::findOrFail($quoteId);

        $pdf = Pdf::loadView('pdf.facture', ['quote' => $quote]);
        $fileName = 'facture_' . $quote->id . '.pdf';
        $pdfPath = storage_path('app/public/' . $fileName);

        file_put_contents($pdfPath, $pdf->output());

        $telegram = new \Telegram\Bot\Api(config('telegram.bots.mybot.token'));

        $telegram->sendDocument([
            'chat_id' => $chatId,
            'document' => fopen($pdfPath, 'r'),
            'caption' => '📄 Voici votre facture',
        ]);

        return response()->json(['message' => 'Facture envoyée']);
    }
}

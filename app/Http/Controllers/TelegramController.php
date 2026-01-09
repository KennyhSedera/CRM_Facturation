<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class TelegramController extends Controller
{
    // public function handle(Request $request, Nutgram $bot)
    // {

    //     try {
    //         // Nutgram va automatiquement charger routes/telegram.php
    //         $bot->run();

    //         Log::info('Webhook processed successfully');

    //         return response()->json(['ok' => true]);

    //     } catch (\Throwable $e) {
    //         Log::error('Telegram webhook error', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json(['error' => 'Internal error'], 500);
    //     }
    // }

    public function handle(Request $request, Nutgram $bot)
{
    try {
        $bot->run();
        
        return response()->json(['ok' => true]);
    } catch (\Throwable $e) {
        Log::error('Telegram webhook error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'Internal error'], 500);
    }
}

}

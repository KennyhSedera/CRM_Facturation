<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;

class StartBotController extends Controller
{

    public function sendMessage()
    {
        $telegramService = app(TelegramService::class);
        $telegramService->sendMessage(env('TELEGRAM_CHAT_ID'),'Bot started succesfully');
        return response()->json(['message' => 'Bot started']);
    }
}

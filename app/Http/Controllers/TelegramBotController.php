<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Services\CallbackService;

class TelegramBotController extends Controller
{
    protected $callbackService;

    public function __construct(CallbackService $callbackService)
    {
        $this->callbackService = $callbackService;
    }

    public function handle(Request $request)
    {
        $data = $request->all();
        $callback = $data['callback_query'] ?? null;
        $message = $data['message'] ?? null;

        // ✅ PRIORITÉ 1: Traiter les callbacks EN PREMIER
        if ($callback) {
            $this->callbackService->handleCallback($callback);
            return response('ok', 200); // ← ARRÊT ICI !
        }

        // ✅ PRIORITÉ 2: Traiter les messages transférés
        if ($message && $this->handleForwardedMessage($message)) {
            return response('ok', 200);
        }

        // ✅ PRIORITÉ 3: Traiter les commandes normales
        if ($message && isset($message['text']) && str_starts_with($message['text'], '/')) {
            Telegram::commandsHandler(true);
            return response('ok', 200);
        }

        // ✅ PRIORITÉ 4: Traiter les messages texte pour la recherche
        if ($message && isset($message['text'])) {
            $this->handleTextMessage($message);
        }

        return response('ok', 200);
    }

    private function handleForwardedMessage(array $message)
    {
        $chatId = $message['chat']['id'] ?? null;

        // Vérifier si le message est transféré
        if (isset($message['forward_from']) || isset($message['forward_from_chat'])) {
            $getMyIdCommand = new \App\Telegram\Commands\GetMyIdCommand();

            // Simuler un update pour la commande
            $update = Telegram::getWebhookUpdate();
            $getMyIdCommand->setUpdate($update);

            return $getMyIdCommand->handleForwardedMessage($message, $chatId);
        }

        return false;
    }

    private function handleTextMessage(array $message)
    {
        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? '';

        // Si c'est un message texte normal (pas une commande), on fait une recherche globale
        if (!empty($text) && $chatId && !str_starts_with($text, '/')) {
            $searchCommand = new \App\Telegram\Commands\RechercheCommand();
            $searchCommand->searchGlobal($text, $chatId);
        }
    }
}

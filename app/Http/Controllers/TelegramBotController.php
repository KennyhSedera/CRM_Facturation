<?php

namespace App\Http\Controllers;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;
use Illuminate\Http\Request;
use App\Models\FormSubmission;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends Controller
{
    public function handle(Request $request)
    {
        $bot = new Nutgram(config('services.telegram.bot_token'));

        // Commande /start - Affiche le bouton du formulaire
        $bot->onCommand('start', function (Nutgram $bot) {
            $webAppUrl = route('webapp.form', ['user_id' => $bot->userId()]);

            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        text: '📝 Remplir le formulaire',
                        web_app: new WebAppInfo($webAppUrl)
                    )
                );

            $bot->sendMessage(
                text: "👋 Bienvenue !\n\nCliquez sur le bouton ci-dessous pour remplir le formulaire :",
                reply_markup: $keyboard
            );
        });

        // Réception des données du Web App
        $bot->onMessage(function (Nutgram $bot) {
            if ($bot->message()->web_app_data) {
                $data = json_decode($bot->message()->web_app_data->data, true);

                // Sauvegarder en base de données
                Log::info('Form submission received', [
                    'user_id' => $bot->userId(),
                    'nom' => $data['nom'],
                    'email' => $data['email'],
                    'telephone' => $data['telephone'] ?? null,
                    'message' => $data['message'],
                    'submitted_at' => now(),
                ]);

                // Envoyer une confirmation
                $bot->sendMessage(
                    text: "✅ Formulaire reçu avec succès !\n\n" .
                    "📝 Récapitulatif :\n" .
                    "━━━━━━━━━━━━━━━\n" .
                    "👤 Nom : {$data['nom']}\n" .
                    "📧 Email : {$data['email']}\n" .
                    "📱 Téléphone : " . ($data['telephone'] ?? 'Non renseigné') . "\n" .
                    "💬 Message : {$data['message']}\n\n" .
                    "Nous vous recontacterons bientôt ! 🚀"
                );
            }
        });

        $bot->run();

        return response()->json(['status' => 'ok']);
    }
}

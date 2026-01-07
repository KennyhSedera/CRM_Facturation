<?php

namespace App\Telegram\Handlers;

use SergiX44\Nutgram\Nutgram;
use Illuminate\Support\Facades\Log;
use App\Models\Ticket;
use App\Telegram\Keyboards\MainMenuKeyboard;

class TextHandler
{
    /**
     * Gérer tous les messages texte
     */
    public function handle(Nutgram $bot): void
    {
        $message = $bot->message();

        // Vérifier que c'est bien un message texte
        if (!$message || !$message->text) {
            return;
        }

        $text = trim($message->text);
        $userId = $bot->userId();

        // Ignorer les commandes (elles sont gérées ailleurs)
        if (str_starts_with($text, '/')) {
            return;
        }

        // 2. Détection de mots-clés pour réponses automatiques
        if ($this->handleKeywords($bot, $text)) {
            return;
        }

        // 3. Détection d'intentions (questions fréquentes)
        if ($this->handleIntents($bot, $text)) {
            return;
        }

        // 4. Réponse par défaut si rien ne correspond
        $this->handleDefault($bot, $text);
    }

    /**
     * Détecter des mots-clés spécifiques
     */
    private function handleKeywords(Nutgram $bot, string $text): bool
    {
        $textLower = mb_strtolower($text);

        // Salutations
        if (preg_match('/\b(bonjour|salut|hello|hi|hey|bonsoir)\b/iu', $textLower)) {
            $user = $bot->user();
            $hour = (int) date('H');

            $greeting = match (true) {
                $hour < 12 => '🌅 Bonjour',
                $hour < 18 => '☀️ Bon après-midi',
                default => '🌙 Bonsoir'
            };

            $bot->sendMessage(
                "{$greeting} {$user->first_name} !\n\n"
                . "Comment puis-je vous aider aujourd'hui ?\n"
                . "Utilisez /help pour voir les commandes disponibles.",
                reply_markup: MainMenuKeyboard::make()
            );
            return true;
        }

        // Remerciements
        if (preg_match('/\b(merci|thanks|thank you|gracias)\b/iu', $textLower)) {
            $bot->sendMessage(
                "😊 De rien ! Je suis là pour vous aider.\n\n"
                . "N'hésitez pas si vous avez d'autres questions !"
            );
            return true;
        }

        // Au revoir
        if (preg_match('/\b(au revoir|bye|goodbye|adieu|ciao)\b/iu', $textLower)) {
            $bot->sendMessage(
                "👋 Au revoir ! À bientôt !\n\n"
                . "N'hésitez pas à revenir si vous avez besoin d'aide."
            );
            return true;
        }

        // Urgence
        if (preg_match('/\b(urgent|urgence|help|aide|sos)\b/iu', $textLower)) {
            $bot->sendMessage(
                "🚨 <b>Besoin d'aide urgente ?</b>\n\n"
                . "Créez un ticket urgent avec /ticket\n"
                . "ou contactez-nous directement :\n\n"
                . "📞 Tel : +261 34 00 000 00\n"
                . "📧 Email : support@example.com",
                parse_mode: 'HTML'
            );
            return true;
        }

        return false;
    }

    /**
     * Détecter des intentions (questions fréquentes)
     */
    private function handleIntents(Nutgram $bot, string $text): bool
    {
        $textLower = mb_strtolower($text);

        // Prix / Tarifs
        if (preg_match('/\b(prix|tarif|coût|combien|cost|price)\b/iu', $textLower)) {
            $bot->sendMessage(
                "💰 <b>Nos Tarifs</b>\n\n"
                . "• Plan Basic : 9.99€/mois\n"
                . "• Plan Pro : 19.99€/mois\n"
                . "• Plan Enterprise : Sur devis\n\n"
                . "Tous nos plans incluent :\n"
                . "✓ Support 24/7\n"
                . "✓ Mises à jour gratuites\n"
                . "✓ Garantie satisfaction\n\n"
                . "Pour plus d'infos : /help",
                parse_mode: 'HTML'
            );
            return true;
        }

        // Livraison
        if (preg_match('/\b(livraison|shipping|délai|delivery)\b/iu', $textLower)) {
            $bot->sendMessage(
                "🚚 <b>Informations Livraison</b>\n\n"
                . "• Standard : 3-5 jours ouvrés\n"
                . "• Express : 1-2 jours ouvrés\n"
                . "• International : 7-14 jours\n\n"
                . "Livraison gratuite à partir de 50€ !",
                parse_mode: 'HTML'
            );
            return true;
        }

        // Retour / Remboursement
        if (preg_match('/\b(retour|remboursement|annulation|return|refund)\b/iu', $textLower)) {
            $bot->sendMessage(
                "↩️ <b>Politique de Retour</b>\n\n"
                . "• Retours acceptés sous 30 jours\n"
                . "• Produit non utilisé\n"
                . "• Remboursement sous 7 jours\n\n"
                . "Pour initier un retour, créez un ticket : /ticket",
                parse_mode: 'HTML'
            );
            return true;
        }

        // Contact / Support
        if (preg_match('/\b(contact|support|joindre|appeler|call)\b/iu', $textLower)) {
            $bot->sendMessage(
                "📞 <b>Nous Contacter</b>\n\n"
                . "📧 Email : support@example.com\n"
                . "📱 Tel : +261 34 00 000 00\n"
                . "🕐 Horaires : Lun-Ven 9h-18h\n\n"
                . "Ou créez un ticket : /ticket",
                parse_mode: 'HTML'
            );
            return true;
        }

        // Horaires
        if (preg_match('/\b(horaire|ouvert|hours|schedule)\b/iu', $textLower)) {
            $bot->sendMessage(
                "🕐 <b>Nos Horaires</b>\n\n"
                . "Lundi - Vendredi : 9h - 18h\n"
                . "Samedi : 10h - 16h\n"
                . "Dimanche : Fermé\n\n"
                . "Support en ligne 24/7 via le bot !",
                parse_mode: 'HTML'
            );
            return true;
        }

        return false;
    }

    /**
     * Réponse par défaut si rien ne correspond
     */
    private function handleDefault(Nutgram $bot, string $text): void
    {
        Log::info("No handler matched for text", ['text' => $text]);

        // Réponse intelligente basée sur la longueur du message
        if (strlen($text) > 100) {
            // Message long = probablement un problème détaillé
            $bot->sendMessage(
                "📝 J'ai bien reçu votre message.\n\n"
                . "Pour un traitement optimal de votre demande, "
                . "je vous invite à créer un ticket : /ticket\n\n"
                . "Notre équipe vous répondra rapidement."
            );
        } else {
            // Message court = question simple
            $bot->sendMessage(
                "Je n'ai pas compris votre demande. 🤔\n\n"
                . "Voici ce que je peux faire :\n\n"
                . "🎫 Créer un ticket : /ticket\n"
                . "📋 Voir mes tickets : /mytickets\n"
                . "📖 Aide : /help\n\n"
                . "Ou utilisez les boutons ci-dessous :",
                reply_markup: MainMenuKeyboard::make()
            );
        }
    }
}

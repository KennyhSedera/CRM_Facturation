<?php

namespace App\Telegram\Commands;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use Illuminate\Support\Facades\Log;

class HelpCommand extends Command
{
    protected string $command = 'help';
    protected ?string $description = 'Afficher l\'aide et les commandes disponibles';
    private ?Nutgram $bot = null;

    /**
     * Gérer la commande /help
     */
    public function handle(Nutgram $bot): void
    {
        Log::info("User {$bot->userId()} requested help");

        $this->bot = $bot;
        $message = $this->getHelpMessage();
        $keyboard = $this->getHelpKeyboard();

        $bot->sendMessage(
            text: $message,
            reply_markup: $keyboard,
            parse_mode: 'HTML'
        );
    }

    /**
     * Obtenir le message d'aide principal
     */
    private function getHelpMessage(): string
    {
        return "📖 <b>Aide et Documentation</b>\n\n"
            . "Voici toutes les commandes disponibles pour utiliser ce bot :\n\n"
            . $this->getCommandsList()
            . "\n"
            . $this->getContactInfo()
            . "\n"
            . $this->getUsageTips();
    }

    /**
     * Vérifier si l'utilisateur est super admin
     */
    private static function isSuperAdmin(Nutgram $bot): bool
    {
        $user = \App\Models\User::where('telegram_id', $bot->userId())->first();
        return $user && $user->user_role === 'super_admin';
    }

    /**
     * Liste complète des commandes disponibles (obsolète, utiliser buildCommandsList)
     */
    private function getCommandsList(): string
    {
        $isSuperAdmin = $this->bot && self::isSuperAdmin($this->bot);
        return self::buildCommandsList($isSuperAdmin);
    }

    /**
     * Informations de contact (obsolète, utiliser getContactInfoStatic)
     */
    private function getContactInfo(): string
    {
        return self::getContactInfoStatic();
    }

    /**
     * Conseils d'utilisation (obsolète, utiliser getUsageTipsStatic)
     */
    private function getUsageTips(): string
    {
        return self::getUsageTipsStatic();
    }

    /**
     * Clavier avec options d'aide
     */
    private function getHelpKeyboard(): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('📞 Contact', callback_data: 'help_contact'),
                InlineKeyboardButton::make('📚 Guide d\'utilisation', callback_data: 'help_guide')
            )
            ->addRow(
                InlineKeyboardButton::make('💳 Abonnements', callback_data: 'help_subscription'),
                InlineKeyboardButton::make('👥 Clients', callback_data: 'help_clients')
            )
            ->addRow(
                InlineKeyboardButton::make('🏠 Menu Principal', callback_data: 'menu_back')
            );
    }

    /**
     * Afficher la FAQ complète
     */
    public static function showFaq(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $message = "❓ <b>Questions Fréquentes (FAQ)</b>\n\n"
            . "<b>Comment créer un ticket ?</b>\n"
            . "Utilisez la commande /ticket ou cliquez sur le bouton correspondant dans le menu.\n\n"

            . "<b>Combien de temps pour une réponse ?</b>\n"
            . "• Tickets normaux : 24-48h\n"
            . "• Tickets urgents : 2-4h\n"
            . "• Support premium : réponse prioritaire\n\n"

            . "<b>Comment voir mes tickets ?</b>\n"
            . "Utilisez /mytickets pour voir tous vos tickets avec leur statut actuel.\n\n"

            . "<b>Comment créer une entreprise ?</b>\n"
            . "Utilisez /createcompany et suivez les étapes guidées. Vous devrez fournir :\n"
            . "- Nom de l'entreprise\n"
            . "- Adresse\n"
            . "- Numéro de téléphone\n"
            . "- Choisir un plan d'abonnement\n\n"

            . "<b>Comment ajouter des clients ?</b>\n"
            . "Utilisez /clients puis cliquez sur \"Ajouter un client\". Fournissez les informations demandées.\n\n"

            . "<b>Quelles sont les limites du plan gratuit ?</b>\n"
            . "• 3 clients maximum\n"
            . "• 5 devis par mois\n"
            . "• 100 MB de stockage\n"
            . "• 1 membre d'équipe\n"
            . "• Catalogue générique uniquement\n\n"

            . "<b>Comment payer mon abonnement ?</b>\n"
            . "Utilisez /subscription, choisissez votre plan, puis suivez les instructions pour le paiement via Mobile Money ou virement bancaire.\n\n"

            . "<b>Puis-je envoyer des fichiers ?</b>\n"
            . "Oui ! Vous pouvez envoyer des photos et documents (PDF, images) comme preuves de paiement.\n\n"

            . "<b>Comment changer de plan ?</b>\n"
            . "Utilisez /subscription et choisissez \"Changer de plan\" pour faire un upgrade ou downgrade.\n\n"

            . "<b>Comment annuler un processus ?</b>\n"
            . "Tapez /cancel à tout moment pour annuler l'action en cours.\n\n"

            . "<b>Quelle est la différence entre les plans ?</b>\n"
            . "Consultez le guide des abonnements pour voir le détail complet des fonctionnalités de chaque plan.";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('💳 Voir Abonnements', callback_data: 'help_subscription'),
                InlineKeyboardButton::make('📞 Contact', callback_data: 'help_contact')
            )
            ->addRow(
                InlineKeyboardButton::make('⬅️ Retour à l\'aide', callback_data: 'help_back')
            );

        $bot->editMessageText(
            text: $message,
            reply_markup: $keyboard,
            parse_mode: 'HTML'
        );
    }

    /**
     * Afficher les informations de contact
     */
    public static function showContact(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $message = "📞 <b>Nous Contacter</b>\n\n"
            . "<b>Support Client :</b>\n"
            . "📧 Email : kennyhsedera@gmail.com\n"
            . "📱 Tel : +261 34 92 879 65\n"
            . "💬 WhatsApp : +261 34 92 879 65\n\n"

            . "<b>Horaires d'ouverture :</b>\n"
            . "🕐 Lundi - Vendredi : 9h00 - 18h00\n"
            . "🕐 Samedi : 10h00 - 16h00\n"
            . "🕐 Dimanche : Fermé\n\n"

            . "<b>Adresse :</b>\n"
            . "📍 Antananarivo, Madagascar\n\n"

            . "<b>Réseaux Sociaux :</b>\n"
            . "🔵 Facebook : @VotreEntreprise\n"
            . "📷 Instagram : @VotreEntreprise\n"
            . "🐦 Twitter : @VotreEntreprise\n\n"

            . "⚡ <b>Support en ligne 24/7 via ce bot Telegram !</b>\n\n"
            . "Pour une assistance rapide :\n"
            . "• Créez un ticket : /ticket\n"
            . "• Consultez la FAQ : /faq\n"
            . "• Vérifiez votre abonnement : /subscription\n\n"

            . "<b>Support WhatsApp</b> disponible pour les abonnés Premium et Entreprise !";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('📧 Email', url: 'https://mail.google.com/mail/u/0/#inbox?compose=jrjtXSqLMrPkFrVRdXWhQMGRtKVgbNzpBKFXqDqPlTZRgCSpfkwcDbVgNQGQBfnhXnddpdnB'),
                InlineKeyboardButton::make(
                    '💬 WhatsApp',
                    url: 'https://wa.me/261349287065'
                )

            )
            ->addRow(
                InlineKeyboardButton::make('⬅️ Retour', callback_data: 'help_back')
            );

        $bot->editMessageText(
            text: $message,
            reply_markup: $keyboard,
            parse_mode: 'HTML'
        );
    }

    /**
     * Afficher le guide d'utilisation
     */
    public static function showGuide(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $message = "📚 <b>Guide d'Utilisation Complet</b>\n\n"
            . "<b>1️⃣ Démarrage</b>\n"
            . "• Utilisez /start pour initialiser le bot\n"
            . "• Créez votre entreprise avec /createcompany\n"
            . "• Commencez avec le plan gratuit (3 clients, 5 devis/mois)\n"
            . "• Passez à Premium ou Entreprise pour plus de fonctionnalités\n\n"

            . "<b>2️⃣ Gérer votre Entreprise</b>\n"
            . "• Consultez votre profil : /profile\n"
            . "• Vérifiez votre abonnement : /subscription\n"
            . "• Ajoutez des clients : /clients\n"
            . "• Suivez vos limites selon votre plan\n\n"

            . "<b>3️⃣ Créer un Ticket</b>\n"
            . "• Utilisez /ticket\n"
            . "• Choisissez la catégorie appropriée\n"
            . "• Décrivez clairement votre problème\n"
            . "• Ajoutez des captures d'écran si nécessaire\n"
            . "• Suivez le statut avec /mytickets\n\n"

            . "<b>4️⃣ Gestion des Clients</b>\n"
            . "• Accédez au menu : /clients\n"
            . "• Limites selon plan :\n"
            . "  - Gratuit : 3 clients max\n"
            . "  - Premium : illimité\n"
            . "  - Entreprise : illimité\n"
            . "• Ajouter : cliquez sur \"Ajouter un client\"\n"
            . "• Voir : liste de tous vos clients\n"
            . "• Supprimer : avec confirmation de sécurité\n\n"

            . "<b>5️⃣ Paiements</b>\n"
            . "• Choisissez votre méthode (Mobile Money ou Banque)\n"
            . "• Suivez les instructions de paiement\n"
            . "• Envoyez la preuve (photo ou numéro de transaction)\n"
            . "• Attendez la validation (notification automatique)\n"
            . "• Monnaie : FCFA\n\n"

            . "<b>6️⃣ Navigation</b>\n"
            . "• Utilisez les boutons pour naviguer facilement\n"
            . "• /menu pour revenir au menu principal\n"
            . "• /cancel pour annuler une action\n"
            . "• Les notifications sont automatiques\n\n"

            . "<b>7️⃣ Aide Rapide</b>\n"
            . "• Tapez votre question directement\n"
            . "• Mots-clés détectés : prix, livraison, retour, etc.\n"
            . "• Support réactif via tickets";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('🎯 Créer une Entreprise', callback_data: 'help_company'),
                InlineKeyboardButton::make('👥 Guide Clients', callback_data: 'help_clients')
            )
            ->addRow(
                InlineKeyboardButton::make('💳 Guide Abonnements', callback_data: 'help_subscription'),
                InlineKeyboardButton::make('❓ Voir la FAQ', callback_data: 'help_faq')
            )
            ->addRow(
                InlineKeyboardButton::make('⬅️ Retour à l\'aide', callback_data: 'help_back')
            );

        $bot->editMessageText(
            text: $message,
            reply_markup: $keyboard,
            parse_mode: 'HTML'
        );
    }

    /**
     * Aide pour la gestion des clients
     */
    public static function showClientsHelp(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $message = "👥 <b>Guide de Gestion des Clients</b>\n\n"
            . "<b>Commande principale : /clients</b>\n\n"

            . "<b>📊 Limites par Plan :</b>\n"
            . "🆓 Gratuit : 3 clients maximum\n"
            . "⭐ Premium : Illimité\n"
            . "🏢 Entreprise : Illimité\n\n"

            . "<b>➕ Ajouter un Client</b>\n"
            . "1. Cliquez sur \"Ajouter un client\"\n"
            . "2. Entrez le nom complet\n"
            . "3. Entrez l'email\n"
            . "4. Entrez le numéro de téléphone\n"
            . "5. Entrez l'adresse (optionnel)\n\n"

            . "<b>📋 Voir vos Clients</b>\n"
            . "• Liste complète avec détails\n"
            . "• Informations de contact\n"
            . "• Date d'ajout\n"
            . "• Nombre total de clients affichés\n\n"

            . "<b>👁️ Détails d'un Client</b>\n"
            . "• Cliquez sur un client dans la liste\n"
            . "• Voir toutes les informations\n"
            . "• Options d'actions disponibles\n"
            . "• Historique (selon plan)\n\n"

            . "<b>🗑️ Supprimer un Client</b>\n"
            . "• Sélectionnez le client\n"
            . "• Cliquez sur \"Supprimer\"\n"
            . "• Confirmez la suppression\n"
            . "• ⚠️ Action irréversible !\n\n"

            . "<b>📄 Créer des Devis :</b>\n"
            . "🆓 Gratuit : 5 devis/mois\n"
            . "⭐ Premium : Illimité\n"
            . "🏢 Entreprise : Illimité\n\n"

            . "<b>🔜 Fonctionnalités à venir :</b>\n"
            . "• Édition des informations clients\n"
            . "• Recherche avancée\n"
            . "• Création de devis personnalisés\n"
            . "• Historique des transactions\n"
            . "• Export des données (Premium+)\n\n"

            . "<b>💡 Conseils :</b>\n"
            . "• Gardez les informations à jour\n"
            . "• Utilisez des emails valides\n"
            . "• Format téléphone : +261 XX XX XXX XX\n"
            . "• Passez à Premium pour clients illimités";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('👥 Gérer mes Clients', callback_data: 'client_menu'),
                InlineKeyboardButton::make('⭐ Passer à Premium', callback_data: 'help_subscription')
            )

            ->addRow(
                InlineKeyboardButton::make('📚 Guide Principal', callback_data: 'help_guide')
            )
            ->addRow(
                InlineKeyboardButton::make('⬅️ Retour à l\'aide', callback_data: 'help_back')
            );

        $bot->editMessageText(
            text: $message,
            reply_markup: $keyboard,
            parse_mode: 'HTML'
        );
    }

    /**
     * Aide pour les abonnements
     */
    public static function showSubscriptionHelp(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $message = "💳 <b>Guide des Abonnements</b>\n\n"
            . "<b>Commande principale : /subscription</b>\n\n"

            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "🆓 <b>PLAN GRATUIT - 0 FCFA</b>\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "<b>Limites :</b>\n"
            . "• 👥 3 clients maximum\n"
            . "• 📄 5 devis par mois\n"
            . "• 💾 100 MB de stockage\n"
            . "• 👤 1 membre d'équipe\n"
            . "• ❌ Pas de produits personnalisés\n\n"
            . "<b>Fonctionnalités :</b>\n"
            . "✅ Calculateur de devis\n"
            . "✅ Catalogue générique\n"
            . "✅ Génération PDF\n"
            . "✅ Support par email\n"
            . "❌ Logo personnalisé\n"
            . "❌ Statistiques avancées\n"
            . "❌ Accès API\n\n"

            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "⭐ <b>PLAN PREMIUM - 9 900 FCFA/mois</b>\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "<b>Limites :</b>\n"
            . "• 👥 Clients illimités\n"
            . "• 📄 Devis illimités\n"
            . "• 💾 5 Go de stockage\n"
            . "• 👥 5 membres d'équipe\n"
            . "• ✅ Produits personnalisés illimités\n\n"
            . "<b>Fonctionnalités :</b>\n"
            . "✅ Toutes les fonctionnalités Gratuites\n"
            . "✅ Logo personnalisé\n"
            . "✅ Produits personnalisés\n"
            . "✅ Statistiques avancées\n"
            . "✅ Multi-devises\n"
            . "✅ Export de données\n"
            . "✅ Support WhatsApp\n"
            . "❌ Accès API\n"
            . "❌ Support prioritaire\n\n"

            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "🏢 <b>PLAN ENTREPRISE - 14 900 FCFA</b>\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "<b>Limites :</b>\n"
            . "• ♾️ TOUT ILLIMITÉ\n"
            . "• 👥 Membres d'équipe illimités\n"
            . "• 💾 Stockage illimité\n\n"
            . "<b>Fonctionnalités :</b>\n"
            . "✅ Toutes les fonctionnalités Premium\n"
            . "✅ Accès API complet\n"
            . "✅ Support prioritaire 24/7\n"
            . "✅ Gestionnaire de compte dédié\n"
            . "✅ White label (marque blanche)\n"
            . "✅ Intégrations personnalisées\n"
            . "✅ Garantie SLA\n"
            . "✅ Sessions de formation\n\n"

            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "💰 <b>MÉTHODES DE PAIEMENT</b>\n"
            . "━━━━━━━━━━━━━━━━━━━━\n\n"

            . "📱 <b>Mobile Money</b>\n"
            . "• MVola : *112*1*montant#\n"
            . "• Orange Money : #111#\n"
            . "• Airtel Money : *123#\n"
            . "• Envoyez au : +261 34 92 879 65\n\n"

            . "🏦 <b>Virement Bancaire</b>\n"
            . "• BNI Madagascar\n"
            . "• BOA Madagascar\n"
            . "• Compte : [à compléter]\n\n"

            . "<b>📤 Envoi de Preuve :</b>\n"
            . "• Photo du reçu, OU\n"
            . "• Numéro de transaction\n"
            . "• Validation sous 24h\n\n"

            . "<b>🔄 Gestion :</b>\n"
            . "• Renouvellement automatique ou manuel\n"
            . "• Upgrade/Downgrade à tout moment\n"
            . "• Historique des paiements\n"
            . "• Notifications automatiques\n\n"

            . "💡 <b>Recommandation :</b>\n"
            . "• Débutants → Plan Gratuit\n"
            . "• PME/Freelances → Plan Premium\n"
            . "• Grandes entreprises → Plan Entreprise";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('💳 Mon Abonnement', callback_data: 'subscription_back'),
                InlineKeyboardButton::make('📚 Guide Principal', callback_data: 'help_guide')
            )
            ->addRow(
                InlineKeyboardButton::make('⬅️ Retour', callback_data: 'help_back')
            );

        $bot->editMessageText(
            text: $message,
            reply_markup: $keyboard,
            parse_mode: 'HTML'
        );
    }

    /**
     * Aide pour la création d'entreprise
     */
    public static function showCompanyHelp(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $message = "🏢 <b>Guide de Création d'Entreprise</b>\n\n"
            . "<b>Commande : /createcompany</b>\n\n"

            . "<b>📝 Étapes de Création :</b>\n\n"

            . "<b>1. Nom de l'Entreprise</b>\n"
            . "• Nom complet et officiel\n"
            . "• Exemple : \"SARL MonEntreprise\"\n\n"

            . "<b>2. Adresse</b>\n"
            . "• Adresse complète du siège\n"
            . "• Exemple : \"Lot II M 15 Antananarivo\"\n\n"

            . "<b>3. Numéro de Téléphone</b>\n"
            . "• Format international recommandé\n"
            . "• Exemple : \"+261 34 XX XXX XX\"\n\n"

            . "<b>4. Choix du Plan</b>\n"
            . "🆓 Gratuit : 0 FCFA\n"
            . "  → Idéal pour commencer\n"
            . "  → 3 clients, 5 devis/mois\n\n"
            . "⭐ Premium : 9 900 FCFA/mois\n"
            . "  → Pour PME et freelances\n"
            . "  → Illimité clients et devis\n\n"
            . "🏢 Entreprise : 14 900 FCFA\n"
            . "  → Pour grandes organisations\n"
            . "  → Tout illimité + API\n\n"

            . "<b>5. Paiement (si Premium/Entreprise)</b>\n"
            . "• Choisissez la méthode\n"
            . "• Effectuez le paiement\n"
            . "• Envoyez la preuve\n\n"

            . "<b>⚠️ Important :</b>\n"
            . "• Plan gratuit : activation immédiate\n"
            . "• Plans payants : validation sous 24h\n"
            . "• Toutes les informations sont modifiables\n"
            . "• Utilisez /cancel pour annuler\n"
            . "• Vous recevrez une notification\n\n"

            . "<b>✅ Après Validation :</b>\n"
            . "• Accès complet aux fonctionnalités\n"
            . "• Ajout de clients (selon limites)\n"
            . "• Création de devis\n"
            . "• Support technique selon plan\n\n"

            . "<b>🆓 Avantages du Plan Gratuit :</b>\n"
            . "• Aucun paiement requis\n"
            . "• Activation instantanée\n"
            . "• Idéal pour tester la plateforme\n"
            . "• Upgrade facile vers Premium";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('💳 Voir les Plans', callback_data: 'help_subscription'),
                InlineKeyboardButton::make('📚 Guide Principal', callback_data: 'help_guide')
            )
            ->addRow(
                InlineKeyboardButton::make('⬅️ Retour', callback_data: 'help_back')
            );

        $bot->editMessageText(
            text: $message,
            reply_markup: $keyboard,
            parse_mode: 'HTML'
        );
    }

    /**
     * Retour au message d'aide principal
     */
    public static function showBack(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $user = \App\Models\User::where('telegram_id', $bot->userId())->first();
        $isSuperAdmin = $user && $user->user_role === 'super_admin';

        $message = self::buildHelpMessage($isSuperAdmin);
        $keyboard = self::buildHelpKeyboard();

        $bot->editMessageText(
            text: $message,
            reply_markup: $keyboard,
            parse_mode: 'HTML'
        );
    }

    /**
     * Construire le message d'aide complet
     */
    private static function buildHelpMessage(bool $isSuperAdmin = false): string
    {
        return "📖 <b>Aide et Documentation</b>\n\n"
            . "Voici toutes les commandes disponibles pour utiliser ce bot :\n\n"
            . self::buildCommandsList($isSuperAdmin)
            . "\n"
            . self::getContactInfoStatic()
            . "\n"
            . self::getUsageTipsStatic();
    }

    /**
     * Construire la liste des commandes (version statique)
     */
    private static function buildCommandsList(bool $isSuperAdmin = false): string
    {
        $commands = "<b>📌 Commandes Principales :</b>\n\n"
            . "🏠 <b>/start</b> - Démarrer le bot et voir le menu principal\n"
            . "📖 <b>/help</b> - Afficher cette aide complète\n"
            . "🔄 <b>/cancel</b> - Annuler l'action ou le processus en cours\n\n"

            . "<b>🎫 Gestion des Tickets :</b>\n"
            . "• <b>/ticket</b> - Créer un nouveau ticket de support\n"
            . "• <b>/mytickets</b> - Voir tous mes tickets (ouverts et fermés)\n\n"

            . "<b>🏢 Gestion d'Entreprise :</b>\n"
            . "• <b>/createcompany</b> - Créer une nouvelle entreprise\n"
            . "• <b>/profile</b> - Voir mon profil et mes informations\n\n"

            . "<b>👥 Gestion des Clients :</b>\n"
            . "• <b>/clients</b> - Gérer mes clients (liste, ajout, modification)\n"
            . "  - Ajouter un client\n"
            . "  - Voir les détails d'un client\n"
            . "  - Supprimer un client\n"
            . "  - Créer des devis (selon plan)\n\n"

            . "<b>💳 Abonnements et Paiements :</b>\n"
            . "• <b>/subscription</b> - Gérer mon abonnement\n"
            . "  - Voir le plan actuel\n"
            . "  - Renouveler l'abonnement\n"
            . "  - Changer de plan (upgrade/downgrade)\n"
            . "  - Historique des paiements\n\n";

        // Afficher les commandes admin si super_admin
        if ($isSuperAdmin) {
            $commands .= "<b>👨‍💼 Commandes Administrateur :</b>\n"
                . "• <b>/pendingpayments</b> - Voir les paiements en attente\n"
                . "  - Approuver les paiements\n"
                . "  - Rejeter les paiements\n"
                . "  - Voir les preuves de paiement\n\n";
        }

        $commands .= "<b>ℹ️ Informations :</b>\n"
            . "• <b>/faq</b> - Questions fréquemment posées\n"
            . "• <b>/contact</b> - Nos coordonnées de contact";

        return $commands;
    }

    /**
     * Informations de contact (version statique)
     */
    private static function getContactInfoStatic(): string
    {
        return "<b>📞 Nous Contacter :</b>\n\n"
            . "📧 Email : kennyhsedera@gmail.com\n"
            . "📱 Tel : +261 34 92 879 65\n"
            . "🕐 Horaires : Lun-Ven 9h-18h\n"
            . "🌍 Site : " . config('app.url', 'https://example.com');
    }

    /**
     * Conseils d'utilisation (version statique)
     */
    private static function getUsageTipsStatic(): string
    {
        return "\n<b>💡 Conseils d'utilisation :</b>\n\n"
            . "• Utilisez les <b>boutons interactifs</b> pour une navigation facile\n"
            . "• Tapez votre message directement pour poser une question\n"
            . "• Les mots-clés sont détectés automatiquement\n"
            . "• Vous pouvez envoyer des <b>photos</b> et <b>documents</b> comme preuves de paiement\n"
            . "• Utilisez <b>/cancel</b> à tout moment pour annuler un processus\n"
            . "• Les notifications sont automatiques pour les mises à jour importantes\n\n"
            . "<b>📋 Plans d'abonnement disponibles :</b>\n"
            . "• 🆓 <b>Gratuit</b> - 0 FCFA\n"
            . "• ⭐ <b>Premium</b> - 9 900 FCFA/mois\n"
            . "• 🏢 <b>Entreprise</b> - 14 900 FCFA (tarif personnalisé)\n\n"
            . "<b>💳 Méthodes de paiement :</b>\n"
            . "• Mobile Money (MVola, Orange Money, Airtel Money)\n"
            . "• Virement bancaire";
    }

    /**
     * Construire le clavier d'aide (version statique)
     */
    private static function buildHelpKeyboard(): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('📞 Contact', callback_data: 'help_contact'),
                InlineKeyboardButton::make('📚 Guide d\'utilisation', callback_data: 'help_guide')
            )
            ->addRow(
                InlineKeyboardButton::make('💳 Abonnements', callback_data: 'help_subscription'),
                InlineKeyboardButton::make('👥 Clients', callback_data: 'help_clients')
            )
            ->addRow(
                InlineKeyboardButton::make('🏠 Menu Principal', callback_data: 'menu_back')
            );
    }

    /**
     * Aide contextuelle selon la situation de l'utilisateur
     */
    public static function contextualHelp(Nutgram $bot, string $context): void
    {
        $messages = [
            'ticket_creation' => "💡 <b>Aide : Création de Ticket</b>\n\n"
                . "Pour créer un ticket efficacement :\n\n"
                . "1. Choisissez la bonne catégorie\n"
                . "2. Soyez précis dans votre description\n"
                . "3. Ajoutez des captures d'écran si possible\n"
                . "4. Indiquez les détails importants (numéro de commande, date, etc.)\n\n"
                . "Utilisez /cancel pour annuler.",

            'waiting_response' => "💡 <b>En Attente de Réponse</b>\n\n"
                . "Votre ticket est en cours de traitement.\n\n"
                . "• Vous recevrez une notification dès qu'un agent répondra\n"
                . "• Temps de réponse moyen : 24-48h\n"
                . "• Support prioritaire pour abonnés Premium/Entreprise\n"
                . "• Pour une question urgente, contactez-nous : /contact",

            'no_tickets' => "💡 <b>Aucun Ticket</b>\n\n"
                . "Vous n'avez pas encore créé de ticket.\n\n"
                . "Créez-en un pour :\n"
                . "• Signaler un problème\n"
                . "• Poser une question\n"
                . "• Demander de l'aide\n\n"
                . "Utilisez /ticket pour commencer.",

            'payment_pending' => "💡 <b>Paiement en Cours</b>\n\n"
                . "Votre paiement est en cours de validation.\n\n"
                . "• Vous recevrez une notification dès la validation\n"
                . "• Délai : généralement sous 24h\n"
                . "• En cas de problème, contactez : /contact\n"
                . "• Montant en FCFA",

            'no_company' => "💡 <b>Aucune Entreprise</b>\n\n"
                . "Vous devez d'abord créer une entreprise.\n\n"
                . "Utilisez /createcompany pour commencer.\n"
                . "Vous pourrez ensuite :\n"
                . "• Ajouter des clients (3 max en Gratuit)\n"
                . "• Créer des devis (5/mois en Gratuit)\n"
                . "• Gérer votre activité\n\n"
                . "💡 Le plan gratuit est parfait pour débuter !",

            'client_limit_reached' => "⚠️ <b>Limite de Clients Atteinte</b>\n\n"
                . "Vous avez atteint la limite de 3 clients du plan Gratuit.\n\n"
                . "Pour ajouter plus de clients :\n"
                . "• Passez au plan Premium (illimité)\n"
                . "• Ou au plan Entreprise (illimité)\n\n"
                . "Utilisez /subscription pour upgrader.",

            'quote_limit_reached' => "⚠️ <b>Limite de Devis Atteinte</b>\n\n"
                . "Vous avez utilisé vos 5 devis du mois (plan Gratuit).\n\n"
                . "Solutions :\n"
                . "• Attendez le mois prochain\n"
                . "• Passez au plan Premium (devis illimités)\n\n"
                . "Utilisez /subscription pour upgrader.",
        ];

        $message = $messages[$context] ?? "Utilisez /help pour voir l'aide complète.";

        $bot->sendMessage(
            text: $message,
            parse_mode: 'HTML'
        );
    }
}

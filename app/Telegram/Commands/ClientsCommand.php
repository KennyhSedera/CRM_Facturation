<?php

namespace App\Telegram\Commands;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use App\Models\User;
use App\Models\Client;
use App\Models\Company;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

/**
 * Commande principale pour gérer les clients
 */
class ClientsCommand extends Command
{
    protected string $command = 'clients';
    protected ?string $description = 'Gérer mes clients';

    public function handle(Nutgram $bot): void
    {
        $useraccess = User::checkTelegramAccess($bot, requireCompany: true);
        if (!$useraccess)
            return;

        $user = User::where('telegram_id', $bot->user()->id)->with('company')->first();
        if (!$user || !$user->company_id) {
            $bot->sendMessage("❌ Vous devez d'abord créer votre entreprise. Utilisez /start");
            return;
        }

        $clientCount = Client::where('company_id', $user->company_id)->count();

        $message = "👥 <b>Gestion des Clients</b>\n\n"
            . "📊 Vous avez <b>{$clientCount} client(s)</b>\n\n"
            . "Que souhaitez-vous faire ?";

        $telegramUser = $bot->user();
        $webAppUrl = route('webapp.form.client', ['user_id' => $telegramUser->id]);

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('➕ Ajouter un client', web_app: new WebAppInfo($webAppUrl)),
                InlineKeyboardButton::make('📋 Voir mes clients', callback_data: 'client_list')
            )
            ->addRow(
                InlineKeyboardButton::make('🔍 Rechercher', callback_data: 'client_search')
            );

        $bot->sendMessage(
            text: $message,
            parse_mode: 'HTML',
            reply_markup: $keyboard
        );
    }
}

/**
 * Gestion des callbacks pour les clients
 */
class ClientCallbackHandler
{
    /**
     * Afficher la liste des clients
     */
    public static function listClients(Nutgram $bot): void
    {
        $user = User::checkTelegramAccess($bot, requireCompany: true);
        if (!$user)
            return;

        $user = User::where('telegram_id', $bot->user()->id)->first();

        if (!$user) {
            $bot->answerCallbackQuery();
            $bot->sendMessage("❌ Utilisateur non trouvé.");
            return;
        }

        $clients = Client::where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($clients->isEmpty()) {
            $bot->editMessageText(
                "📋 <b>Liste des clients</b>\n\n"
                . "Aucun client pour le moment.\n\n"
                . "➕ Ajoutez votre premier client !",
                parse_mode: 'HTML'
            );
            return;
        }

        $message = "📋 <b>Vos clients</b>\n\n";

        $keyboard = InlineKeyboardMarkup::make();

        foreach ($clients as $client) {
            $statusEmoji = $client->client_status === 'active' ? '✅' : '❌';
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    "{$statusEmoji} {$client->client_name}",
                    callback_data: "client_view_{$client->client_id}"
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make('🔙 Retour', callback_data: 'client_menu')
        );

        $bot->editMessageText(
            text: $message . "Sélectionnez un client pour voir les détails :",
            parse_mode: 'HTML',
            reply_markup: $keyboard
        );

        $bot->answerCallbackQuery();
    }

    /**
     * Voir les détails d'un client
     */
    public static function viewClient(Nutgram $bot, int $clientId): void
    {
        $user = User::checkTelegramAccess($bot, requireCompany: true);
        if (!$user)
            return;

        $client = Client::find($clientId);

        if (!$client) {
            $bot->answerCallbackQuery("❌ Client non trouvé", show_alert: true);
            return;
        }

        $message = $client->formatForDisplay();

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✏️ Modifier', callback_data: "client_modify_{$clientId}"),
                InlineKeyboardButton::make('📋 Créer devis', callback_data: "quote_create_{$clientId}")
            )
            ->addRow(
                InlineKeyboardButton::make('🗑️ Supprimer', callback_data: "client_delete_{$clientId}")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Retour', callback_data: 'client_list')
            );

        $bot->editMessageText(
            text: $message,
            parse_mode: 'HTML',
            reply_markup: $keyboard
        );

        $bot->answerCallbackQuery();
    }

    /**
     * Démarrer le processus d'ajout d'un client
     */
    public static function addClient(Nutgram $bot): void
    {
        $user = User::checkTelegramAccess($bot, requireCompany: true);
        if (!$user)
            return;

        $bot->answerCallbackQuery();

        $message = "➕ <b>Ajouter un nouveau client</b>\n\n"
            . "Envoyez-moi les informations du client dans ce format :\n\n"
            . "<code>Nom\n"
            . "Téléphone\n"
            . "Email (optionnel)\n"
            . "CIN (optionnel)\n"
            . "Adresse (optionnel)</code>\n\n"
            . "<b>Exemple :</b>\n"
            . "<code>Jean Dupond\n"
            . "+2613323456785\n"
            . "jean@email.com\n"
            . "20201132393\n"
            . "Lot II A 45 Kara</code>\n\n"
            . "💡 Vous pouvez aussi envoyer uniquement le nom et téléphone.";

        $bot->sendMessage($message, parse_mode: 'HTML');

        // Stocker l'état pour le prochain message
        $bot->setGlobalData('awaiting_client_data', true);
        $bot->setGlobalData('user_telegram_id', $bot->user()->id);
    }

    /**
     * Traiter les données du nouveau client
     */
    public static function processClientData(Nutgram $bot): void
    {
        $user = User::checkTelegramAccess($bot, requireCompany: true);
        if (!$user)
            return;

        $text = trim($bot->message()->text);
        $lines = array_map('trim', explode("\n", $text));

        if (count($lines) < 2) {
            $bot->sendMessage(
                "❌ Format incorrect. Vous devez fournir au minimum :\n"
                . "• Nom\n"
                . "• Téléphone\n\n"
                . "Réessayez.",
                parse_mode: 'HTML'
            );
            return;
        }

        $user = User::where('telegram_id', $bot->user()->id)->first();

        if (!$user || !$user->company_id) {
            $bot->sendMessage("❌ Erreur : entreprise non trouvée.");
            return;
        }

        // Vérifier les limites du plan
        $clientCount = Client::where('company_id', $user->company_id)->count();
        $maxClients = Client::getMaxClients($user->company->plan_status);

        if ($clientCount >= $maxClients) {
            $bot->sendMessage(
                "⚠️ <b>Limite atteinte</b>\n\n"
                . "Votre plan {$user->company->plan_status} permet {$maxClients} clients maximum.\n"
                . "Vous avez déjà {$clientCount} clients.\n\n"
                . "💎 Passez à un plan supérieur pour ajouter plus de clients.",
                parse_mode: 'HTML'
            );
            return;
        }

        // Créer le client
        $clientData = [
            'client_name' => $lines[0],
            'client_phone' => $lines[1] ?? null,
            'client_email' => $lines[2] ?? null,
            'client_cin' => $lines[3] ?? null,
            'client_adress' => $lines[4] ?? null,
            'client_country' => 'Togo',
            'client_note' => 'Client VIP - Paiement toujours à temps',
        ];

        $existing = Client::where([
            'client_name' => $clientData['client_name'],
            'company_id' => $user->company_id,
        ])->first();

        if ($existing) {
            $bot->sendMessage("⚠️ Ce client est déjà enregistré ! \n\n💡 Ajouter de nouveau ou tapez /cancel pour annuler");
            return;
        }

        try {
            $client = Client::createClient($clientData, $user->id, $user->company_id);
            Company::where(column: 'company_id', value: $user->company_id)
                ->update(['client_count' => ($clientCount + 1)]);

            $message = "✅ <b>Client créé avec succès !</b>\n\n"
                . $client->formatForDisplay();

            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('📋 Créer un devis', callback_data: "quote_create_{$client->client_id}"),
                    InlineKeyboardButton::make('👥 Voir tous les clients', callback_data: 'client_list')
                )
                ->addRow(
                    InlineKeyboardButton::make('🏢 Menu Principale', callback_data: 'menu_back')
                );

            $bot->sendMessage($message, parse_mode: 'HTML', reply_markup: $keyboard);

            // Réinitialiser l'état
            $bot->deleteGlobalData('awaiting_client_data');

        } catch (\Exception $e) {
            $bot->sendMessage(
                "❌ Erreur lors de la création du client : " . $e->getMessage()
            );
        }
    }

    /**
     * Supprimer un client
     */
    public static function deleteClient(Nutgram $bot, int $clientId): void
    {
        $user = User::checkTelegramAccess($bot, requireCompany: true);
        if (!$user)
            return;

        $client = Client::find($clientId);

        if (!$client) {
            $bot->answerCallbackQuery("❌ Client non trouvé", show_alert: true);
            return;
        }

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Oui, supprimer', callback_data: "client_delete_confirm_{$clientId}"),
                InlineKeyboardButton::make('❌ Annuler', callback_data: "client_view_{$clientId}")
            );

        $message = "⚠️ <b>Confirmation de suppression</b>\n\n"
            . "Êtes-vous sûr de vouloir supprimer ce client ?\n\n"
            . "👤 <b>{$client->client_name}</b>\n"
            . "📋 Réf: {$client->client_reference}\n\n"
            . "⚠️ Cette action est irréversible !";

        $bot->editMessageText($message, parse_mode: 'HTML', reply_markup: $keyboard);
        $bot->answerCallbackQuery();
    }

    /**
     * Confirmer la suppression
     */
    public static function confirmDelete(Nutgram $bot, int $clientId): void
    {
        $user = User::checkTelegramAccess($bot, requireCompany: true);
        if (!$user)
            return;

        $client = Client::with('company')->find($clientId);

        if (!$client) {
            $bot->answerCallbackQuery("❌ Client non trouvé", show_alert: true);
            return;
        }

        $clientName = $client->client_name;

        // ✅ Décrémenter le compteur de clients avant suppression
        if ($client->company && $client->company->client_count > 0) {
            $client->company->decrement('client_count');
        }

        $client->delete();

        $bot->editMessageText(
            "✅ <b>Client supprimé</b>\n\n"
            . "Le client <b>{$clientName}</b> a été supprimé avec succès.",
            parse_mode: 'HTML'
        );

        $bot->answerCallbackQuery("✅ Client supprimé");
    }

    /**
     * Retour au menu principal des clients
     */
    public static function showMenu(Nutgram $bot): void
    {
        $user = User::checkTelegramAccess($bot, requireCompany: true);
        if (!$user)
            return;

        $user = User::where('telegram_id', $bot->user()->id)->with('company')->first();
        $clientCount = Client::where('company_id', $user->company_id)->count();

        $message = "👥 <b>Gestion des Clients</b>\n\n"
            . "📊 Vous avez <b>{$clientCount} client(s)</b>\n\n"
            . "Que souhaitez-vous faire ?";


        $telegramUser = $bot->user();
        $webAppUrl = route('webapp.form.client', ['user_id' => $telegramUser->id]);

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('➕ Ajouter un client', web_app: new WebAppInfo($webAppUrl)),
                InlineKeyboardButton::make('📋 Voir mes clients', callback_data: 'client_list')
            )
            ->addRow(
                InlineKeyboardButton::make('🔍 Rechercher', callback_data: 'client_search')
            );

        $bot->editMessageText(
            text: $message,
            parse_mode: 'HTML',
            reply_markup: $keyboard
        );

        $bot->answerCallbackQuery();
    }
    /**
     * Rechercher un client
     */
    public static function searchClient(Nutgram $bot): void
    {
        $user = User::checkTelegramAccess($bot, requireCompany: true);
        if (!$user)
            return;

        $bot->answerCallbackQuery();

        $message = "🔍 <b>Rechercher un client</b>\n\n"
            . "Envoyez-moi le nom, téléphone ou référence du client à rechercher.\n\n"
            . "💡 <i>Tapez /cancel pour annuler</i>";

        $bot->sendMessage($message, parse_mode: 'HTML');

        // Stocker l'état pour le prochain message
        $bot->setGlobalData('awaiting_search_query', true);
        $bot->setGlobalData('user_telegram_id', $bot->user()->id);
    }

    /**
     * Traiter la recherche de client
     */
    public static function processSearchQuery(Nutgram $bot): void
    {
        $user = User::checkTelegramAccess($bot, requireCompany: true);
        if (!$user)
            return;

        $query = trim($bot->message()->text);

        if (empty($query)) {
            $bot->sendMessage("❌ Veuillez entrer un terme de recherche valide.");
            return;
        }

        $user = User::where('telegram_id', $bot->user()->id)->first();

        if (!$user || !$user->company_id) {
            $bot->sendMessage("❌ Erreur : entreprise non trouvée.");
            return;
        }

        // ✅ Recherche insensible à la casse
        $clients = Client::where('company_id', $user->company_id)
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(client_name) LIKE ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(client_phone) LIKE ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(client_reference) LIKE ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(client_email) LIKE ?', ['%' . strtolower($query) . '%']);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($clients->isEmpty()) {
            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('🔙 Retour au menu', callback_data: 'client_menu')
                );

            $bot->sendMessage(
                "❌ <b>Aucun résultat</b>\n\n"
                . "Aucun client trouvé pour : <code>{$query}</code>\n\n"
                . "💡 Vérifiez l'orthographe ou essayez avec un autre terme.",
                parse_mode: 'HTML',
                reply_markup: $keyboard
            );

            $bot->deleteGlobalData('awaiting_search_query');
            return;
        }

        $message = "🔍 <b>Résultats de recherche</b>\n\n"
            . "Recherche : <code>{$query}</code>\n"
            . "📊 {$clients->count()} résultat(s) trouvé(s)\n\n";

        $keyboard = InlineKeyboardMarkup::make();

        foreach ($clients as $client) {
            $statusEmoji = $client->client_status === 'active' ? '✅' : '❌';
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    "{$statusEmoji} {$client->client_name} - {$client->client_phone}",
                    callback_data: "client_view_{$client->client_id}"
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make('🔍 Nouvelle recherche', callback_data: 'client_search'),
            InlineKeyboardButton::make('🔙 Menu', callback_data: 'client_menu')
        );

        $bot->sendMessage(
            text: $message . "Sélectionnez un client :",
            parse_mode: 'HTML',
            reply_markup: $keyboard
        );

        // Réinitialiser l'état
        $bot->deleteGlobalData('awaiting_search_query');
    }

    /**
     * Modifier un client
     */
    public static function editClient(Nutgram $bot, int $clientId): void
    {
        $user = User::checkTelegramAccess($bot, requireCompany: true);
        if (!$user)
            return;

        $client = Client::find($clientId);

        if (!$client) {
            $bot->answerCallbackQuery("❌ Client non trouvé", show_alert: true);
            return;
        }

        $bot->answerCallbackQuery();

        $message = "✏️ <b>Modifier le client</b>\n\n"
            . "Client actuel :\n"
            . "👤 <b>{$client->client_name}</b>\n"
            . "📞 {$client->client_phone}\n"
            . "📧 " . ($client->client_email ?? 'Non renseigné') . "\n"
            . "🆔 " . ($client->client_cin ?? 'Non renseigné') . "\n"
            . "📍 " . ($client->client_adress ?? 'Non renseigné') . "\n\n"
            . "Que souhaitez-vous modifier ?";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('👤 Nom', callback_data: "client_edit_field_{$clientId}_name"),
                InlineKeyboardButton::make('📞 Téléphone', callback_data: "client_edit_field_{$clientId}_phone")
            )
            ->addRow(
                InlineKeyboardButton::make('📧 Email', callback_data: "client_edit_field_{$clientId}_email"),
                InlineKeyboardButton::make('🆔 CIN', callback_data: "client_edit_field_{$clientId}_cin")
            )
            ->addRow(
                InlineKeyboardButton::make('📍 Adresse', callback_data: "client_edit_field_{$clientId}_address"),
                InlineKeyboardButton::make('🔄 Statut', callback_data: "client_toggle_status_{$clientId}")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Retour', callback_data: "client_view_{$clientId}")
            );

        $bot->editMessageText($message, parse_mode: 'HTML', reply_markup: $keyboard);
    }
    /**
     * Modifier un champ spécifique du client
     */
    public static function editClientField(Nutgram $bot, int $clientId, string $field): void
    {
        $user = User::checkTelegramAccess($bot, requireCompany: true);
        if (!$user)
            return;

        $client = Client::find($clientId);

        if (!$client) {
            $bot->answerCallbackQuery("❌ Client non trouvé", show_alert: true);
            return;
        }

        $bot->answerCallbackQuery();

        $fieldLabels = [
            'name' => '👤 Nom',
            'phone' => '📞 Téléphone',
            'email' => '📧 Email',
            'cin' => '🆔 CIN',
            'address' => '📍 Adresse',
        ];

        $fieldLabel = $fieldLabels[$field] ?? $field;

        $message = "✏️ <b>Modifier {$fieldLabel}</b>\n\n"
            . "Client : <b>{$client->client_name}</b>\n\n"
            . "Envoyez-moi la nouvelle valeur pour ce champ.\n\n"
            . "💡 <i>Tapez /cancel pour annuler</i>";

        $bot->sendMessage($message, parse_mode: 'HTML');

        // Stocker l'état pour le prochain message
        $bot->setGlobalData('editing_client_id', $clientId);
        $bot->setGlobalData('editing_field', $field);
        $bot->setGlobalData('user_telegram_id', $bot->user()->id);
    }

    /**
     * Traiter la modification d'un champ
     */
    public static function processFieldEdit(Nutgram $bot): void
    {
        $user = User::checkTelegramAccess($bot, requireCompany: true);
        if (!$user)
            return;

        $clientId = $bot->getGlobalData('editing_client_id');
        $field = $bot->getGlobalData('editing_field');
        $newValue = trim($bot->message()->text);

        if (empty($newValue)) {
            $bot->sendMessage("❌ La valeur ne peut pas être vide.");
            return;
        }

        $client = Client::find($clientId);

        if (!$client) {
            $bot->sendMessage("❌ Client non trouvé.");
            $bot->deleteGlobalData('editing_client_id');
            $bot->deleteGlobalData('editing_field');
            return;
        }

        // Mapper les noms de champs aux colonnes de la base de données
        $fieldMapping = [
            'name' => 'client_name',
            'phone' => 'client_phone',
            'email' => 'client_email',
            'cin' => 'client_cin',
            'address' => 'client_adress',
        ];

        $dbField = $fieldMapping[$field] ?? null;

        if (!$dbField) {
            $bot->sendMessage("❌ Champ invalide.");
            return;
        }

        try {
            $oldValue = $client->$dbField;
            $client->$dbField = $newValue;
            $client->save();

            $fieldLabels = [
                'name' => '👤 Nom',
                'phone' => '📞 Téléphone',
                'email' => '📧 Email',
                'cin' => '🆔 CIN',
                'address' => '📍 Adresse',
            ];

            $message = "✅ <b>Modification réussie</b>\n\n"
                . "Client : <b>{$client->client_name}</b>\n\n"
                . "{$fieldLabels[$field]} :\n"
                . "Ancien : <code>" . ($oldValue ?? 'Non renseigné') . "</code>\n"
                . "Nouveau : <code>{$newValue}</code>";

            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('👁️ Voir le client', callback_data: "client_view_{$clientId}"),
                    InlineKeyboardButton::make('✏️ Modifier autre chose', callback_data: "client_modify_{$clientId}")
                )
                ->addRow(
                    InlineKeyboardButton::make('🔙 Retour au menu', callback_data: 'client_menu')
                );

            $bot->sendMessage($message, parse_mode: 'HTML', reply_markup: $keyboard);

            // Réinitialiser l'état
            $bot->deleteGlobalData('editing_client_id');
            $bot->deleteGlobalData('editing_field');

        } catch (\Exception $e) {
            $bot->sendMessage("❌ Erreur lors de la modification : " . $e->getMessage());
        }
    }

    /**
     * Basculer le statut du client (actif/inactif)
     */
    public static function toggleClientStatus(Nutgram $bot, int $clientId): void
    {
        $user = User::checkTelegramAccess($bot, requireCompany: true);
        if (!$user)
            return;

        $client = Client::find($clientId);

        if (!$client) {
            $bot->answerCallbackQuery("❌ Client non trouvé", show_alert: true);
            return;
        }

        $newStatus = $client->client_status === 'active' ? 'inactive' : 'active';
        $client->client_status = $newStatus;
        $client->save();

        $statusEmoji = $newStatus === 'active' ? '✅' : '❌';
        $statusText = $newStatus === 'active' ? 'Actif' : 'Inactif';

        $bot->answerCallbackQuery(text: "✅ Statut changé : {$statusText}");

        $message = "🔄 <b>Statut modifié</b>\n\n"
            . "Client : <b>{$client->client_name}</b>\n"
            . "Nouveau statut : {$statusEmoji} <b>{$statusText}</b>";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('👁️ Voir le client', callback_data: "client_view_{$clientId}"),
                InlineKeyboardButton::make('✏️ Modifier', callback_data: "client_modify_{$clientId}")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Menu clients', callback_data: 'client_menu')
            );

        $bot->editMessageText($message, parse_mode: 'HTML', reply_markup: $keyboard);
    }
}

/**
 * Handler pour les messages en attente de données client
 */
class ClientMessageHandler
{
    public function handle(Nutgram $bot): void
    {
        if ($bot->getGlobalData('awaiting_client_data')) {
            ClientCallbackHandler::processClientData($bot);
        }
    }
}

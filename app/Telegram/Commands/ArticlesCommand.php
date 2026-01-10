<?php

namespace App\Telegram\Commands;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use App\Models\User;
use App\Models\Article;
use App\Models\Company;
use App\Models\MvtArticle;

/**
 * Commande principale pour gérer les articles
 */
class ArticlesCommand extends Command
{
    protected string $command = 'articles';
    protected ?string $description = 'Gérer mes articles';

    public function handle(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->with('company')->first();

        if (!$user || !$user->company_id) {
            $bot->sendMessage("❌ Vous devez d'abord créer votre entreprise. Utilisez /start");
            return;
        }

        $articleCount = Article::where('user_id', $user->id)->count();
        $totalStock = Article::where('user_id', $user->id)->sum('quantity_stock');

        $message = "📦 <b>Gestion des Articles</b>\n\n"
            . "📊 Vous avez <b>{$articleCount} article(s)</b>\n"
            . "📦 Stock total : <b>{$totalStock} unités</b>\n\n"
            . "Que souhaitez-vous faire ?";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('➕ Ajouter un article', callback_data: 'article_add'),
                InlineKeyboardButton::make('📋 Voir mes articles', callback_data: 'article_list')
            )
            ->addRow(
                InlineKeyboardButton::make('🔍 Rechercher', callback_data: 'article_search')
            )
            ->addRow(
                InlineKeyboardButton::make('🏢 Menu Principal', callback_data: 'menu_back')
            );

        $bot->sendMessage(
            text: $message,
            parse_mode: 'HTML',
            reply_markup: $keyboard
        );
    }
}

/**
 * Gestion des callbacks pour les articles
 */
class ArticleCallbackHandler
{
    /**
     * Afficher la liste des articles
     */
    public static function listArticles(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->first();

        if (!$user) {
            $bot->answerCallbackQuery();
            $bot->sendMessage("❌ Utilisateur non trouvé.");
            return;
        }

        $articles = Article::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($articles->isEmpty()) {
            $bot->editMessageText(
                "📋 <b>Liste des articles</b>\n\n"
                . "Aucun article pour le moment.\n\n"
                . "➕ Ajoutez votre premier article !",
                parse_mode: 'HTML',
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make('🔙 Retour', callback_data: 'article_menu'))
            );
            $bot->answerCallbackQuery();
            return;
        }

        $message = "📋 <b>Vos articles</b>\n\n";

        $keyboard = InlineKeyboardMarkup::make();

        foreach ($articles as $article) {
            $stockEmoji = $article->quantity_stock > 0 ? '✅' : '⚠️';
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    "{$stockEmoji} {$article->article_name} (Stock: {$article->quantity_stock})",
                    callback_data: "article_view_{$article->article_id}"
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make('🔙 Retour', callback_data: 'article_menu')
        );

        $bot->editMessageText(
            text: $message . "Sélectionnez un article pour voir les détails :",
            parse_mode: 'HTML',
            reply_markup: $keyboard
        );

        $bot->answerCallbackQuery();
    }

    /**
     * Voir les détails d'un article
     */
    public static function viewArticle(Nutgram $bot, int $articleId): void
    {
        $article = Article::find($articleId);

        if (!$article) {
            $bot->answerCallbackQuery("❌ Article non trouvé", show_alert: true);
            return;
        }

        $stockStatus = $article->quantity_stock > 0 ? '✅ En stock' : '⚠️ Rupture de stock';
        $tva = $article->article_tva ?? 0;
        $priceWithTVA = $article->selling_price * (1 + $tva / 100);
        $stockValue = $article->quantity_stock * $article->selling_price;

        $message = "📦 <b>Détails de l'article</b>\n\n"
            . "📝 <b>Nom :</b> {$article->article_name}\n"
            . "🔖 <b>Référence :</b> {$article->article_reference}\n"
            . "📊 <b>Source :</b> {$article->article_source}\n"
            . "📏 <b>Unité :</b> {$article->article_unité}\n\n"
            . "💰 <b>Prix HT :</b> " . number_format($article->selling_price, 0, ',', ' ') . " FCFA\n"
            . "💵 <b>TVA :</b> {$tva}%\n"
            . "💸 <b>Prix TTC :</b> " . number_format($priceWithTVA, 0, ',', ' ') . " FCFA\n\n"
            . "📦 <b>Stock :</b> {$article->quantity_stock} {$article->article_unité}\n"
            . "💎 <b>Valeur stock :</b> " . number_format($stockValue, 0, ',', ' ') . " FCFA\n"
            . "🔔 <b>Statut :</b> {$stockStatus}";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✏️ Modifier', callback_data: "article_edit_{$articleId}"),
                InlineKeyboardButton::make('📦 Ajuster stock', callback_data: "article_stock_{$articleId}")
            )
            ->addRow(
                InlineKeyboardButton::make('📊 Historique', callback_data: "article_history_{$articleId}"),
                InlineKeyboardButton::make('🗑️ Supprimer', callback_data: "article_delete_{$articleId}")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Retour', callback_data: 'article_list')
            );

        $bot->editMessageText(
            text: $message,
            parse_mode: 'HTML',
            reply_markup: $keyboard
        );

        $bot->answerCallbackQuery();
    }

    /**
     * Démarrer le processus d'ajout d'un article
     */
    public static function addArticle(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $message = "➕ <b>Ajouter un nouveau article</b>\n\n"
            . "Envoyez-moi les informations de l'article dans ce format :\n\n"
            . "<code>Nom de l'article\n"
            . "Prix de vente (FCFA)\n"
            . "Quantité en stock\n"
            . "Unité (ex: pièce, kg, litre)\n"
            . "Source (ex: Fournisseur A)\n"
            . "TVA en % (optionnel, défaut: 0)</code>\n\n"
            . "<b>Exemple :</b>\n"
            . "<code>Ordinateur Dell XPS 15\n"
            . "850000\n"
            . "5\n"
            . "pièce\n"
            . "Dell Store\n"
            . "18</code>\n\n"
            . "💡 Le numéro de référence sera généré automatiquement.";

        $bot->sendMessage($message, parse_mode: 'HTML');

        // Stocker l'état pour le prochain message
        $bot->setGlobalData('awaiting_article_data', true);
        $bot->setGlobalData('user_telegram_id', $bot->user()->id);
    }

    /**
     * Traiter les données du nouvel article
     */
    public static function processArticleData(Nutgram $bot): void
    {
        $text = trim($bot->message()->text);
        $lines = array_map('trim', explode("\n", $text));

        if (count($lines) < 4) {
            $bot->sendMessage(
                "❌ Format incorrect. Vous devez fournir au minimum :\n"
                . "• Nom de l'article\n"
                . "• Prix de vente\n"
                . "• Quantité en stock\n"
                . "• Unité\n\n"
                . "Réessayez.",
                parse_mode: 'HTML'
            );
            return;
        }

        $user = User::where('telegram_id', $bot->user()->id)->first();

        if (!$user) {
            $bot->sendMessage("❌ Erreur : utilisateur non trouvé.");
            return;
        }

        // Validation du prix
        if (!is_numeric($lines[1]) || $lines[1] <= 0) {
            $bot->sendMessage("❌ Le prix de vente doit être un nombre positif.");
            return;
        }

        // Validation de la quantité
        if (!is_numeric($lines[2]) || $lines[2] < 0) {
            $bot->sendMessage("❌ La quantité en stock doit être un nombre positif ou zéro.");
            return;
        }

        // Validation de la TVA si fournie
        $tva = isset($lines[5]) && is_numeric($lines[5]) ? (float) $lines[5] : 0;
        if ($tva < 0 || $tva > 100) {
            $bot->sendMessage("❌ La TVA doit être entre 0 et 100%.");
            return;
        }

        // Générer la référence automatiquement
        $reference = 'ART-' . strtoupper(substr(md5(uniqid()), 0, 8));

        // Créer l'article
        $articleData = [
            'article_name' => $lines[0],
            'selling_price' => (float) $lines[1],
            'quantity_stock' => (int) $lines[2],
            'article_unité' => $lines[3],
            'article_source' => $lines[4] ?? 'N/A',
            'article_tva' => $tva,
            'article_reference' => $reference,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
        ];

        $existing = Article::where([
            'article_name' => $lines[0],
            'company_id' => $user->company_id,
        ])->first();

        if ($existing) {
            $bot->sendMessage('⚠️ Cette article déjà enregistrée !');
        } else {
            try {
                $article = Article::create($articleData);

                if ($article->quantity_stock > 0) {
                    MvtArticle::create([
                        'mvtType' => 'entree',
                        'mvt_quantity' => $article->quantity_stock,
                        'mvt_date' => now(),
                        'article_id' => $article->article_id,
                        'user_id' => $user->id,
                    ]);
                }

                $priceWithTVA = $article->selling_price * (1 + $article->article_tva / 100);

                $message = "✅ <b>Article créé avec succès !</b>\n\n"
                    . "📦 <b>{$article->article_name}</b>\n"
                    . "🔖 Réf: {$article->article_reference}\n\n"
                    . "💰 Prix HT : " . number_format($article->selling_price, 0, ',', ' ') . " FCFA\n"
                    . "💵 TVA : {$article->article_tva}%\n"
                    . "💸 Prix TTC : " . number_format($priceWithTVA, 0, ',', ' ') . " FCFA\n\n"
                    . "📦 Stock : {$article->quantity_stock} {$article->article_unité}";

                $keyboard = InlineKeyboardMarkup::make()
                    ->addRow(
                        InlineKeyboardButton::make('📦 Voir l\'article', callback_data: "article_view_{$article->article_id}"),
                        InlineKeyboardButton::make('📋 Tous les articles', callback_data: 'article_list')
                    )
                    ->addRow(
                        InlineKeyboardButton::make('🏢 Menu Principal', callback_data: 'menu_back')
                    );

                $bot->sendMessage($message, parse_mode: 'HTML', reply_markup: $keyboard);

                // Réinitialiser l'état
                $bot->deleteGlobalData('awaiting_article_data');

            } catch (\Exception $e) {
                $bot->sendMessage(
                    "❌ Erreur lors de la création de l'article : " . $e->getMessage()
                );
            }
        }
    }

    /**
     * Démarrer le processus de modification d'un article
     */
    public static function editArticle(Nutgram $bot, int $articleId): void
    {
        $article = Article::find($articleId);

        if (!$article) {
            $bot->answerCallbackQuery("❌ Article non trouvé", show_alert: true);
            return;
        }

        $bot->answerCallbackQuery();

        $message = "✏️ <b>Modifier l'article</b>\n\n"
            . "📦 <b>Article actuel :</b> {$article->article_name}\n\n"
            . "Envoyez les nouvelles informations dans ce format :\n\n"
            . "<code>Nom de l'article\n"
            . "Prix de vente (FCFA)\n"
            . "Quantité en stock\n"
            . "Unité\n"
            . "Source\n"
            . "TVA en %</code>\n\n"
            . "<b>Valeurs actuelles :</b>\n"
            . "<code>{$article->article_name}\n"
            . "{$article->selling_price}\n"
            . "{$article->quantity_stock}\n"
            . "{$article->article_unité}\n"
            . "{$article->article_source}\n"
            . "{$article->article_tva}</code>";

        $bot->sendMessage($message, parse_mode: 'HTML');

        // Stocker l'état pour le prochain message
        $bot->setGlobalData('awaiting_article_edit', $articleId);
        $bot->setGlobalData('user_telegram_id', $bot->user()->id);
    }

    /**
     * Traiter la modification d'un article
     */
    public static function processArticleEdit(Nutgram $bot, int $articleId): void
    {
        $text = trim($bot->message()->text);
        $lines = array_map('trim', explode("\n", $text));

        if (count($lines) < 6) {
            $bot->sendMessage("❌ Format incorrect. Veuillez fournir toutes les informations.");
            return;
        }

        $article = Article::find($articleId);

        if (!$article) {
            $bot->sendMessage("❌ Article non trouvé.");
            return;
        }

        // Validations
        if (!is_numeric($lines[1]) || $lines[1] <= 0) {
            $bot->sendMessage("❌ Le prix de vente doit être un nombre positif.");
            return;
        }

        if (!is_numeric($lines[2]) || $lines[2] < 0) {
            $bot->sendMessage("❌ La quantité en stock doit être un nombre positif ou zéro.");
            return;
        }

        $tva = is_numeric($lines[5]) ? (float) $lines[5] : 0;
        if ($tva < 0 || $tva > 100) {
            $bot->sendMessage("❌ La TVA doit être entre 0 et 100%.");
            return;
        }

        $user = User::where('telegram_id', $bot->user()->id)->first();

        try {
            $article->update([
                'article_name' => $lines[0],
                'selling_price' => (float) $lines[1],
                'quantity_stock' => (int) $lines[2],
                'article_unité' => $lines[3],
                'article_source' => $lines[4],
                'article_tva' => $tva,
                'company_id' => $user->company_id,
            ]);

            $priceWithTVA = $article->selling_price * (1 + $article->article_tva / 100);

            $message = "✅ <b>Article modifié avec succès !</b>\n\n"
                . "📦 <b>{$article->article_name}</b>\n"
                . "💰 Prix HT : " . number_format($article->selling_price, 0, ',', ' ') . " FCFA\n"
                . "💸 Prix TTC : " . number_format($priceWithTVA, 0, ',', ' ') . " FCFA\n"
                . "📦 Stock : {$article->quantity_stock} {$article->article_unité}";

            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('📦 Voir l\'article', callback_data: "article_view_{$article->article_id}")
                )
                ->addRow(
                    InlineKeyboardButton::make('🔙 Liste des articles', callback_data: 'article_list')
                );

            $bot->sendMessage($message, parse_mode: 'HTML', reply_markup: $keyboard);

            $bot->deleteGlobalData('awaiting_article_edit');

        } catch (\Exception $e) {
            $bot->sendMessage("❌ Erreur lors de la modification : " . $e->getMessage());
        }
    }

    /**
     * Ajuster le stock d'un article - Menu de choix
     */
    public static function adjustStock(Nutgram $bot, int $articleId): void
    {
        $article = Article::find($articleId);

        if (!$article) {
            $bot->answerCallbackQuery("❌ Article non trouvé", show_alert: true);
            return;
        }

        $stockValue = $article->quantity_stock * $article->selling_price;

        $message = "📦 <b>Ajuster le stock</b>\n\n"
            . "📦 <b>Article :</b> {$article->article_name}\n"
            . "📊 <b>Stock actuel :</b> {$article->quantity_stock} {$article->article_unité}\n"
            . "💎 <b>Valeur stock :</b> " . number_format($stockValue, 0, ',', ' ') . " FCFA\n\n"
            . "Choisissez le type d'ajustement :";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('➕ Ajouter', callback_data: "stock_add_{$articleId}"),
                InlineKeyboardButton::make('➖ Retirer', callback_data: "stock_remove_{$articleId}")
            )
            ->addRow(
                InlineKeyboardButton::make('🔄 Remplacer', callback_data: "stock_replace_{$articleId}")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Retour', callback_data: "article_view_{$articleId}")
            );

        $bot->editMessageText($message, parse_mode: 'HTML', reply_markup: $keyboard);
        $bot->answerCallbackQuery();
    }

    /**
     * Ajouter au stock
     */
    public static function stockAdd(Nutgram $bot, int $articleId): void
    {
        $article = Article::find($articleId);

        if (!$article) {
            $bot->answerCallbackQuery("❌ Article non trouvé", show_alert: true);
            return;
        }

        $bot->answerCallbackQuery();

        $message = "➕ <b>Ajouter au stock</b>\n\n"
            . "📦 <b>Article :</b> {$article->article_name}\n"
            . "📊 <b>Stock actuel :</b> {$article->quantity_stock} {$article->article_unité}\n\n"
            . "Envoyez la quantité à ajouter :";

        $bot->sendMessage($message, parse_mode: 'HTML');

        $bot->setGlobalData('awaiting_stock_add', $articleId);
        $bot->setGlobalData('user_telegram_id', $bot->user()->id);
    }

    /**
     * Retirer du stock
     */
    public static function stockRemove(Nutgram $bot, int $articleId): void
    {
        $article = Article::find($articleId);

        if (!$article) {
            $bot->answerCallbackQuery("❌ Article non trouvé", show_alert: true);
            return;
        }

        $bot->answerCallbackQuery();

        $message = "➖ <b>Retirer du stock</b>\n\n"
            . "📦 <b>Article :</b> {$article->article_name}\n"
            . "📊 <b>Stock actuel :</b> {$article->quantity_stock} {$article->article_unité}\n\n"
            . "Envoyez la quantité à retirer :";

        $bot->sendMessage($message, parse_mode: 'HTML');

        $bot->setGlobalData('awaiting_stock_remove', $articleId);
        $bot->setGlobalData('user_telegram_id', $bot->user()->id);
    }

    /**
     * Remplacer le stock
     */
    public static function stockReplace(Nutgram $bot, int $articleId): void
    {
        $article = Article::find($articleId);

        if (!$article) {
            $bot->answerCallbackQuery("❌ Article non trouvé", show_alert: true);
            return;
        }

        $bot->answerCallbackQuery();

        $message = "🔄 <b>Remplacer le stock</b>\n\n"
            . "📦 <b>Article :</b> {$article->article_name}\n"
            . "📊 <b>Stock actuel :</b> {$article->quantity_stock} {$article->article_unité}\n\n"
            . "Envoyez la nouvelle quantité totale :";

        $bot->sendMessage($message, parse_mode: 'HTML');

        $bot->setGlobalData('awaiting_stock_replace', $articleId);
        $bot->setGlobalData('user_telegram_id', $bot->user()->id);
    }

    /**
     * Traiter l'ajout de stock
     */
    public static function processStockAdd(Nutgram $bot, int $articleId): void
    {
        $text = trim($bot->message()->text);

        if (!is_numeric($text) || $text <= 0) {
            $bot->sendMessage("❌ La quantité à ajouter doit être un nombre positif.");
            return;
        }

        $article = Article::find($articleId);
        $user = User::where('telegram_id', $bot->user()->id)->first();

        if (!$article) {
            $bot->sendMessage("❌ Article non trouvé.");
            return;
        }

        $oldStock = $article->quantity_stock;
        $addQuantity = (int) $text;
        $newStock = $oldStock + $addQuantity;

        $article->update(['quantity_stock' => $newStock]);

        MvtArticle::create([
            'mvtType' => 'entree',
            'mvt_quantity' => $addQuantity,
            'mvt_date' => now(),
            'article_id' => $article->article_id,
            'user_id' => $user->id,
        ]);

        $stockValue = $newStock * $article->selling_price;

        $message = "✅ <b>Stock ajouté avec succès !</b>\n\n"
            . "📦 <b>{$article->article_name}</b>\n"
            . "📊 Stock : {$oldStock} + {$addQuantity} = {$newStock} {$article->article_unité}\n"
            . "💎 Valeur stock : " . number_format($stockValue, 0, ',', ' ') . " FCFA";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('📦 Voir l\'article', callback_data: "article_view_{$article->article_id}")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Liste des articles', callback_data: 'article_list')
            );

        $bot->sendMessage($message, parse_mode: 'HTML', reply_markup: $keyboard);

        $bot->deleteGlobalData('awaiting_stock_add');
    }

    /**
     * Traiter le retrait de stock
     */
    public static function processStockRemove(Nutgram $bot, int $articleId): void
    {
        $text = trim($bot->message()->text);

        if (!is_numeric($text) || $text <= 0) {
            $bot->sendMessage("❌ La quantité à retirer doit être un nombre positif.");
            return;
        }

        $article = Article::find($articleId);
        $user = User::where('telegram_id', $bot->user()->id)->first();

        if (!$article) {
            $bot->sendMessage("❌ Article non trouvé.");
            return;
        }

        $oldStock = $article->quantity_stock;
        $removeQuantity = (int) $text;

        if ($removeQuantity > $oldStock) {
            $bot->sendMessage("❌ Impossible de retirer {$removeQuantity} {$article->article_unité}. Stock actuel: {$oldStock}");
            return;
        }

        $newStock = $oldStock - $removeQuantity;

        $article->update(['quantity_stock' => $newStock]);

        // Enregistrer le mouvement
        MvtArticle::create([
            'mvtType' => 'sortie',
            'mvt_quantity' => $removeQuantity,
            'mvt_date' => now(),
            'article_id' => $article->article_id,
            'user_id' => $user->id,
        ]);

        $stockValue = $newStock * $article->selling_price;

        $message = "✅ <b>Stock retiré avec succès !</b>\n\n"
            . "📦 <b>{$article->article_name}</b>\n"
            . "📊 Stock : {$oldStock} - {$removeQuantity} = {$newStock} {$article->article_unité}\n"
            . "💎 Valeur stock : " . number_format($stockValue, 0, ',', ' ') . " FCFA";

        // Alertes
        if ($newStock == 0) {
            $message .= "\n\n🚨 <b>Alerte : Rupture de stock !</b>";
        } elseif ($newStock < 5) {
            $message .= "\n\n⚠️ <b>Attention : Stock faible ({$newStock} restants)</b>";
        }

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('📦 Voir l\'article', callback_data: "article_view_{$article->article_id}")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Liste des articles', callback_data: 'article_list')
            );

        $bot->sendMessage($message, parse_mode: 'HTML', reply_markup: $keyboard);

        $bot->deleteGlobalData('awaiting_stock_remove');
    }

    /**
     * Traiter le remplacement de stock
     */
    public static function processStockReplace(Nutgram $bot, int $articleId): void
    {
        $text = trim($bot->message()->text);

        if (!is_numeric($text) || $text < 0) {
            $bot->sendMessage("❌ La quantité doit être un nombre positif ou zéro.");
            return;
        }

        $article = Article::find($articleId);
        $user = User::where('telegram_id', $bot->user()->id)->first();

        if (!$article) {
            $bot->sendMessage("❌ Article non trouvé.");
            return;
        }

        $oldStock = $article->quantity_stock;
        $newStock = (int) $text;

        $article->update(['quantity_stock' => $newStock]);

        // Enregistrer le mouvement
        MvtArticle::create([
            'mvtType' => 'inventaire',
            'mvt_quantity' => $newStock,
            'mvt_date' => now(),
            'article_id' => $article->article_id,
            'user_id' => $user->id,
        ]);

        $diff = $newStock - $oldStock;
        $diffText = $diff > 0 ? "+{$diff}" : "{$diff}";
        $stockValue = $newStock * $article->selling_price;

        $message = "✅ <b>Stock remplacé avec succès !</b>\n\n"
            . "📦 <b>{$article->article_name}</b>\n"
            . "📊 Stock : {$oldStock} → {$newStock} {$article->article_unité}\n"
            . "📈 Différence : {$diffText} {$article->article_unité}\n"
            . "💎 Valeur stock : " . number_format($stockValue, 0, ',', ' ') . " FCFA";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('📦 Voir l\'article', callback_data: "article_view_{$article->article_id}")
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Liste des articles', callback_data: 'article_list')
            );

        $bot->sendMessage($message, parse_mode: 'HTML', reply_markup: $keyboard);

        $bot->deleteGlobalData('awaiting_stock_replace');
    }

    /**
     * Afficher l'historique des mouvements
     */
    public static function showHistory(Nutgram $bot, int $articleId): void
    {
        $article = Article::find($articleId);

        if (!$article) {
            $bot->answerCallbackQuery("❌ Article non trouvé", show_alert: true);
            return;
        }

        $movements = MvtArticle::where('article_id', $articleId)
            ->orderBy('mvt_date', 'desc')
            ->limit(10)
            ->get();

        if ($movements->isEmpty()) {
            $message = "📊 <b>Historique des mouvements</b>\n\n"
                . "📦 <b>{$article->article_name}</b>\n\n"
                . "Aucun mouvement enregistré.";
        } else {
            $message = "📊 <b>Historique des mouvements</b>\n\n"
                . "📦 <b>{$article->article_name}</b>\n"
                . "📋 Stock actuel : {$article->quantity_stock} {$article->article_unité}\n\n";

            foreach ($movements as $mvt) {
                $emoji = match ($mvt->mvtType) {
                    'entree' => '➕',
                    'sortie' => '➖',
                    'retour' => '🔄',
                    'création' => '✨',
                    default => '📝'
                };

                $date = \Carbon\Carbon::parse($mvt->mvt_date)->format('d/m/Y H:i');
                $message .= "{$emoji} <b>{$mvt->mvtType}</b> : {$mvt->mvt_quantity} {$article->article_unité}\n";
                $message .= "   📅 {$date}\n\n";
            }
        }

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('🔙 Retour', callback_data: "article_view_{$articleId}")
            );

        $bot->editMessageText(
            text: $message,
            parse_mode: 'HTML',
            reply_markup: $keyboard
        );

        $bot->answerCallbackQuery();
    }

    /**
     * Supprimer un article
     */
    public static function deleteArticle(Nutgram $bot, int $articleId): void
    {
        $article = Article::find($articleId);

        if (!$article) {
            $bot->answerCallbackQuery("❌ Article non trouvé", show_alert: true);
            return;
        }

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Oui, supprimer', callback_data: "article_delete_confirm_{$articleId}"),
                InlineKeyboardButton::make('❌ Annuler', callback_data: "article_view_{$articleId}")
            );

        $message = "⚠️ <b>Confirmation de suppression</b>\n\n"
            . "Êtes-vous sûr de vouloir supprimer cet article ?\n\n"
            . "📦 <b>{$article->article_name}</b>\n"
            . "🔖 Réf: {$article->article_reference}\n"
            . "📦 Stock: {$article->quantity_stock} {$article->article_unité}\n\n"
            . "⚠️ Cette action est irréversible !";

        $bot->editMessageText($message, parse_mode: 'HTML', reply_markup: $keyboard);
        $bot->answerCallbackQuery();
    }

    /**
     * Confirmer la suppression
     */
    public static function confirmDelete(Nutgram $bot, int $articleId): void
    {
        $article = Article::find($articleId);

        if (!$article) {
            $bot->answerCallbackQuery("❌ Article non trouvé", show_alert: true);
            return;
        }

        $articleName = $article->article_name;

        // Supprimer aussi les mouvements associés
        MvtArticle::where('article_id', $articleId)->delete();
        $article->delete();

        $bot->editMessageText(
            "✅ <b>Article supprimé</b>\n\n"
            . "L'article <b>{$articleName}</b> et son historique ont été supprimés avec succès.",
            parse_mode: 'HTML',
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('🔙 Liste des articles', callback_data: 'article_list'))
        );

        $bot->answerCallbackQuery("✅ Article supprimé");
    }

    /**
     * Retour au menu principal des articles
     */
    public static function showMenu(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->first();
        $articleCount = Article::where('user_id', $user->id)->count();
        $totalStock = Article::where('user_id', $user->id)->sum('quantity_stock');

        $message = "📦 <b>Gestion des Articles</b>\n\n"
            . "📊 Vous avez <b>{$articleCount} article(s)</b>\n"
            . "📦 Stock total : <b>{$totalStock} unités</b>\n\n"
            . "Que souhaitez-vous faire ?";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('➕ Ajouter un article', callback_data: 'article_add'),
                InlineKeyboardButton::make('📋 Voir mes articles', callback_data: 'article_list')
            )
            ->addRow(
                InlineKeyboardButton::make('🔍 Rechercher', callback_data: 'article_search')
            )
            ->addRow(
                InlineKeyboardButton::make('🏢 Menu Principal', callback_data: 'menu_back')
            );

        $bot->editMessageText(
            text: $message,
            parse_mode: 'HTML',
            reply_markup: $keyboard
        );

        $bot->answerCallbackQuery();
    }
}

/**
 * Handler pour les messages en attente de données article
 */
class ArticleMessageHandler
{
    public function handle(Nutgram $bot): void
    {
        // Ajout d'un nouvel article
        if ($bot->getGlobalData('awaiting_article_data')) {
            ArticleCallbackHandler::processArticleData($bot);
            return;
        }

        // Modification d'un article
        if ($articleId = $bot->getGlobalData('awaiting_article_edit')) {
            ArticleCallbackHandler::processArticleEdit($bot, $articleId);
            return;
        }

        // Ajout de stock
        if ($articleId = $bot->getGlobalData('awaiting_stock_add')) {
            ArticleCallbackHandler::processStockAdd($bot, $articleId);
            return;
        }

        // Retrait de stock
        if ($articleId = $bot->getGlobalData('awaiting_stock_remove')) {
            ArticleCallbackHandler::processStockRemove($bot, $articleId);
            return;
        }

        // Remplacement de stock
        if ($articleId = $bot->getGlobalData('awaiting_stock_replace')) {
            ArticleCallbackHandler::processStockReplace($bot, $articleId);
            return;
        }
    }
}

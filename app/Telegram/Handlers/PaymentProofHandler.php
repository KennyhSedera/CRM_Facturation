<?php

namespace App\Telegram\Handlers;

use SergiX44\Nutgram\Nutgram;
use App\Models\User;
use App\Models\Payment;
use App\Models\Company;
use App\Telegram\Commands\CreateCompanyCommand;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class PaymentProofHandler
{
    /**
     * Traiter la réception d'une photo
     */
    public static function handlePhoto(Nutgram $bot): void
    {
        // Vérifier si c'est pour un paiement de création
        $awaitingCreationProof = $bot->getGlobalData('awaiting_creation_payment_proof');
        if ($awaitingCreationProof) {
            self::handleCreationPaymentPhoto($bot);
            return;
        }

        // Sinon, traiter comme un paiement de renouvellement
        $awaitingProof = $bot->getGlobalData('awaiting_payment_proof');
        if (!$awaitingProof) {
            return;
        }

        self::handleSubscriptionPaymentPhoto($bot);
    }

    /**
     * Traiter la photo pour un paiement de création d'entreprise
     */
    private static function handleCreationPaymentPhoto(Nutgram $bot): void
    {
        $plan = $bot->getGlobalData('creation_payment_plan');
        $paymentMethod = $bot->getGlobalData('creation_payment_method');

        try {
            $photos = $bot->message()->photo;
            $photo = end($photos);

            $file = $bot->getFile($photo->file_id);
            $filePath = $file->file_path;

            $fileUrl = "https://api.telegram.org/file/bot" . env('TELEGRAM_BOT_TOKEN') . "/{$filePath}";

            $response = Http::withOptions([
                'verify' => env('TELEGRAM_VERIFY_SSL', true),
            ])->timeout(30)->get($fileUrl);

            if (!$response->successful()) {
                throw new \Exception('Failed to download file from Telegram');
            }

            $fileContent = $response->body();
            $fileName = 'payment_proofs/creation_' . uniqid('proof_') . '_' . time() . '.jpg';
            Storage::disk('public')->put($fileName, $fileContent);

            // Sauvegarder le chemin du fichier pour l'utiliser après création de l'entreprise
            $bot->setUserData('pending_payment_proof', $fileName);
            $bot->setUserData('pending_payment_method', $paymentMethod);
            $bot->setUserData('pending_payment_file_id', $photo->file_id);

            $paymentReference = 'CREATION_' . strtoupper(substr(md5(time() . $bot->userId()), 0, 10));

            $bot->sendMessage(
                "✅ <b>Preuve de paiement reçue !</b>\n\n"
                . "📱 Référence temporaire : <code>{$paymentReference}</code>\n\n"
                . "⏳ <b>Prochaines étapes :</b>\n"
                . "1️⃣ Notre équipe vérifie votre paiement\n"
                . "2️⃣ Votre entreprise sera créée et activée\n"
                . "3️⃣ Vous recevrez une confirmation sous 5-10 minutes\n\n"
                . "💡 Questions ? /help",
                parse_mode: 'HTML'
            );

            // Créer l'entreprise maintenant (inactive)
            CreateCompanyCommand::createCompany($bot, false);

            // Après création, créer le paiement
            $user = User::where('telegram_id', $bot->user()->id)->with('company')->first();
            if ($user && $user->company_id) {
                $payment = Payment::createPayment([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'payment_method' => $paymentMethod,
                    'plan_type' => $plan,
                    'action_type' => 'creation',
                    'amount' => self::getPlanPrice($plan),
                    'currency' => 'MGA',
                    'transaction_proof' => $fileName,
                ]);

                self::notifyAdminsCreationPayment($bot, $user, $payment, $photo->file_id);
            }

            // Nettoyer les données temporaires
            $bot->deleteGlobalData('awaiting_creation_payment_proof');
            $bot->deleteGlobalData('creation_payment_plan');
            $bot->deleteGlobalData('creation_payment_method');
            $bot->deleteUserData('pending_payment_proof');
            $bot->deleteUserData('pending_payment_method');
            $bot->deleteUserData('pending_payment_file_id');

        } catch (\Exception $e) {
            \Log::error('Failed to process creation payment proof: ' . $e->getMessage());
            $bot->sendMessage(
                "❌ Erreur lors du traitement de votre preuve.\n\n"
                . "Veuillez réessayer ou contacter le support : /help"
            );
        }
    }

    /**
     * Traiter la photo pour un paiement de renouvellement
     */
    private static function handleSubscriptionPaymentPhoto(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->with('company')->first();

        if (!$user) {
            $bot->sendMessage("❌ Utilisateur non trouvé.");
            return;
        }

        $plan = $bot->getGlobalData('payment_plan');
        $action = $bot->getGlobalData('payment_action');
        $paymentMethod = $bot->getGlobalData('payment_method');

        try {
            $photos = $bot->message()->photo;
            $photo = end($photos);

            $file = $bot->getFile($photo->file_id);
            $filePath = $file->file_path;

            $fileUrl = "https://api.telegram.org/file/bot" . env('TELEGRAM_BOT_TOKEN') . "/{$filePath}";

            $response = Http::withOptions([
                'verify' => env('TELEGRAM_VERIFY_SSL', true),
            ])->timeout(30)->get($fileUrl);

            if (!$response->successful()) {
                throw new \Exception('Failed to download file from Telegram');
            }

            $fileContent = $response->body();
            $fileName = 'payment_proofs/' . uniqid('proof_') . '_' . time() . '.jpg';
            Storage::disk('public')->put($fileName, $fileContent);

            $payment = Payment::createPayment([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'payment_method' => $paymentMethod,
                'plan_type' => $plan,
                'action_type' => $action,
                'amount' => self::getPlanPrice($plan),
                'currency' => 'MGA',
                'transaction_proof' => $fileName,
            ]);

            $bot->sendMessage(
                "✅ <b>Preuve de paiement reçue !</b>\n\n"
                . "📱 Référence : <code>{$payment->payment_reference}</code>\n\n"
                . "⏳ <b>Prochaines étapes :</b>\n"
                . "1️⃣ Notre équipe vérifie votre paiement\n"
                . "2️⃣ Vous recevrez une confirmation sous 5-10 minutes\n"
                . "3️⃣ Votre plan sera activé automatiquement\n\n"
                . "💡 Questions ? /help",
                parse_mode: 'HTML'
            );

            self::notifyAdminsWithProof($bot, $user, $payment, $photo->file_id);

            $bot->deleteGlobalData('awaiting_payment_proof');
            $bot->deleteGlobalData('payment_plan');
            $bot->deleteGlobalData('payment_action');
            $bot->deleteGlobalData('payment_method');

        } catch (\Exception $e) {
            \Log::error('Failed to process payment proof: ' . $e->getMessage());
            $bot->sendMessage(
                "❌ Erreur lors du traitement de votre preuve.\n\n"
                . "Veuillez réessayer ou contacter le support : /help"
            );
        }
    }

    /**
     * Traiter la réception d'un document
     */
    public static function handleDocument(Nutgram $bot): void
    {
        // Vérifier si c'est pour un paiement de création
        $awaitingCreationProof = $bot->getGlobalData('awaiting_creation_payment_proof');
        if ($awaitingCreationProof) {
            self::handleCreationPaymentDocument($bot);
            return;
        }

        // Sinon, traiter comme un paiement de renouvellement
        $awaitingProof = $bot->getGlobalData('awaiting_payment_proof');
        if (!$awaitingProof) {
            return;
        }

        self::handleSubscriptionPaymentDocument($bot);
    }

    /**
     * Traiter le document pour un paiement de création
     */
    private static function handleCreationPaymentDocument(Nutgram $bot): void
    {
        $plan = $bot->getGlobalData('creation_payment_plan');
        $paymentMethod = $bot->getGlobalData('creation_payment_method');

        try {
            $document = $bot->message()->document;

            $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!in_array($document->mime_type, $allowedMimes)) {
                $bot->sendMessage(
                    "❌ Type de fichier non supporté.\n\n"
                    . "Formats acceptés : JPG, PNG, PDF"
                );
                return;
            }

            $file = $bot->getFile($document->file_id);
            $filePath = $file->file_path;

            $fileUrl = "https://api.telegram.org/file/bot" . env('TELEGRAM_BOT_TOKEN') . "/{$filePath}";

            $response = Http::withOptions([
                'verify' => env('TELEGRAM_VERIFY_SSL', true),
            ])->timeout(30)->get($fileUrl);

            if (!$response->successful()) {
                throw new \Exception('Failed to download file from Telegram');
            }

            $fileContent = $response->body();

            $extension = match ($document->mime_type) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'application/pdf' => 'pdf',
                default => 'bin'
            };

            $fileName = 'payment_proofs/creation_' . uniqid('proof_') . '_' . time() . '.' . $extension;
            Storage::disk('public')->put($fileName, $fileContent);

            $bot->setUserData('pending_payment_proof', $fileName);
            $bot->setUserData('pending_payment_method', $paymentMethod);
            $bot->setUserData('pending_payment_file_id', $document->file_id);

            $paymentReference = 'CREATION_' . strtoupper(substr(md5(time() . $bot->userId()), 0, 10));

            $bot->sendMessage(
                "✅ <b>Preuve de paiement reçue !</b>\n\n"
                . "📱 Référence temporaire : <code>{$paymentReference}</code>\n\n"
                . "⏳ <b>Prochaines étapes :</b>\n"
                . "1️⃣ Notre équipe vérifie votre paiement\n"
                . "2️⃣ Votre entreprise sera créée et activée\n"
                . "3️⃣ Vous recevrez une confirmation sous 5-10 minutes\n\n"
                . "💡 Questions ? /help",
                parse_mode: 'HTML'
            );

            CreateCompanyCommand::createCompany($bot, false);

            $user = User::where('telegram_id', $bot->user()->id)->with('company')->first();
            if ($user && $user->company_id) {
                $payment = Payment::createPayment([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'payment_method' => $paymentMethod,
                    'plan_type' => $plan,
                    'action_type' => 'creation',
                    'amount' => self::getPlanPrice($plan),
                    'transaction_proof' => $fileName,
                ]);

                self::notifyAdminsCreationPayment($bot, $user, $payment, $document->file_id);
            }

            $bot->deleteGlobalData('awaiting_creation_payment_proof');
            $bot->deleteGlobalData('creation_payment_plan');
            $bot->deleteGlobalData('creation_payment_method');
            $bot->deleteUserData('pending_payment_proof');
            $bot->deleteUserData('pending_payment_method');
            $bot->deleteUserData('pending_payment_file_id');

        } catch (\Exception $e) {
            \Log::error('Failed to process creation payment document: ' . $e->getMessage());
            $bot->sendMessage(
                "❌ Erreur lors du traitement de votre document.\n\n"
                . "Veuillez réessayer ou contacter le support : /help"
            );
        }
    }

    /**
     * Traiter le document pour un paiement de renouvellement
     */
    private static function handleSubscriptionPaymentDocument(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->with('company')->first();

        if (!$user) {
            $bot->sendMessage("❌ Utilisateur non trouvé.");
            return;
        }

        $plan = $bot->getGlobalData('payment_plan');
        $action = $bot->getGlobalData('payment_action');
        $paymentMethod = $bot->getGlobalData('payment_method');

        try {
            $document = $bot->message()->document;

            $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!in_array($document->mime_type, $allowedMimes)) {
                $bot->sendMessage(
                    "❌ Type de fichier non supporté.\n\n"
                    . "Formats acceptés : JPG, PNG, PDF"
                );
                return;
            }

            $file = $bot->getFile($document->file_id);
            $filePath = $file->file_path;

            $fileUrl = "https://api.telegram.org/file/bot" . env('TELEGRAM_BOT_TOKEN') . "/{$filePath}";

            $response = Http::withOptions([
                'verify' => env('TELEGRAM_VERIFY_SSL', true),
            ])->timeout(30)->get($fileUrl);

            if (!$response->successful()) {
                throw new \Exception('Failed to download file from Telegram');
            }

            $fileContent = $response->body();

            $extension = match ($document->mime_type) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'application/pdf' => 'pdf',
                default => 'bin'
            };

            $fileName = 'payment_proofs/' . uniqid('proof_') . '_' . time() . '.' . $extension;
            Storage::disk('public')->put($fileName, $fileContent);

            $payment = Payment::createPayment([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'payment_method' => $paymentMethod,
                'plan_type' => $plan,
                'action_type' => $action,
                'amount' => self::getPlanPrice($plan),
                'currency' => 'MGA',
                'transaction_proof' => $fileName,
            ]);

            $bot->sendMessage(
                "✅ <b>Preuve de paiement reçue !</b>\n\n"
                . "📱 Référence : <code>{$payment->payment_reference}</code>\n\n"
                . "⏳ <b>Prochaines étapes :</b>\n"
                . "1️⃣ Notre équipe vérifie votre paiement\n"
                . "2️⃣ Vous recevrez une confirmation sous 5-10 minutes\n"
                . "3️⃣ Votre plan sera activé automatiquement\n\n"
                . "💡 Questions ? /help",
                parse_mode: 'HTML'
            );

            self::notifyAdminsWithProof($bot, $user, $payment, $document->file_id);

            $bot->deleteGlobalData('awaiting_payment_proof');
            $bot->deleteGlobalData('payment_plan');
            $bot->deleteGlobalData('payment_action');
            $bot->deleteGlobalData('payment_method');

        } catch (\Exception $e) {
            \Log::error('Failed to process payment document: ' . $e->getMessage());
            $bot->sendMessage(
                "❌ Erreur lors du traitement de votre document.\n\n"
                . "Veuillez réessayer ou contacter le support : /help"
            );
        }
    }

    /**
     * Traiter un message texte (numéro de transaction)
     */
    public static function handleTransactionNumber(Nutgram $bot): void
    {
        $awaitingCreationProof = $bot->getGlobalData('awaiting_creation_payment_proof');
        $awaitingProof = $bot->getGlobalData('awaiting_payment_proof');

        if (!$awaitingCreationProof && !$awaitingProof) {
            return;
        }

        $text = trim($bot->message()->text);

        if (str_starts_with($text, '/')) {
            return;
        }

        if ($awaitingCreationProof) {
            self::handleCreationTransactionNumber($bot, $text);
        } else {
            self::handleSubscriptionTransactionNumber($bot, $text);
        }
    }

    /**
     * Traiter le numéro de transaction pour création
     */
    private static function handleCreationTransactionNumber(Nutgram $bot, string $text): void
    {
        $plan = $bot->getGlobalData('creation_payment_plan');
        $paymentMethod = $bot->getGlobalData('creation_payment_method');

        try {
            $bot->setUserData('pending_transaction_id', $text);
            $bot->setUserData('pending_payment_method', $paymentMethod);

            $bot->sendMessage(
                "✅ <b>Numéro de transaction enregistré !</b>\n\n"
                . "💳 Transaction : <code>{$text}</code>\n\n"
                . "📸 Vous pouvez maintenant envoyer la capture d'écran de votre reçu.\n\n"
                . "Ou votre entreprise sera créée avec ce numéro de transaction.",
                parse_mode: 'HTML'
            );

        } catch (\Exception $e) {
            \Log::error('Failed to save creation transaction number: ' . $e->getMessage());
            $bot->sendMessage("❌ Erreur lors de l'enregistrement.");
        }
    }

    /**
     * Traiter le numéro de transaction pour renouvellement
     */
    private static function handleSubscriptionTransactionNumber(Nutgram $bot, string $text): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->with('company')->first();

        if (!$user) {
            return;
        }

        $plan = $bot->getGlobalData('payment_plan');
        $action = $bot->getGlobalData('payment_action');
        $paymentMethod = $bot->getGlobalData('payment_method');

        try {
            $payment = Payment::createPayment([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'payment_method' => $paymentMethod,
                'plan_type' => $plan,
                'action_type' => $action,
                'amount' => self::getPlanPrice($plan),
                'currency' => 'MGA',
                'transaction_id' => $text,
            ]);

            $bot->sendMessage(
                "✅ <b>Numéro de transaction enregistré !</b>\n\n"
                . "📱 Référence : <code>{$payment->payment_reference}</code>\n"
                . "💳 Transaction : <code>{$text}</code>\n\n"
                . "📸 Vous pouvez maintenant envoyer la capture d'écran de votre reçu.\n\n"
                . "Ou tapez /cancel pour annuler.",
                parse_mode: 'HTML'
            );

        } catch (\Exception $e) {
            \Log::error('Failed to save transaction number: ' . $e->getMessage());
            $bot->sendMessage("❌ Erreur lors de l'enregistrement.");
        }
    }

    /**
     * Notifier les admins avec la preuve (renouvellement)
     */
    private static function notifyAdminsWithProof(Nutgram $bot, User $user, Payment $payment, string $fileId): void
    {
        $adminIds = explode(',', env('TELEGRAM_ADMIN_IDS', ''));

        $actionText = $payment->action_type === 'renew' ? 'Renouvellement' : 'Upgrade';
        $methodText = $payment->payment_method === 'mobile_money' ? 'Mobile Money' : 'Virement bancaire';

        $message = "🔔 <b>Nouvelle preuve de paiement reçue</b>\n\n"
            . "👤 Utilisateur : {$user->name}\n"
            . "🏢 Entreprise : {$user->company->company_name}\n"
            . "💳 Type : {$actionText}\n"
            . "📦 Plan : " . strtoupper($payment->plan_type) . "\n"
            . "💰 Montant : " . number_format((float) $payment->amount, 0, ',', ' ') . " FCFA\n"
            . "🏦 Méthode : {$methodText}\n"
            . "📅 Date : " . now()->format('d/m/Y H:i') . "\n\n"
            . "🆔 User ID : {$user->id}\n"
            . "🆔 Telegram ID : {$user->telegram_id}\n"
            . "📱 Référence : <code>{$payment->payment_reference}</code>\n\n"
            . "👉 Utilisez cette référence pour valider le paiement.";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Valider', callback_data: "admin_payment_approve_{$payment->payment_id}"),
                InlineKeyboardButton::make('❌ Rejeter', callback_data: "admin_payment_reject_{$payment->payment_id}")
            );

        foreach ($adminIds as $adminId) {
            if ($adminId) {
                try {
                    $bot->sendPhoto(
                        $fileId,
                        chat_id: trim($adminId),
                        caption: $message,
                        parse_mode: 'HTML',
                        reply_markup: $keyboard
                    );
                } catch (\Exception $e) {
                    \Log::error("Failed to notify admin {$adminId}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Notifier les admins pour paiement de création
     */
    private static function notifyAdminsCreationPayment(Nutgram $bot, User $user, Payment $payment, string $fileId): void
    {
        $adminIds = explode(',', env('TELEGRAM_ADMIN_IDS', ''));

        $methodText = $payment->payment_method === 'mobile_money' ? 'Mobile Money' : 'Virement bancaire';

        $message = "🔔 <b>Nouveau paiement de création d'entreprise</b>\n\n"
            . "👤 Utilisateur : {$user->name}\n"
            . "🏢 Entreprise : {$user->company->company_name}\n"
            . "💳 Type : <b>CRÉATION ENTREPRISE</b>\n"
            . "📦 Plan : " . strtoupper($payment->plan_type) . "\n"
            . "💰 Montant : " . number_format((float) $payment->amount, 0, ',', ' ') . " FCFA\n"
            . "🏦 Méthode : {$methodText}\n"
            . "📅 Date : " . now()->format('d/m/Y H:i') . "\n\n"
            . "🆔 Company ID : {$user->company_id}\n"
            . "🆔 User ID : {$user->id}\n"
            . "🆔 Telegram ID : {$user->telegram_id}\n"
            . "📱 Référence : <code>{$payment->payment_reference}</code>\n\n"
            . "⚠️ <b>L'entreprise est inactive jusqu'à validation du paiement</b>\n\n"
            . "👉 Validez le paiement pour activer l'entreprise automatiquement.";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Valider', callback_data: "admin_payment_approve_{$payment->payment_id}"),
                InlineKeyboardButton::make('❌ Rejeter', callback_data: "admin_payment_reject_{$payment->payment_id}")
            );

        foreach ($adminIds as $adminId) {
            if ($adminId) {
                try {
                    $bot->sendPhoto(
                        $fileId,
                        chat_id: trim($adminId),
                        caption: $message,
                        parse_mode: 'HTML',
                        reply_markup: $keyboard
                    );
                } catch (\Exception $e) {
                    \Log::error("Failed to notify admin {$adminId}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Obtenir le prix d'un plan
     */
    private static function getPlanPrice(string $plan): int
    {
        $prices = Payment::getPlanPrices();

        return match ($plan) {
            'premium' => $prices['premium'] ?? 9900,
            'entreprise', 'enterprise' => $prices['enterprise'] ?? 14900,
            default => 0
        };
    }
}

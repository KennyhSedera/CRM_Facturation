<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SergiX44\Nutgram\Nutgram;

class SetupTelegramWebhook extends Command
{
    protected $signature = 'telegram:webhook:set';
    protected $description = 'Configure le webhook Telegram';

    public function handle(Nutgram $bot)
    {
        $webhookUrl = env('TELEGRAM_WEBHOOK_URL');

        if (!$webhookUrl) {
            $this->error('❌ TELEGRAM_WEBHOOK_URL non défini dans .env');
            return 1;
        }

        $this->info("Configuration du webhook : {$webhookUrl}");

        try {
            // Signature correcte : setWebhook(string $url, ?string $certificate = null, array $opt = [])
            $result = $bot->setWebhook(
                url: $webhookUrl,
                drop_pending_updates: true
            );

            if ($result) {
                $this->info('✅ Webhook configuré avec succès !');

                // Afficher les informations
                $info = $bot->getWebhookInfo();

                $this->newLine();
                $this->table(
                    ['Propriété', 'Valeur'],
                    [
                        ['URL', $info->url ?? 'N/A'],
                        ['Actif', $info->url ? 'Oui ✅' : 'Non ❌'],
                        ['Mises à jour en attente', $info->pending_update_count ?? 0],
                    ]
                );

                return 0;
            }

            $this->error('❌ Échec de la configuration');
            return 1;

        } catch (\Throwable $e) {
            $this->error('❌ Erreur : ' . $e->getMessage());
            return 1;
        }
    }
}

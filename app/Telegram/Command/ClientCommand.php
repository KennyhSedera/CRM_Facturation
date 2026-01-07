<?php

namespace App\Telegram\Commands;

use App\Models\Client;
use App\Services\ClientService;
use Telegram\Bot\Commands\Command;

class ClientCommand extends Command
{
    protected string $name = 'client';
    protected string $description = 'Fiche client détaillée - /client [nom]';

    public function handle()
    {
        $searchTerm = $this->extractSearchTerm();

        if (empty($searchTerm)) {
            $this->sendUsageMessage();
            return;
        }

        $client = $this->findClient($searchTerm);

        if (!$client) {
            $this->sendClientNotFoundMessage($searchTerm);
            return;
        }

        $this->displayClientDetails($client->id);
    }

    public function handleCallback($chatId)
    {
        // Par défaut, afficher un message d'aide car on ne peut pas passer d'argument via un bouton
        \Telegram\Bot\Laravel\Facades\Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "❗️ Pour utiliser cette commande, tapez : /client [nom du client]",
        ]);
    }

    private function extractSearchTerm(): string
    {
        $arguments = $this->getArguments();
        $searchTerm = trim(implode(' ', $arguments));

        if (empty($searchTerm)) {
            $messageText = $this->getUpdate()->getMessage()->getText();
            $searchTerm = trim(preg_replace('/^\/client(@\w+)?\s*/', '', $messageText));
        }

        return $searchTerm;
    }

    private function findClient(string $searchTerm): ?Client
    {
        return Client::where('name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('email', 'LIKE', "%{$searchTerm}%")
            ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
            ->first();
    }

    private function sendUsageMessage(): void
    {
        $this->replyWithMessage([
            'text' => "❌ Veuillez spécifier le nom du client.\n\n" .
                     "<b>Usage :</b> /client [nom du client]\n\n" .
                     "<b>Exemples :</b>\n" .
                     "• /client Jean Dupont\n" .
                     "• /client jean.dupont@email.com\n" .
                     "• /client 0123456789",
            'parse_mode' => 'HTML'
        ]);
    }

    private function sendClientNotFoundMessage(string $searchTerm): void
    {
        $similarClients = Client::where('name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('email', 'LIKE', "%{$searchTerm}%")
            ->limit(3)
            ->get();

        $message = "❌ Aucun client trouvé pour \"<b>" . htmlspecialchars($searchTerm) . "</b>\".\n\n";

        if ($similarClients->count() > 0) {
            $message .= "🔍 Suggestions :\n";
            foreach ($similarClients as $client) {
                $message .= "• <code>/client {$client->name}</code>\n";
            }
        } else {
            $message .= "💡 Vérifiez l'orthographe ou utilisez /clients pour lister tous les clients.";
        }

        $this->replyWithMessage([
            'text' => $message,
            'parse_mode' => 'HTML'
        ]);
    }

    private function displayClientDetails(int $clientId): void
    {
        try {
            $clientService = new ClientService();
            $details = $clientService->getClientDetails($clientId);

            $this->replyWithMessage([
                'text' => $details,
                'parse_mode' => 'HTML'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error displaying client details:', [
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);

            $this->replyWithMessage([
                'text' => "❌ Erreur lors de l'affichage des détails du client.\n\n" .
                         "🔄 Essayez à nouveau ou contactez l'administrateur.",
                'parse_mode' => 'HTML'
            ]);
        }
    }
}

<?php

namespace App\Telegram\Commands;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;
use App\Telegram\Conversations\CreateTicketConversation;

class TicketCommand extends Command
{
    protected string $command = 'ticket';
    protected ?string $description = 'Créer un nouveau ticket';

    public function handle(Nutgram $bot): void
    {
        // Démarrer une conversation
        CreateTicketConversation::begin($bot);
    }
}

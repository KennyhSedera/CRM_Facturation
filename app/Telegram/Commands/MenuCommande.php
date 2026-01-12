<?php

namespace App\Telegram\Commands;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;
use App\Models\User;
use App\Telegram\Callbacks\MenuCallback;

class MenuCommande extends Command
{
    protected string $command = 'menu';
    protected ?string $description = 'Menu du bot';

    public function handle(Nutgram $bot): void
    {
        MenuCallback::showMenu($bot);
    }
}

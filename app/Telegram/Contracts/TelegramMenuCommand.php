<?php

namespace App\Telegram\Contracts;

interface TelegramMenuCommand
{
    public static function command(): string;

    public static function description(string $locale = 'fr'): string;

    public static function adminOnly(): bool;
}

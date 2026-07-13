<?php

namespace Arthurpar06\DiscordNotifier\Transport;

use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;

/**
 * Selects the transport that matches a resolved route.
 */
class TransportFactory
{
    public function for(DiscordRoute $route): Transport
    {
        if ($route->isWebhook()) {
            return new WebhookTransport;
        }

        /** @var string|null $token */
        $token = config('discord-notifier.bot.token');

        /** @var string $apiBase */
        $apiBase = config('discord-notifier.bot.api_base', 'https://discord.com/api/v10');

        return new BotTransport($token, $apiBase);
    }
}

<?php

namespace Arthurpar06\DiscordNotifier\Transport;

use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;
use Illuminate\Support\Facades\Http;

/**
 * Delivers a message by executing a Discord webhook: a plain JSON POST to the
 * webhook URL, with no authentication header.
 */
class WebhookTransport implements Transport
{
    public function send(DiscordRoute $route, array $payload): void
    {
        Http::asJson()
            ->post($route->target(), $payload)
            ->throw();
    }
}

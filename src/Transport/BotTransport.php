<?php

namespace Arthurpar06\DiscordNotifier\Transport;

use Arthurpar06\DiscordNotifier\Exceptions\DiscordConfigurationException;
use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;
use Illuminate\Support\Facades\Http;

/**
 * Delivers a message as a bot via POST {api_base}/channels/{id}/messages with a
 * Bot authorization header. The channel id may be a guild channel or a user's
 * DM channel — Discord treats both the same way.
 */
class BotTransport implements Transport
{
    public function __construct(
        protected ?string $token,
        protected string $apiBase,
    ) {}

    public function send(DiscordRoute $route, array $payload): void
    {
        if ($this->token === null || $this->token === '') {
            throw DiscordConfigurationException::missingBotToken();
        }

        $endpoint = rtrim($this->apiBase, '/')."/channels/{$route->target()}/messages";

        Http::withToken($this->token, 'Bot')
            ->asJson()
            ->post($endpoint, $payload)
            ->throw();
    }
}

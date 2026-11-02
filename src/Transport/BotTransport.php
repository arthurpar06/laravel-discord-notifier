<?php

namespace Arthurpar06\DiscordNotifier\Transport;

use Arthurpar06\DiscordNotifier\Exceptions\DiscordConfigurationException;
use Arthurpar06\DiscordNotifier\Messages\SentMessage;
use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Delivers a message as a bot via POST {api_base}/channels/{id}/messages with a
 * Bot authorization header, and edits it via PATCH on the message. The channel
 * id may be a guild channel or a user's DM channel — Discord treats both the
 * same way.
 */
class BotTransport implements Transport
{
    public function __construct(
        protected ?string $token,
        protected string $apiBase,
    ) {}

    public function send(DiscordRoute $route, array $payload): SentMessage
    {
        return SentMessage::fromHttpResponse(
            $this->request()->post($this->endpoint($route), $payload)->throw()
        );
    }

    public function edit(DiscordRoute $route, string $messageId, array $payload): SentMessage
    {
        return SentMessage::fromHttpResponse(
            $this->request()->patch($this->endpoint($route).'/'.rawurlencode($messageId), $payload)->throw()
        );
    }

    protected function request(): PendingRequest
    {
        if ($this->token === null || $this->token === '') {
            throw DiscordConfigurationException::missingBotToken();
        }

        return Http::withToken($this->token, 'Bot')->asJson();
    }

    protected function endpoint(DiscordRoute $route): string
    {
        return rtrim($this->apiBase, '/')."/channels/{$route->target()}/messages";
    }
}

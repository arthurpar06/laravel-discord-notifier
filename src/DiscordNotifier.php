<?php

namespace Arthurpar06\DiscordNotifier;

use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;
use Arthurpar06\DiscordNotifier\Messages\SentMessage;
use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;
use Arthurpar06\DiscordNotifier\Transport\TransportFactory;

/**
 * Sends a message to Discord and edits one already sent, outside the
 * notification system. Use it when you need the sent message back (to store its
 * id and edit it later); the "discord" notification channel delegates here.
 *
 * It holds no per-call state, so it is safe to resolve once and reuse.
 */
final class DiscordNotifier
{
    public function __construct(private readonly TransportFactory $transports = new TransportFactory) {}

    /**
     * Send a message and return the Discord message that was created.
     */
    public function send(DiscordRoute|string $route, DiscordMessage $message): SentMessage
    {
        $route = DiscordRoute::resolve($route);

        return $this->transports->for($route)->send($route, $message->toArray());
    }

    /**
     * Replace the content of a message previously sent to the same route.
     */
    public function edit(DiscordRoute|string $route, string $messageId, DiscordMessage $message): SentMessage
    {
        $route = DiscordRoute::resolve($route);

        return $this->transports->for($route)->edit($route, $messageId, $message->toEditArray());
    }
}

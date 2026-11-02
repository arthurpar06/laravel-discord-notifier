<?php

namespace Arthurpar06\DiscordNotifier\Transport;

use Arthurpar06\DiscordNotifier\Messages\SentMessage;
use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;

interface Transport
{
    /**
     * Deliver a serialized Create Message body to the given route.
     *
     * @param  array<string, mixed>  $payload
     */
    public function send(DiscordRoute $route, array $payload): SentMessage;

    /**
     * Replace the content of a message previously sent to the given route.
     *
     * @param  array<string, mixed>  $payload
     */
    public function edit(DiscordRoute $route, string $messageId, array $payload): SentMessage;
}

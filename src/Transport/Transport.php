<?php

namespace Arthurpar06\DiscordNotifier\Transport;

use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;

interface Transport
{
    /**
     * Deliver a serialized Create Message body to the given route.
     *
     * @param  array<string, mixed>  $payload
     */
    public function send(DiscordRoute $route, array $payload): void;
}

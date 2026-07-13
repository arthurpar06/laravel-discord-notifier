<?php

namespace Arthurpar06\DiscordNotifier\Contracts;

/**
 * A Discord payload object that can serialize itself to the array shape the
 * Discord API expects. Every builder object in this package implements it.
 */
interface Arrayable
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}

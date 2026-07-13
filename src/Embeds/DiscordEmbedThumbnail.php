<?php

namespace Arthurpar06\DiscordNotifier\Embeds;

use Arthurpar06\DiscordNotifier\Contracts\Arrayable;
use Arthurpar06\DiscordNotifier\Support\Serializer;

class DiscordEmbedThumbnail implements Arrayable
{
    final public function __construct(protected string $url) {}

    public static function make(string $url): static
    {
        return new static($url);
    }

    public function toArray(): array
    {
        return Serializer::build([
            'url' => $this->url,
        ]);
    }
}

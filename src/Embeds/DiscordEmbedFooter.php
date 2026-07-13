<?php

namespace Arthurpar06\DiscordNotifier\Embeds;

use Arthurpar06\DiscordNotifier\Contracts\Arrayable;
use Arthurpar06\DiscordNotifier\Support\Serializer;

class DiscordEmbedFooter implements Arrayable
{
    protected ?string $iconUrl = null;

    final public function __construct(protected string $text) {}

    public static function make(string $text): static
    {
        return new static($text);
    }

    public function iconUrl(?string $iconUrl): static
    {
        $this->iconUrl = $iconUrl;

        return $this;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function toArray(): array
    {
        return Serializer::build([
            'text' => $this->text,
            'icon_url' => $this->iconUrl,
        ]);
    }
}

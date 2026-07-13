<?php

namespace Arthurpar06\DiscordNotifier\Embeds;

use Arthurpar06\DiscordNotifier\Contracts\Arrayable;
use Arthurpar06\DiscordNotifier\Support\Serializer;

class DiscordEmbedAuthor implements Arrayable
{
    protected ?string $url = null;

    protected ?string $iconUrl = null;

    final public function __construct(protected string $name) {}

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function url(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function iconUrl(?string $iconUrl): static
    {
        $this->iconUrl = $iconUrl;

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return Serializer::build([
            'name' => $this->name,
            'url' => $this->url,
            'icon_url' => $this->iconUrl,
        ]);
    }
}

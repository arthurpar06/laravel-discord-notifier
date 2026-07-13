<?php

namespace Arthurpar06\DiscordNotifier\Embeds;

use Arthurpar06\DiscordNotifier\Contracts\Arrayable;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;
use Arthurpar06\DiscordNotifier\Support\Serializer;

class DiscordEmbedField implements Arrayable
{
    public const MAX_NAME = 256;

    public const MAX_VALUE = 1024;

    protected ?bool $inline = null;

    final public function __construct(protected string $name, protected string $value) {}

    public static function make(string $name, string $value): static
    {
        return new static($name, $value);
    }

    public function inline(bool $inline = true): static
    {
        $this->inline = $inline;

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function validate(): void
    {
        if (mb_strlen($this->name) > self::MAX_NAME) {
            throw InvalidDiscordMessageException::limitExceeded('embed.field.name', mb_strlen($this->name), self::MAX_NAME, 'characters');
        }

        if (mb_strlen($this->value) > self::MAX_VALUE) {
            throw InvalidDiscordMessageException::limitExceeded('embed.field.value', mb_strlen($this->value), self::MAX_VALUE, 'characters');
        }
    }

    public function toArray(): array
    {
        $this->validate();

        return Serializer::build([
            'name' => $this->name,
            'value' => $this->value,
            'inline' => $this->inline,
        ]);
    }
}

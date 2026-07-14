<?php

namespace Arthurpar06\DiscordNotifier\Components;

use Arthurpar06\DiscordNotifier\Contracts\Arrayable;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;
use Arthurpar06\DiscordNotifier\Support\Serializer;

/**
 * A Discord action row (component type 1): a horizontal container holding one
 * to five buttons.
 *
 * @see https://docs.discord.com/developers/components/reference#action-row
 *
 * @phpstan-consistent-constructor
 */
class ActionRow implements Arrayable
{
    public const MAX_BUTTONS = 5;

    /** @var array<int, Button> */
    protected array $buttons;

    public function __construct(Button ...$buttons)
    {
        $this->buttons = array_values($buttons);
    }

    public static function make(Button ...$buttons): static
    {
        return new static(...$buttons);
    }

    public function button(Button $button): static
    {
        $this->buttons[] = $button;

        return $this;
    }

    public function validate(): void
    {
        if ($this->buttons === []) {
            throw InvalidDiscordMessageException::emptyActionRow();
        }

        if (count($this->buttons) > self::MAX_BUTTONS) {
            throw InvalidDiscordMessageException::limitExceeded('action_row.buttons', count($this->buttons), self::MAX_BUTTONS);
        }
    }

    public function toArray(): array
    {
        $this->validate();

        return Serializer::build([
            'type' => 1,
            'components' => $this->buttons,
        ]);
    }
}

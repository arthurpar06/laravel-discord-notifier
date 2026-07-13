<?php

namespace Arthurpar06\DiscordNotifier\Exceptions;

use InvalidArgumentException;

/**
 * Raised at serialization time when a message (or one of its parts) violates a
 * documented Discord limit, or when the payload handed to the channel is not a
 * DiscordMessage.
 */
class InvalidDiscordMessageException extends InvalidArgumentException
{
    public static function limitExceeded(string $field, int $actual, int $max, string $unit = 'items'): self
    {
        return new self("Discord message field [{$field}] has {$actual} {$unit}; the maximum is {$max}.");
    }

    public static function componentsV2Conflict(): self
    {
        return new self(
            'A message with the IS_COMPONENTS_V2 flag cannot also set content or embeds; these fields are mutually exclusive.'
        );
    }

    public static function notAMessage(mixed $value): self
    {
        $type = get_debug_type($value);

        return new self("A notification's toDiscord() must return a DiscordMessage instance, got [{$type}].");
    }
}

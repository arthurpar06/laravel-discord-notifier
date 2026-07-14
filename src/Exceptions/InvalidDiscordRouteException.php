<?php

namespace Arthurpar06\DiscordNotifier\Exceptions;

use InvalidArgumentException;

/**
 * Raised when a route value cannot be resolved to a webhook or bot channel, or
 * when an on-demand notification is routed to Discord with no destination.
 *
 * A notifiable that simply has no route is skipped by the channel rather than
 * raising this, so only the on-demand path can reach missing().
 */
class InvalidDiscordRouteException extends InvalidArgumentException
{
    public static function unresolvable(mixed $value): self
    {
        $type = get_debug_type($value);

        return new self(
            "Cannot resolve a Discord route from [{$type}]. Provide a DiscordRoute, ".
            'a webhook URL (starting with http), or a numeric channel id snowflake.'
        );
    }

    public static function missing(): self
    {
        return new self(
            'No Discord route was provided. Pass a destination to '.
            'Notification::route(\'discord\', ...): a DiscordRoute, a webhook URL, '.
            'or a numeric channel id snowflake.'
        );
    }
}

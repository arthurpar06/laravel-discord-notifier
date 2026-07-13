<?php

namespace Arthurpar06\DiscordNotifier\Exceptions;

use InvalidArgumentException;

/**
 * Raised when a route value cannot be resolved to a webhook or bot channel, or
 * when no route is available for a notification at all.
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
            'No Discord route was provided. Pass one via Notification::route(\'discord\', ...) '.
            'or implement routeNotificationForDiscord() on the notifiable.'
        );
    }
}

<?php

namespace Arthurpar06\DiscordNotifier\Routing;

use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordRouteException;

/**
 * Where a Discord message is delivered. Either an explicit webhook URL or a bot
 * channel id. A bare string route value can be resolved into one of these:
 * an http(s) URL becomes a webhook, a numeric snowflake becomes a bot channel.
 */
final class DiscordRoute
{
    private function __construct(
        private readonly RouteType $type,
        private readonly string $target,
    ) {}

    public static function webhook(string $url): self
    {
        return new self(RouteType::Webhook, $url);
    }

    public static function channel(string $id): self
    {
        return new self(RouteType::Bot, $id);
    }

    /**
     * Normalize any supported route value into a DiscordRoute.
     */
    public static function resolve(mixed $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            if (str_starts_with(strtolower($value), 'http')) {
                return self::webhook($value);
            }

            if (ctype_digit($value)) {
                return self::channel($value);
            }
        }

        throw InvalidDiscordRouteException::unresolvable($value);
    }

    public function type(): RouteType
    {
        return $this->type;
    }

    public function target(): string
    {
        return $this->target;
    }

    public function isWebhook(): bool
    {
        return $this->type === RouteType::Webhook;
    }

    public function isBot(): bool
    {
        return $this->type === RouteType::Bot;
    }
}

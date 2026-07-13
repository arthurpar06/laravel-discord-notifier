<?php

namespace Arthurpar06\DiscordNotifier\Exceptions;

use RuntimeException;

/**
 * Raised when the package is asked to do something it is not configured for,
 * such as a bot delivery with no bot token set.
 */
class DiscordConfigurationException extends RuntimeException
{
    public static function missingBotToken(): self
    {
        return new self(
            'Cannot send via the Discord bot transport: no bot token configured. '.
            'Set DISCORD_BOT_TOKEN in your environment (config discord-notifier.bot.token).'
        );
    }
}

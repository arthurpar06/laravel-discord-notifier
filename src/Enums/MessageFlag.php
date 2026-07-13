<?php

namespace Arthurpar06\DiscordNotifier\Enums;

/**
 * Message flags that can be set when creating a message, as a bitfield.
 *
 * @see https://docs.discord.com/developers/resources/message#message-object-message-flags
 */
enum MessageFlag: int
{
    /** Do not include any embeds when serializing this message (1 << 2). */
    case SuppressEmbeds = 4;

    /** Do not trigger push/desktop notifications for this message (1 << 12). */
    case SuppressNotifications = 4096;

    /** Opt this message into the Components V2 system (1 << 15). */
    case IsComponentsV2 = 32768;

    /**
     * Combine a set of flags into the single integer bitfield Discord expects.
     *
     * @param  array<int, MessageFlag>  $flags
     */
    public static function combine(array $flags): ?int
    {
        if ($flags === []) {
            return null;
        }

        return array_reduce(
            $flags,
            static fn (int $carry, MessageFlag $flag): int => $carry | $flag->value,
            0,
        );
    }
}

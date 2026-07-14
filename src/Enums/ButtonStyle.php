<?php

namespace Arthurpar06\DiscordNotifier\Enums;

/**
 * Discord message button styles.
 *
 * @see https://docs.discord.com/developers/components/reference#button-button-styles
 */
enum ButtonStyle: int
{
    case Primary = 1;
    case Secondary = 2;
    case Success = 3;
    case Danger = 4;
    case Link = 5;
    case Premium = 6;

    /**
     * Interactive styles carry a custom_id and raise an Interaction when clicked;
     * Link and Premium buttons do not.
     */
    public function isInteractive(): bool
    {
        return match ($this) {
            self::Primary, self::Secondary, self::Success, self::Danger => true,
            self::Link, self::Premium => false,
        };
    }
}

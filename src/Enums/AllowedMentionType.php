<?php

namespace Arthurpar06\DiscordNotifier\Enums;

/**
 * Mention categories that Discord will parse from a message's content.
 *
 * @see https://docs.discord.com/developers/resources/message#allowed-mentions-object
 */
enum AllowedMentionType: string
{
    case Roles = 'roles';
    case Users = 'users';
    case Everyone = 'everyone';
}

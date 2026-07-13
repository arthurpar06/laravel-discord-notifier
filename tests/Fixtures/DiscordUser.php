<?php

namespace Arthurpar06\DiscordNotifier\Tests\Fixtures;

use Illuminate\Notifications\RoutesNotifications;

/**
 * A minimal notifiable that routes Discord notifications to its own stored
 * (DM) channel id, mimicking a User model with a discord_private_channel_id.
 */
class DiscordUser
{
    use RoutesNotifications;

    public function __construct(public string $discordChannelId = '111111111111111111') {}

    public function routeNotificationForDiscord(mixed $notification = null): string
    {
        return $this->discordChannelId;
    }
}

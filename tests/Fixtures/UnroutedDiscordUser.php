<?php

namespace Arthurpar06\DiscordNotifier\Tests\Fixtures;

use Illuminate\Notifications\RoutesNotifications;

/**
 * A notifiable that has no Discord destination, mimicking a User model whose
 * owner never linked their Discord account (or has since unlinked it).
 */
class UnroutedDiscordUser
{
    use RoutesNotifications;

    public function routeNotificationForDiscord(mixed $notification = null): ?string
    {
        return null;
    }
}

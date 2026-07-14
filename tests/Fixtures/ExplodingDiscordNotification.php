<?php

namespace Arthurpar06\DiscordNotifier\Tests\Fixtures;

use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;
use Illuminate\Notifications\Notification;
use RuntimeException;

/**
 * Proves the channel does no message-building work for a notifiable it is going
 * to skip: if toDiscord() is reached at all, the test fails loudly.
 */
class ExplodingDiscordNotification extends Notification
{
    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['discord'];
    }

    public function toDiscord(mixed $notifiable): DiscordMessage
    {
        throw new RuntimeException('toDiscord() must not be called when the notifiable has no route.');
    }
}

<?php

namespace Arthurpar06\DiscordNotifier\Tests\Fixtures;

use Illuminate\Notifications\Notification;

/**
 * A stand-in for any second channel a notification might list alongside
 * "discord", recording what it was handed so a test can assert the rest of the
 * via() list still runs when the Discord route is empty.
 */
class RecordingChannel
{
    /**
     * @var array<int, Notification>
     */
    public static array $sent = [];

    public static function reset(): void
    {
        static::$sent = [];
    }

    public function send(mixed $notifiable, Notification $notification): void
    {
        static::$sent[] = $notification;
    }
}

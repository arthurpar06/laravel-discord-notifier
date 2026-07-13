<?php

namespace Arthurpar06\DiscordNotifier\Tests\Fixtures;

use Illuminate\Notifications\Notification;

class NonDiscordMessageNotification extends Notification
{
    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['discord'];
    }

    public function toDiscord(mixed $notifiable): string
    {
        return 'this is not a DiscordMessage';
    }
}

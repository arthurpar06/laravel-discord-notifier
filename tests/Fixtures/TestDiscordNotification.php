<?php

namespace Arthurpar06\DiscordNotifier\Tests\Fixtures;

use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;
use Illuminate\Notifications\Notification;

class TestDiscordNotification extends Notification
{
    public function __construct(protected string $body = 'Hello') {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['discord'];
    }

    public function toDiscord(mixed $notifiable): DiscordMessage
    {
        return DiscordMessage::make()->content($this->body);
    }
}

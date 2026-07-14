<?php

namespace Arthurpar06\DiscordNotifier\Tests\Fixtures;

use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;
use Illuminate\Notifications\Notification;

/**
 * Lists "discord" alongside another channel, so a test can prove that skipping
 * an unrouted notifiable leaves the rest of the via() list alone.
 */
class MultiChannelNotification extends Notification
{
    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['discord', RecordingChannel::class];
    }

    public function toDiscord(mixed $notifiable): DiscordMessage
    {
        return DiscordMessage::make()->content('discord copy');
    }
}

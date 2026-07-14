<?php

namespace Arthurpar06\DiscordNotifier\Notifications;

use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordRouteException;
use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;
use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;
use Arthurpar06\DiscordNotifier\Transport\TransportFactory;
use Illuminate\Notifications\Notification;

/**
 * The Laravel notification channel registered as "discord". Resolves the route
 * for the notifiable (on-demand or via routeNotificationForDiscord), builds the
 * message from the notification, and hands it to the matching transport.
 */
class DiscordChannel
{
    public function __construct(protected TransportFactory $transports = new TransportFactory) {}

    public function send(mixed $notifiable, Notification $notification): void
    {
        $routeValue = is_object($notifiable) && method_exists($notifiable, 'routeNotificationFor')
            ? $notifiable->routeNotificationFor('discord', $notification)
            : null;

        if ($routeValue === null || $routeValue === '' || $routeValue === []) {
            throw InvalidDiscordRouteException::missing();
        }

        $message = method_exists($notification, 'toDiscord')
            ? $notification->toDiscord($notifiable)
            : null;

        if (! $message instanceof DiscordMessage) {
            throw InvalidDiscordMessageException::notAMessage($message);
        }

        $route = DiscordRoute::resolve($routeValue);

        $this->transports->for($route)->send($route, $message->toArray());
    }
}

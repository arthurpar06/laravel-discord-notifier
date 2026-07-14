<?php

namespace Arthurpar06\DiscordNotifier\Notifications;

use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordRouteException;
use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;
use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;
use Arthurpar06\DiscordNotifier\Transport\TransportFactory;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;

/**
 * The Laravel notification channel registered as "discord". Resolves the route
 * for the notifiable (on-demand or via routeNotificationForDiscord), builds the
 * message from the notification, and hands it to the matching transport.
 *
 * A notifiable that returns no route is skipped rather than rejected: having no
 * Discord destination is a normal state for a model whose owner never linked an
 * account, so "discord" can sit in a via() list next to other channels. An
 * on-demand route with no destination is a caller error and still throws, as is
 * any route value that is present but unresolvable.
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
            if ($notifiable instanceof AnonymousNotifiable) {
                throw InvalidDiscordRouteException::missing();
            }

            return;
        }

        $route = DiscordRoute::resolve($routeValue);

        $message = method_exists($notification, 'toDiscord')
            ? $notification->toDiscord($notifiable)
            : null;

        if (! $message instanceof DiscordMessage) {
            throw InvalidDiscordMessageException::notAMessage($message);
        }

        $this->transports->for($route)->send($route, $message->toArray());
    }
}

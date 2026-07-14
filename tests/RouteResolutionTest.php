<?php

use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordRouteException;
use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;
use Arthurpar06\DiscordNotifier\Routing\RouteType;

it('uses a DiscordRoute instance as-is', function () {
    $route = DiscordRoute::webhook('https://discord.com/api/webhooks/1/abc');

    expect(DiscordRoute::resolve($route))->toBe($route);
});

it('resolves an http string to a webhook route', function () {
    $route = DiscordRoute::resolve('https://discord.com/api/webhooks/1/abc');

    expect($route->isWebhook())->toBeTrue()
        ->and($route->target())->toBe('https://discord.com/api/webhooks/1/abc');
});

it('resolves a snowflake string to a bot channel route', function () {
    $route = DiscordRoute::resolve('123456789012345678');

    expect($route->isBot())->toBeTrue()
        ->and($route->target())->toBe('123456789012345678');
});

it('exposes its type via the type() accessor', function () {
    expect(DiscordRoute::channel('123')->type())->toBe(RouteType::Bot)
        ->and(DiscordRoute::webhook('https://discord.com/api/webhooks/1/abc')->type())->toBe(RouteType::Webhook);
});

it('throws on an unresolvable route value', function () {
    DiscordRoute::resolve('not-a-url-or-snowflake');
})->throws(InvalidDiscordRouteException::class);

it('throws on a non-string, non-route value', function () {
    DiscordRoute::resolve(42);
})->throws(InvalidDiscordRouteException::class);

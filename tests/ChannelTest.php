<?php

use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordRouteException;
use Arthurpar06\DiscordNotifier\Notifications\DiscordChannel;
use Arthurpar06\DiscordNotifier\Tests\Fixtures\DiscordUser;
use Arthurpar06\DiscordNotifier\Tests\Fixtures\ExplodingDiscordNotification;
use Arthurpar06\DiscordNotifier\Tests\Fixtures\MultiChannelNotification;
use Arthurpar06\DiscordNotifier\Tests\Fixtures\NonDiscordMessageNotification;
use Arthurpar06\DiscordNotifier\Tests\Fixtures\RecordingChannel;
use Arthurpar06\DiscordNotifier\Tests\Fixtures\TestDiscordNotification;
use Arthurpar06\DiscordNotifier\Tests\Fixtures\UnroutedDiscordUser;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification as NotificationFacade;

beforeEach(function () {
    Http::fake();
    config()->set('discord-notifier.bot.token', 'test-token');
});

it('routes an on-demand notification to a bot channel', function () {
    NotificationFacade::route('discord', '123456789012345678')
        ->notify(new TestDiscordNotification('hi'));

    Http::assertSent(fn ($request) => str_contains($request->url(), '/channels/123456789012345678/messages')
        && $request['content'] === 'hi'
        && $request->hasHeader('Authorization', 'Bot test-token'));
});

it('routes a notifiable to its own discord channel', function () {
    (new DiscordUser('999888777666555444'))->notify(new TestDiscordNotification('yo'));

    Http::assertSent(fn ($request) => str_contains($request->url(), '/channels/999888777666555444/messages')
        && $request['content'] === 'yo');
});

it('routes a bare webhook url to the webhook transport', function () {
    NotificationFacade::route('discord', 'https://discord.com/api/webhooks/1/abc')
        ->notify(new TestDiscordNotification('via hook'));

    Http::assertSent(fn ($request) => $request->url() === 'https://discord.com/api/webhooks/1/abc'
        && $request['content'] === 'via hook');
});

it('rejects a notification whose toDiscord is not a DiscordMessage', function () {
    $notifiable = (new AnonymousNotifiable)->route('discord', '123456789012345678');

    (new DiscordChannel)->send($notifiable, new NonDiscordMessageNotification);
})->throws(InvalidDiscordMessageException::class);

it('throws when an on-demand notification is routed with no destination', function () {
    // Routing on demand is an explicit request to deliver to Discord, so an
    // empty route is a caller error rather than an absent destination.
    (new DiscordChannel)->send(new AnonymousNotifiable, new TestDiscordNotification);
})->throws(InvalidDiscordRouteException::class);

it('skips a notifiable whose discord route is null', function () {
    (new UnroutedDiscordUser)->notify(new TestDiscordNotification);

    Http::assertNothingSent();
});

it('skips a notifiable whose discord route is an empty string', function () {
    (new DiscordUser(''))->notify(new TestDiscordNotification);

    Http::assertNothingSent();
});

it('skips a notifiable that cannot resolve a discord route at all', function () {
    // A plain object exposes no routeNotificationFor(), so no route can be derived.
    (new DiscordChannel)->send(new stdClass, new TestDiscordNotification);

    Http::assertNothingSent();
});

it('builds no message for a notifiable it skips', function () {
    // ExplodingDiscordNotification throws from toDiscord(), so reaching the
    // message-building step at all would surface here.
    (new UnroutedDiscordUser)->notify(new ExplodingDiscordNotification);

    Http::assertNothingSent();
});

it('still delivers the other channels when the discord route is empty', function () {
    RecordingChannel::reset();

    (new UnroutedDiscordUser)->notify(new MultiChannelNotification);

    Http::assertNothingSent();
    expect(RecordingChannel::$sent)->toHaveCount(1)
        ->and(RecordingChannel::$sent[0])->toBeInstanceOf(MultiChannelNotification::class);
});

it('throws when a notifiable returns a malformed route', function () {
    (new DiscordUser('not-a-url'))->notify(new TestDiscordNotification);
})->throws(InvalidDiscordRouteException::class);

it('throws when an on-demand route is malformed', function () {
    NotificationFacade::route('discord', 'not-a-url')
        ->notify(new TestDiscordNotification);
})->throws(InvalidDiscordRouteException::class);

it('throws when the notification has no toDiscord method', function () {
    $notifiable = (new AnonymousNotifiable)->route('discord', '123456789012345678');

    (new DiscordChannel)->send($notifiable, new Notification);
})->throws(InvalidDiscordMessageException::class);

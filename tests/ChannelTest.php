<?php

use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordRouteException;
use Arthurpar06\DiscordNotifier\Notifications\DiscordChannel;
use Arthurpar06\DiscordNotifier\Tests\Fixtures\DiscordUser;
use Arthurpar06\DiscordNotifier\Tests\Fixtures\NonDiscordMessageNotification;
use Arthurpar06\DiscordNotifier\Tests\Fixtures\TestDiscordNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Http::fake();
    config()->set('discord-notifier.bot.token', 'test-token');
});

it('routes an on-demand notification to a bot channel', function () {
    Notification::route('discord', '123456789012345678')
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
    Notification::route('discord', 'https://discord.com/api/webhooks/1/abc')
        ->notify(new TestDiscordNotification('via hook'));

    Http::assertSent(fn ($request) => $request->url() === 'https://discord.com/api/webhooks/1/abc'
        && $request['content'] === 'via hook');
});

it('rejects a notification whose toDiscord is not a DiscordMessage', function () {
    $notifiable = (new AnonymousNotifiable)->route('discord', '123456789012345678');

    (new DiscordChannel)->send($notifiable, new NonDiscordMessageNotification);
})->throws(InvalidDiscordMessageException::class);

it('throws when no route is available', function () {
    (new DiscordChannel)->send(new AnonymousNotifiable, new TestDiscordNotification);
})->throws(InvalidDiscordRouteException::class);

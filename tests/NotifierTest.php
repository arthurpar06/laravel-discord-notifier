<?php

use Arthurpar06\DiscordNotifier\DiscordNotifier;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordRouteException;
use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;
use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake(['*' => Http::response(['id' => '111', 'channel_id' => '222'])]);
    config()->set('discord-notifier.bot.token', 'test-token');
});

it('sends and returns the message id', function () {
    $sent = (new DiscordNotifier)->send(DiscordRoute::channel('222'), DiscordMessage::make()->content('hi'));

    expect($sent->id)->toBe('111')
        ->and($sent->channelId)->toBe('222');

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && str_contains($request->url(), '/channels/222/messages')
        && $request['content'] === 'hi');
});

it('edits a message by id', function () {
    $sent = (new DiscordNotifier)->edit(DiscordRoute::channel('222'), '111', DiscordMessage::make()->content('changed')->tts());

    expect($sent->id)->toBe('111');

    Http::assertSent(fn (Request $request) => $request->method() === 'PATCH'
        && str_contains($request->url(), '/channels/222/messages/111')
        && $request['content'] === 'changed'
        && ! isset($request['tts']));
});

it('resolves string routes', function () {
    $notifier = new DiscordNotifier;

    $notifier->send('222', DiscordMessage::make()->content('a'));
    $notifier->edit('https://discord.com/api/webhooks/1/abc', '111', DiscordMessage::make()->content('b'));

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && str_contains($request->url(), '/channels/222/messages'));
    Http::assertSent(fn (Request $request) => $request->method() === 'PATCH'
        && str_starts_with($request->url(), 'https://discord.com/api/webhooks/1/abc/messages/111'));
});

it('throws on an unresolvable route', function () {
    (new DiscordNotifier)->send('not-a-url', DiscordMessage::make()->content('x'));
})->throws(InvalidDiscordRouteException::class);

it('throws on an unresolvable route when editing', function () {
    (new DiscordNotifier)->edit('not-a-url', '111', DiscordMessage::make()->content('x'));
})->throws(InvalidDiscordRouteException::class);

it('validates the message before sending', function () {
    try {
        (new DiscordNotifier)->send('222', DiscordMessage::make()->content(str_repeat('a', 2001)));
    } finally {
        Http::assertNothingSent();
    }
})->throws(InvalidDiscordMessageException::class);

it('validates the message before editing', function () {
    try {
        (new DiscordNotifier)->edit('222', '111', DiscordMessage::make()->content(str_repeat('a', 2001)));
    } finally {
        Http::assertNothingSent();
    }
})->throws(InvalidDiscordMessageException::class);

it('is resolvable from the container', function () {
    expect(app(DiscordNotifier::class))->toBeInstanceOf(DiscordNotifier::class);
});

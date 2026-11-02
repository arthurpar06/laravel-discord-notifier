<?php

use Arthurpar06\DiscordNotifier\Exceptions\DiscordConfigurationException;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;
use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;
use Arthurpar06\DiscordNotifier\Messages\SentMessage;
use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;
use Arthurpar06\DiscordNotifier\Transport\BotTransport;
use Arthurpar06\DiscordNotifier\Transport\WebhookTransport;
use Illuminate\Support\Facades\Http;

it('posts the body to the webhook url', function () {
    Http::fake(['*' => Http::response(['id' => '111', 'channel_id' => '222'])]);

    (new WebhookTransport)->send(
        DiscordRoute::webhook('https://discord.com/api/webhooks/1/abc'),
        ['content' => 'x'],
    );

    Http::assertSent(fn ($request) => $request->url() === 'https://discord.com/api/webhooks/1/abc?wait=true'
        && $request['content'] === 'x');
});

it('posts to the channel messages endpoint with a bot token', function () {
    Http::fake(['*' => Http::response(['id' => '111', 'channel_id' => '222'])]);

    (new BotTransport('secret', 'https://discord.com/api/v10'))->send(
        DiscordRoute::channel('123456789012345678'),
        ['content' => 'x'],
    );

    Http::assertSent(fn ($request) => $request->url() === 'https://discord.com/api/v10/channels/123456789012345678/messages'
        && $request->hasHeader('Authorization', 'Bot secret')
        && $request['content'] === 'x');
});

it('throws when the bot token is not configured', function () {
    (new BotTransport(null, 'https://discord.com/api/v10'))->send(
        DiscordRoute::channel('123456789012345678'),
        [],
    );
})->throws(DiscordConfigurationException::class);

it('returns the sent message from a bot send', function () {
    Http::fake(['*' => Http::response(['id' => '111', 'channel_id' => '222'])]);

    $sent = (new BotTransport('secret', 'https://discord.com/api/v10'))
        ->send(DiscordRoute::channel('123456789012345678'), ['content' => 'x']);

    expect($sent)->toBeInstanceOf(SentMessage::class)
        ->and($sent->id)->toBe('111')
        ->and($sent->channelId)->toBe('222');
});

it('returns the sent message from a webhook send', function () {
    Http::fake(['*' => Http::response(['id' => '111', 'channel_id' => '222'])]);

    $sent = (new WebhookTransport)->send(DiscordRoute::webhook('https://discord.com/api/webhooks/1/abc'), []);

    expect($sent->id)->toBe('111')
        ->and($sent->channelId)->toBe('222');
});

it('executes webhooks with wait=true', function () {
    Http::fake(['*' => Http::response(['id' => '111', 'channel_id' => '222'])]);

    (new WebhookTransport)->send(DiscordRoute::webhook('https://discord.com/api/webhooks/1/abc'), []);

    Http::assertSent(fn ($request) => $request->url() === 'https://discord.com/api/webhooks/1/abc?wait=true');
});

it('keeps an existing webhook query string', function () {
    Http::fake(['*' => Http::response(['id' => '111', 'channel_id' => '222'])]);
    $route = DiscordRoute::webhook('https://discord.com/api/webhooks/1/abc?thread_id=9');

    (new WebhookTransport)->send($route, []);
    (new WebhookTransport)->edit($route, '111', []);

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && $request->url() === 'https://discord.com/api/webhooks/1/abc?thread_id=9&wait=true');
    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && $request->url() === 'https://discord.com/api/webhooks/1/abc/messages/111?thread_id=9');
});

it('patches a bot message', function () {
    Http::fake(['*' => Http::response(['id' => '111', 'channel_id' => '222'])]);

    $sent = (new BotTransport('secret', 'https://discord.com/api/v10/'))
        ->edit(DiscordRoute::channel('123456789012345678'), '111', ['content' => 'y']);

    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && $request->url() === 'https://discord.com/api/v10/channels/123456789012345678/messages/111'
        && $request->hasHeader('Authorization', 'Bot secret')
        && $request['content'] === 'y');
    expect($sent->id)->toBe('111')->and($sent->channelId)->toBe('222');
});

it('patches a webhook message', function () {
    Http::fake(['*' => Http::response(['id' => '111', 'channel_id' => '222'])]);

    $sent = (new WebhookTransport)->edit(DiscordRoute::webhook('https://discord.com/api/webhooks/1/abc'), '111', ['content' => 'y']);

    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && $request->url() === 'https://discord.com/api/webhooks/1/abc/messages/111'
        && $request['content'] === 'y');
    expect($sent->id)->toBe('111');
});

it('throws when the bot token is missing on edit', function () {
    (new BotTransport(null, 'https://discord.com/api/v10'))
        ->edit(DiscordRoute::channel('123456789012345678'), '111', []);
})->throws(DiscordConfigurationException::class);

it('throws when the bot token is empty on edit', function () {
    (new BotTransport('', 'https://discord.com/api/v10'))
        ->edit(DiscordRoute::channel('123456789012345678'), '111', []);
})->throws(DiscordConfigurationException::class);

it('drops tts from edit payloads', function () {
    $message = DiscordMessage::make()->content('x')->tts();

    expect($message->toArray())->toHaveKey('tts')
        ->and($message->toEditArray())->not->toHaveKey('tts')
        ->and($message->toEditArray()['content'])->toBe('x');
});

it('rejects a response without an id', function () {
    expect(fn () => SentMessage::fromResponse(['channel_id' => '222']))
        ->toThrow(InvalidDiscordMessageException::class);
});

it('rejects a response without a channel id', function () {
    expect(fn () => SentMessage::fromResponse(['id' => '111']))
        ->toThrow(InvalidDiscordMessageException::class);
});

it('rejects non-string ids in a response', function () {
    expect(fn () => SentMessage::fromResponse(['id' => ['x'], 'channel_id' => '222']))
        ->toThrow(InvalidDiscordMessageException::class);
});

it('builds a sent message from a response', function () {
    $sent = SentMessage::fromResponse(['id' => '111', 'channel_id' => '222', 'extra' => 1]);

    expect($sent->id)->toBe('111')->and($sent->channelId)->toBe('222');
});

it('builds the exact invalid response message', function () {
    expect(InvalidDiscordMessageException::invalidResponse('id')->getMessage())
        ->toBe('The Discord response did not contain a message [id].');
});

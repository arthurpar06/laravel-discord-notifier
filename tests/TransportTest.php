<?php

use Arthurpar06\DiscordNotifier\Exceptions\DiscordConfigurationException;
use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;
use Arthurpar06\DiscordNotifier\Transport\BotTransport;
use Arthurpar06\DiscordNotifier\Transport\WebhookTransport;
use Illuminate\Support\Facades\Http;

it('posts the body to the webhook url', function () {
    Http::fake();

    (new WebhookTransport)->send(
        DiscordRoute::webhook('https://discord.com/api/webhooks/1/abc'),
        ['content' => 'x'],
    );

    Http::assertSent(fn ($request) => $request->url() === 'https://discord.com/api/webhooks/1/abc'
        && $request['content'] === 'x');
});

it('posts to the channel messages endpoint with a bot token', function () {
    Http::fake();

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

<?php

use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbed;
use Arthurpar06\DiscordNotifier\Enums\AllowedMentionType;
use Arthurpar06\DiscordNotifier\Enums\DiscordColor;
use Arthurpar06\DiscordNotifier\Enums\MessageFlag;
use Arthurpar06\DiscordNotifier\Messages\AllowedMentions;
use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;

it('emits only fields that were set', function () {
    $body = DiscordMessage::make()->content('Hello')->toArray();

    expect($body)->toBe(['content' => 'Hello']);
});

it('serializes embeds with their sub-objects', function () {
    $embed = DiscordEmbed::make()
        ->title('Bonjour')
        ->description('A description')
        ->color(DiscordColor::Green)
        ->footer('the footer')
        ->author('the author')
        ->field('Name', 'Value', inline: true);

    $body = DiscordMessage::make()->embed($embed)->toArray();

    expect($body)->toBe([
        'embeds' => [[
            'title' => 'Bonjour',
            'description' => 'A description',
            'color' => 0x57F287,
            'footer' => ['text' => 'the footer'],
            'author' => ['name' => 'the author'],
            'fields' => [[
                'name' => 'Name',
                'value' => 'Value',
                'inline' => true,
            ]],
        ]],
    ]);
});

it('coerces enums to their scalar values', function () {
    $body = DiscordMessage::make()
        ->content('x')
        ->flags(MessageFlag::SuppressEmbeds, MessageFlag::SuppressNotifications)
        ->toArray();

    // 1<<2 | 1<<12 = 4 | 4096 = 4100
    expect($body['flags'])->toBe(4100);
});

it('serializes allowed_mentions as an object', function () {
    $mentions = AllowedMentions::make()
        ->parse([AllowedMentionType::Users])
        ->users(['123'])
        ->repliedUser(false);

    $body = DiscordMessage::make()->content('x')->allowedMentions($mentions)->toArray();

    expect($body['allowed_mentions'])->toBe([
        'parse' => ['users'],
        'users' => ['123'],
        'replied_user' => false,
    ]);
});

it('serializes role mentions', function () {
    $mentions = AllowedMentions::make()
        ->parse([AllowedMentionType::Roles])
        ->roles(['555', '666']);

    $body = DiscordMessage::make()->content('x')->allowedMentions($mentions)->toArray();

    expect($body['allowed_mentions'])->toBe([
        'parse' => ['roles'],
        'roles' => ['555', '666'],
    ]);
});

it('drops nulls but keeps explicit false and empty arrays', function () {
    $body = DiscordMessage::make()
        ->content('x')
        ->tts(false)
        ->allowedMentions(AllowedMentions::none())
        ->toArray();

    expect($body)->toBe([
        'content' => 'x',
        'tts' => false,
        'allowed_mentions' => ['parse' => []],
    ]);
});

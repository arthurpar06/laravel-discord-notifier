<?php

use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbed;
use Arthurpar06\DiscordNotifier\Enums\MessageFlag;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;
use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;

it('rejects more than ten embeds', function () {
    $embeds = array_fill(0, 11, DiscordEmbed::make()->title('x'));

    DiscordMessage::make()->embeds($embeds)->toArray();
})->throws(InvalidDiscordMessageException::class, 'embeds');

it('rejects content longer than 2000 characters', function () {
    DiscordMessage::make()->content(str_repeat('a', 2001))->toArray();
})->throws(InvalidDiscordMessageException::class, 'content');

it('rejects an embed field value over its limit', function () {
    $embed = DiscordEmbed::make()->field('name', str_repeat('a', 1025));

    DiscordMessage::make()->embed($embed)->toArray();
})->throws(InvalidDiscordMessageException::class, 'embed.field.value');

it('rejects the components v2 flag together with content', function () {
    DiscordMessage::make()
        ->content('hello')
        ->flag(MessageFlag::IsComponentsV2)
        ->toArray();
})->throws(InvalidDiscordMessageException::class, 'mutually exclusive');

it('rejects the components v2 flag together with embeds', function () {
    DiscordMessage::make()
        ->embed(DiscordEmbed::make()->title('x'))
        ->flag(MessageFlag::IsComponentsV2)
        ->toArray();
})->throws(InvalidDiscordMessageException::class, 'mutually exclusive');

it('passes a message within all limits', function () {
    $body = DiscordMessage::make()
        ->content(str_repeat('a', 2000))
        ->embeds(array_fill(0, 10, DiscordEmbed::make()->title('x')))
        ->toArray();

    expect($body['content'])->toHaveLength(2000)
        ->and($body['embeds'])->toHaveCount(10);
});

<?php

use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbed;
use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbedAuthor;
use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbedField;
use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbedFooter;
use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbedImage;
use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbedThumbnail;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;

it('serializes every embed property including url, timestamp, image and thumbnail', function () {
    $embed = DiscordEmbed::make()
        ->title('Title')
        ->description('Desc')
        ->url('https://example.com')
        ->timestamp('2026-07-14T00:00:00+00:00')
        ->image('https://example.com/image.png')
        ->thumbnail('https://example.com/thumb.png');

    expect($embed->toArray())->toBe([
        'title' => 'Title',
        'description' => 'Desc',
        'url' => 'https://example.com',
        'timestamp' => '2026-07-14T00:00:00+00:00',
        'image' => ['url' => 'https://example.com/image.png'],
        'thumbnail' => ['url' => 'https://example.com/thumb.png'],
    ]);
});

it('formats a DateTimeInterface timestamp as an ATOM string', function () {
    $date = new DateTimeImmutable('2026-07-14 12:34:56', new DateTimeZone('UTC'));

    $embed = DiscordEmbed::make()->timestamp($date);

    expect($embed->toArray()['timestamp'])->toBe('2026-07-14T12:34:56+00:00');
});

it('accepts footer, author, image and thumbnail as value objects', function () {
    $embed = DiscordEmbed::make()
        ->footer(DiscordEmbedFooter::make('footer text')->iconUrl('https://example.com/f.png'))
        ->author(DiscordEmbedAuthor::make('the author')->url('https://example.com/a')->iconUrl('https://example.com/a.png'))
        ->image(DiscordEmbedImage::make('https://example.com/i.png'))
        ->thumbnail(DiscordEmbedThumbnail::make('https://example.com/t.png'));

    expect($embed->toArray())->toBe([
        'footer' => ['text' => 'footer text', 'icon_url' => 'https://example.com/f.png'],
        'author' => [
            'name' => 'the author',
            'url' => 'https://example.com/a',
            'icon_url' => 'https://example.com/a.png',
        ],
        'image' => ['url' => 'https://example.com/i.png'],
        'thumbnail' => ['url' => 'https://example.com/t.png'],
    ]);
});

it('replaces fields wholesale via the fields() setter', function () {
    $embed = DiscordEmbed::make()
        ->field('will be replaced', 'x')
        ->fields([
            DiscordEmbedField::make('A', '1'),
            DiscordEmbedField::make('B', '2')->inline(),
        ]);

    expect($embed->toArray()['fields'])->toBe([
        ['name' => 'A', 'value' => '1'],
        ['name' => 'B', 'value' => '2', 'inline' => true],
    ]);
});

it('computes the combined text length across title, description, footer, author and fields', function () {
    $embed = DiscordEmbed::make()
        ->title('ab')          // 2
        ->description('cde')    // 3
        ->footer('fg')          // 2
        ->author('hij')         // 3
        ->field('kl', 'mno');   // 2 + 3

    expect($embed->totalLength())->toBe(15);
});

it('rejects an embed title over its limit', function () {
    DiscordEmbed::make()->title(str_repeat('a', DiscordEmbed::MAX_TITLE + 1))->toArray();
})->throws(InvalidDiscordMessageException::class, 'embed.title');

it('rejects an embed description over its limit', function () {
    DiscordEmbed::make()->description(str_repeat('a', DiscordEmbed::MAX_DESCRIPTION + 1))->toArray();
})->throws(InvalidDiscordMessageException::class, 'embed.description');

it('rejects more than twenty-five fields', function () {
    $embed = DiscordEmbed::make()->fields(
        array_fill(0, DiscordEmbed::MAX_FIELDS + 1, DiscordEmbedField::make('n', 'v'))
    );

    $embed->toArray();
})->throws(InvalidDiscordMessageException::class, 'embed.fields');

it('rejects footer text over its limit', function () {
    DiscordEmbed::make()->footer(str_repeat('a', DiscordEmbed::MAX_FOOTER_TEXT + 1))->toArray();
})->throws(InvalidDiscordMessageException::class, 'embed.footer.text');

it('rejects an author name over its limit', function () {
    DiscordEmbed::make()->author(str_repeat('a', DiscordEmbed::MAX_AUTHOR_NAME + 1))->toArray();
})->throws(InvalidDiscordMessageException::class, 'embed.author.name');

it('rejects an embed whose combined text exceeds the total limit', function () {
    $embed = DiscordEmbed::make()
        ->description(str_repeat('a', DiscordEmbed::MAX_DESCRIPTION))
        ->fields([DiscordEmbedField::make('n', str_repeat('b', DiscordEmbed::MAX_TOTAL))]);

    $embed->toArray();
})->throws(InvalidDiscordMessageException::class, 'combined text');

it('rejects an embed field name over its limit', function () {
    DiscordEmbedField::make(str_repeat('a', DiscordEmbedField::MAX_NAME + 1), 'v')->toArray();
})->throws(InvalidDiscordMessageException::class, 'embed.field.name');

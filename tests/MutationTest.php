<?php

use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbed;
use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbedField;
use Arthurpar06\DiscordNotifier\Enums\AllowedMentionType;
use Arthurpar06\DiscordNotifier\Enums\DiscordColor;
use Arthurpar06\DiscordNotifier\Enums\MessageFlag;
use Arthurpar06\DiscordNotifier\Exceptions\DiscordConfigurationException;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordRouteException;
use Arthurpar06\DiscordNotifier\Messages\AllowedMentions;
use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;
use Arthurpar06\DiscordNotifier\Notifications\DiscordChannel;
use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;
use Arthurpar06\DiscordNotifier\Tests\Fixtures\TestDiscordNotification;
use Arthurpar06\DiscordNotifier\Transport\BotTransport;
use Illuminate\Support\Facades\Http;

/*
 * Behaviour-pinning tests aimed at mutation survivors: exact enum values, limit
 * boundaries (> vs >=), default argument values, array re-indexing, string
 * normalisation and exact exception messages.
 */

// --- Enum values -------------------------------------------------------------

it('pins every DiscordColor to its exact hex value', function () {
    expect([
        DiscordColor::Blurple->value,
        DiscordColor::Green->value,
        DiscordColor::Yellow->value,
        DiscordColor::Red->value,
        DiscordColor::White->value,
        DiscordColor::Black->value,
        DiscordColor::Greyple->value,
        DiscordColor::DarkButNotBlack->value,
    ])->toBe([0x5865F2, 0x57F287, 0xFEE75C, 0xED4245, 0xFFFFFF, 0x000000, 0x99AAB5, 0x2C2F33]);
});

it('pins every MessageFlag to its exact bitfield value', function () {
    expect([
        MessageFlag::SuppressEmbeds->value,
        MessageFlag::SuppressNotifications->value,
        MessageFlag::IsComponentsV2->value,
    ])->toBe([4, 4096, 32768]);

    expect(MessageFlag::combine([MessageFlag::SuppressEmbeds, MessageFlag::IsComponentsV2]))
        ->toBe(4 | 32768);
});

// --- Default argument values -------------------------------------------------

it('defaults tts() to true when called with no argument', function () {
    expect(DiscordMessage::make()->content('x')->tts()->toArray()['tts'])->toBeTrue();
});

it('defaults repliedUser() to true when called with no argument', function () {
    $body = AllowedMentions::make()->repliedUser()->toArray();

    expect($body['replied_user'])->toBeTrue();
});

it('defaults a field to non-inline', function () {
    $body = DiscordEmbed::make()->field('name', 'value')->toArray();

    expect($body['fields'][0])->toBe(['name' => 'name', 'value' => 'value', 'inline' => false]);
});

// --- Array re-indexing (array_values) ---------------------------------------

it('re-indexes embeds passed with non-sequential keys', function () {
    $body = DiscordMessage::make()
        ->embeds([5 => DiscordEmbed::make()->title('a'), 9 => DiscordEmbed::make()->title('b')])
        ->toArray();

    expect(array_is_list($body['embeds']))->toBeTrue()
        ->and($body['embeds'])->toHaveCount(2);
});

it('re-indexes embed fields passed with non-sequential keys', function () {
    $body = DiscordEmbed::make()
        ->fields([3 => DiscordEmbedField::make('a', '1'), 8 => DiscordEmbedField::make('b', '2')])
        ->toArray();

    expect(array_is_list($body['fields']))->toBeTrue();
});

it('re-indexes allowed-mention parse, roles and users with non-sequential keys', function () {
    $body = AllowedMentions::make()
        ->parse([2 => AllowedMentionType::Users, 5 => AllowedMentionType::Roles])
        ->roles([4 => '111'])
        ->users([7 => '222'])
        ->toArray();

    expect(array_is_list($body['parse']))->toBeTrue()
        ->and(array_is_list($body['roles']))->toBeTrue()
        ->and(array_is_list($body['users']))->toBeTrue();
});

// --- Embed limit boundaries (> vs >=) ---------------------------------------

it('accepts embed text exactly at each limit', function () {
    expect(fn () => DiscordEmbed::make()->title(str_repeat('a', DiscordEmbed::MAX_TITLE))->toArray())
        ->not->toThrow(Exception::class);

    expect(fn () => DiscordEmbed::make()->description(str_repeat('a', DiscordEmbed::MAX_DESCRIPTION))->toArray())
        ->not->toThrow(Exception::class);

    expect(fn () => DiscordEmbed::make()->footer(str_repeat('a', DiscordEmbed::MAX_FOOTER_TEXT))->toArray())
        ->not->toThrow(Exception::class);

    expect(fn () => DiscordEmbed::make()->author(str_repeat('a', DiscordEmbed::MAX_AUTHOR_NAME))->toArray())
        ->not->toThrow(Exception::class);

    expect(fn () => DiscordEmbed::make()->fields(array_fill(0, DiscordEmbed::MAX_FIELDS, DiscordEmbedField::make('n', 'v')))->toArray())
        ->not->toThrow(Exception::class);
});

it('accepts an embed whose combined text is exactly at the total limit', function () {
    // 256 + 4096 + 1648 = 6000, each part within its own limit.
    $embed = DiscordEmbed::make()
        ->title(str_repeat('a', DiscordEmbed::MAX_TITLE))
        ->description(str_repeat('b', DiscordEmbed::MAX_DESCRIPTION))
        ->footer(str_repeat('c', DiscordEmbed::MAX_TOTAL - DiscordEmbed::MAX_TITLE - DiscordEmbed::MAX_DESCRIPTION));

    expect($embed->totalLength())->toBe(DiscordEmbed::MAX_TOTAL)
        ->and(fn () => $embed->toArray())->not->toThrow(Exception::class);
});

it('accepts an embed field exactly at its name and value limits', function () {
    expect(fn () => DiscordEmbedField::make(str_repeat('a', DiscordEmbedField::MAX_NAME), 'v')->toArray())
        ->not->toThrow(Exception::class);

    expect(fn () => DiscordEmbedField::make('n', str_repeat('a', DiscordEmbedField::MAX_VALUE))->toArray())
        ->not->toThrow(Exception::class);
});

// --- Combined length arithmetic (the `: 0` branches) ------------------------

it('counts zero for an absent footer and author in the combined length', function () {
    $embed = DiscordEmbed::make()
        ->title('ab')          // 2
        ->description('cde')    // 3
        ->field('kl', 'mno');   // 2 + 3, no footer/author

    expect($embed->totalLength())->toBe(10);
});

// --- Color coercion (instanceof) --------------------------------------------

it('keeps a raw integer color untouched', function () {
    expect(DiscordEmbed::make()->color(0x123456)->toArray()['color'])->toBe(0x123456);
});

// --- Route normalisation -----------------------------------------------------

it('lowercases the scheme before matching a webhook url', function () {
    $route = DiscordRoute::resolve('HTTPS://discord.com/api/webhooks/1/abc');

    expect($route->isWebhook())->toBeTrue();
});

it('rejects an empty string route value', function () {
    DiscordRoute::resolve('');
})->throws(InvalidDiscordRouteException::class);

// --- Channel empty-route guard ----------------------------------------------

dataset('empty routes', [
    'null' => [null],
    'empty string' => [''],
    'empty array' => [[]],
]);

it('reports a missing route for every empty route value', function (mixed $value) {
    $notifiable = new class($value)
    {
        public function __construct(private mixed $value) {}

        public function routeNotificationFor(string $driver, mixed $notification = null): mixed
        {
            return $this->value;
        }
    };

    expect(fn () => (new DiscordChannel)->send($notifiable, new TestDiscordNotification))
        ->toThrow(InvalidDiscordRouteException::class, 'No Discord route was provided');
})->with('empty routes');

// --- Exact exception messages (concat mutants) ------------------------------

it('builds the exact unresolvable-route message', function () {
    expect(InvalidDiscordRouteException::unresolvable(42)->getMessage())->toBe(
        'Cannot resolve a Discord route from [int]. Provide a DiscordRoute, '.
        'a webhook URL (starting with http), or a numeric channel id snowflake.'
    );
});

it('builds the exact missing-route message', function () {
    expect(InvalidDiscordRouteException::missing()->getMessage())->toBe(
        'No Discord route was provided. Pass one via Notification::route(\'discord\', ...) '.
        'or implement routeNotificationForDiscord() on the notifiable.'
    );
});

it('builds the exact missing-bot-token message', function () {
    expect(DiscordConfigurationException::missingBotToken()->getMessage())->toBe(
        'Cannot send via the Discord bot transport: no bot token configured. '.
        'Set DISCORD_BOT_TOKEN in your environment (config discord-notifier.bot.token).'
    );
});

// --- Bot transport -----------------------------------------------------------

it('treats an empty bot token as missing configuration', function () {
    (new BotTransport('', 'https://discord.com/api/v10'))
        ->send(DiscordRoute::channel('123'), []);
})->throws(DiscordConfigurationException::class);

it('trims a trailing slash from the api base before building the endpoint', function () {
    Http::fake();

    (new BotTransport('secret', 'https://discord.com/api/v10/'))
        ->send(DiscordRoute::channel('123'), ['content' => 'x']);

    Http::assertSent(fn ($request) => $request->url() === 'https://discord.com/api/v10/channels/123/messages');
});

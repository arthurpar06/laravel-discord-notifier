<?php

use Arthurpar06\DiscordNotifier\Components\ActionRow;
use Arthurpar06\DiscordNotifier\Components\Button;
use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbed;
use Arthurpar06\DiscordNotifier\Enums\ButtonStyle;
use Arthurpar06\DiscordNotifier\Enums\MessageFlag;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;
use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;

// --- Button style enum -------------------------------------------------------

it('pins every ButtonStyle to its exact wire value', function () {
    expect([
        ButtonStyle::Primary->value,
        ButtonStyle::Secondary->value,
        ButtonStyle::Success->value,
        ButtonStyle::Danger->value,
        ButtonStyle::Link->value,
        ButtonStyle::Premium->value,
    ])->toBe([1, 2, 3, 4, 5, 6]);
});

it('marks only the four interactive styles as interactive', function () {
    expect(ButtonStyle::Primary->isInteractive())->toBeTrue()
        ->and(ButtonStyle::Secondary->isInteractive())->toBeTrue()
        ->and(ButtonStyle::Success->isInteractive())->toBeTrue()
        ->and(ButtonStyle::Danger->isInteractive())->toBeTrue()
        ->and(ButtonStyle::Link->isInteractive())->toBeFalse()
        ->and(ButtonStyle::Premium->isInteractive())->toBeFalse();
});

// --- Button construction & serialization ------------------------------------

it('serializes a link button with a label', function () {
    expect(Button::link('https://example.com', 'View')->toArray())->toBe([
        'type' => 2,
        'style' => 5,
        'label' => 'View',
        'url' => 'https://example.com',
    ]);
});

it('serializes a link button without a label', function () {
    expect(Button::link('https://example.com')->toArray())->toBe([
        'type' => 2,
        'style' => 5,
        'url' => 'https://example.com',
    ]);
});

it('serializes each interactive style with its custom id and label', function () {
    expect(Button::primary('c1', 'P')->toArray())->toBe(['type' => 2, 'style' => 1, 'label' => 'P', 'custom_id' => 'c1'])
        ->and(Button::secondary('c2', 'S')->toArray())->toBe(['type' => 2, 'style' => 2, 'label' => 'S', 'custom_id' => 'c2'])
        ->and(Button::success('c3', 'G')->toArray())->toBe(['type' => 2, 'style' => 3, 'label' => 'G', 'custom_id' => 'c3'])
        ->and(Button::danger('c4', 'D')->toArray())->toBe(['type' => 2, 'style' => 4, 'label' => 'D', 'custom_id' => 'c4']);
});

it('exposes its style via the style() accessor', function () {
    expect(Button::primary('c', 'x')->style())->toBe(ButtonStyle::Primary)
        ->and(Button::link('https://e.com')->style())->toBe(ButtonStyle::Link);
});

it('serializes a premium button with only its sku id', function () {
    expect(Button::premium('123456789012345678')->toArray())->toBe([
        'type' => 2,
        'style' => 6,
        'sku_id' => '123456789012345678',
    ]);
});

it('serializes the disabled flag when set and defaults it to true', function () {
    expect(Button::primary('c', 'x')->disabled()->toArray()['disabled'])->toBeTrue();
    expect(Button::primary('c', 'x')->disabled(false)->toArray()['disabled'])->toBeFalse();
    expect(Button::primary('c', 'x')->toArray())->not->toHaveKey('disabled');
});

it('serializes a unicode emoji as a name object', function () {
    expect(Button::primary('c', 'x')->emoji('🔥')->toArray()['emoji'])->toBe(['name' => '🔥']);
});

it('serializes a custom emoji object as-is', function () {
    $emoji = ['id' => '123', 'name' => 'blob', 'animated' => true];

    expect(Button::link('https://e.com')->emoji($emoji)->toArray()['emoji'])->toBe($emoji);
});

it('omits the emoji key when none is set', function () {
    expect(Button::primary('c', 'x')->toArray())->not->toHaveKey('emoji');
});

// --- Button validation -------------------------------------------------------

it('accepts a label exactly at the limit and rejects one over it', function () {
    expect(fn () => Button::primary('c', str_repeat('a', Button::MAX_LABEL))->toArray())->not->toThrow(Exception::class);

    expect(fn () => Button::primary('c', str_repeat('a', Button::MAX_LABEL + 1))->toArray())
        ->toThrow(InvalidDiscordMessageException::class, 'button.label');
});

it('accepts a custom id exactly at the limit and rejects one over it', function () {
    expect(fn () => Button::primary(str_repeat('a', Button::MAX_CUSTOM_ID), 'x')->toArray())->not->toThrow(Exception::class);

    expect(fn () => Button::primary(str_repeat('a', Button::MAX_CUSTOM_ID + 1), 'x')->toArray())
        ->toThrow(InvalidDiscordMessageException::class, 'button.custom_id');
});

it('rejects an emoji on a premium button', function () {
    Button::premium('123')->emoji('🔥')->toArray();
})->throws(InvalidDiscordMessageException::class, 'premium button cannot set an emoji');

it('allows a long custom id on a link button (no custom id constraint)', function () {
    // Link buttons carry a url, not a custom_id, so the custom_id limit never applies.
    expect(fn () => Button::link('https://example.com/'.str_repeat('a', 200))->toArray())->not->toThrow(Exception::class);
});

// --- Action rows -------------------------------------------------------------

it('serializes an action row wrapping its buttons', function () {
    $row = ActionRow::make(Button::link('https://a.com', 'A'), Button::primary('b', 'B'));

    expect($row->toArray())->toBe([
        'type' => 1,
        'components' => [
            ['type' => 2, 'style' => 5, 'label' => 'A', 'url' => 'https://a.com'],
            ['type' => 2, 'style' => 1, 'label' => 'B', 'custom_id' => 'b'],
        ],
    ]);
});

it('supports adding buttons to a row fluently', function () {
    $row = ActionRow::make()->button(Button::link('https://a.com', 'A'));

    expect($row->toArray()['components'])->toHaveCount(1);
});

it('rejects an empty action row', function () {
    ActionRow::make()->toArray();
})->throws(InvalidDiscordMessageException::class, 'at least one button');

it('accepts five buttons in a row and rejects a sixth', function () {
    $five = array_fill(0, ActionRow::MAX_BUTTONS, Button::link('https://a.com', 'x'));
    expect(fn () => ActionRow::make(...$five)->toArray())->not->toThrow(Exception::class);

    $six = array_fill(0, ActionRow::MAX_BUTTONS + 1, Button::link('https://a.com', 'x'));
    expect(fn () => ActionRow::make(...$six)->toArray())
        ->toThrow(InvalidDiscordMessageException::class, 'action_row.buttons');
});

// --- Messages carrying components -------------------------------------------

it('attaches a single button wrapped in its own row', function () {
    $body = DiscordMessage::make()
        ->content('Deploy done')
        ->button(Button::link('https://ci.example.com', 'View build'))
        ->toArray();

    expect($body['content'])->toBe('Deploy done')
        ->and($body['components'])->toBe([[
            'type' => 1,
            'components' => [[
                'type' => 2,
                'style' => 5,
                'label' => 'View build',
                'url' => 'https://ci.example.com',
            ]],
        ]]);
});

it('attaches an action row of several buttons alongside an embed', function () {
    $body = DiscordMessage::make()
        ->embed(DiscordEmbed::make()->title('Alert'))
        ->actionRow(Button::danger('ack', 'Acknowledge'), Button::link('https://docs.example.com', 'Docs'))
        ->toArray();

    expect($body['embeds'])->toHaveCount(1)
        ->and($body['components'][0]['components'])->toHaveCount(2);
});

it('omits the components key when no components are set', function () {
    expect(DiscordMessage::make()->content('x')->toArray())->not->toHaveKey('components');
});

it('accepts five action rows and rejects a sixth', function () {
    $message = DiscordMessage::make();
    for ($i = 0; $i < DiscordMessage::MAX_ACTION_ROWS; $i++) {
        $message->button(Button::link('https://a.com', 'x'));
    }
    expect(fn () => $message->toArray())->not->toThrow(Exception::class);

    $message->button(Button::link('https://a.com', 'x'));
    expect(fn () => $message->toArray())
        ->toThrow(InvalidDiscordMessageException::class, 'components');
});

it('rejects mixing the components v2 flag with classic action rows', function () {
    DiscordMessage::make()
        ->flag(MessageFlag::IsComponentsV2)
        ->button(Button::link('https://a.com', 'x'))
        ->toArray();
})->throws(InvalidDiscordMessageException::class, 'mutually exclusive');

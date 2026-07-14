<?php

namespace Arthurpar06\DiscordNotifier\Components;

use Arthurpar06\DiscordNotifier\Contracts\Arrayable;
use Arthurpar06\DiscordNotifier\Enums\ButtonStyle;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;
use Arthurpar06\DiscordNotifier\Support\Serializer;

/**
 * A Discord message button (component type 2). Built through a named
 * constructor per style so an invalid field combination cannot be assembled:
 * interactive styles carry a custom_id, Link carries a url, Premium carries an
 * sku_id.
 *
 * @see https://docs.discord.com/developers/components/reference#button
 */
class Button implements Arrayable
{
    public const MAX_LABEL = 80;

    public const MAX_CUSTOM_ID = 100;

    protected ?string $customId = null;

    protected ?string $url = null;

    protected ?string $skuId = null;

    protected ?bool $disabled = null;

    /** @var array<string, mixed>|null */
    protected ?array $emoji = null;

    private function __construct(
        protected readonly ButtonStyle $style,
        protected ?string $label = null,
    ) {}

    public static function primary(string $customId, string $label): self
    {
        return self::interactive(ButtonStyle::Primary, $customId, $label);
    }

    public static function secondary(string $customId, string $label): self
    {
        return self::interactive(ButtonStyle::Secondary, $customId, $label);
    }

    public static function success(string $customId, string $label): self
    {
        return self::interactive(ButtonStyle::Success, $customId, $label);
    }

    public static function danger(string $customId, string $label): self
    {
        return self::interactive(ButtonStyle::Danger, $customId, $label);
    }

    public static function link(string $url, ?string $label = null): self
    {
        $button = new self(ButtonStyle::Link, $label);
        $button->url = $url;

        return $button;
    }

    public static function premium(string $skuId): self
    {
        $button = new self(ButtonStyle::Premium);
        $button->skuId = $skuId;

        return $button;
    }

    public function disabled(bool $disabled = true): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Attach an emoji. A unicode string becomes `{name}`; a custom emoji is
     * given as `['id' => ..., 'name' => ..., 'animated' => ...]`.
     *
     * @param  string|array<string, mixed>  $emoji
     */
    public function emoji(string|array $emoji): static
    {
        $this->emoji = is_string($emoji) ? ['name' => $emoji] : $emoji;

        return $this;
    }

    public function style(): ButtonStyle
    {
        return $this->style;
    }

    public function validate(): void
    {
        if ($this->label !== null && mb_strlen($this->label) > self::MAX_LABEL) {
            throw InvalidDiscordMessageException::limitExceeded('button.label', mb_strlen($this->label), self::MAX_LABEL, 'characters');
        }

        if ($this->style === ButtonStyle::Premium && $this->emoji !== null) {
            throw InvalidDiscordMessageException::invalidButton('a premium button cannot set an emoji');
        }

        if ($this->style->isInteractive() && $this->customId !== null && mb_strlen($this->customId) > self::MAX_CUSTOM_ID) {
            throw InvalidDiscordMessageException::limitExceeded('button.custom_id', mb_strlen($this->customId), self::MAX_CUSTOM_ID, 'characters');
        }
    }

    public function toArray(): array
    {
        $this->validate();

        return Serializer::build([
            'type' => 2,
            'style' => $this->style,
            'label' => $this->label,
            'emoji' => $this->emoji,
            'custom_id' => $this->customId,
            'url' => $this->url,
            'sku_id' => $this->skuId,
            'disabled' => $this->disabled,
        ]);
    }

    private static function interactive(ButtonStyle $style, string $customId, string $label): self
    {
        $button = new self($style, $label);
        $button->customId = $customId;

        return $button;
    }
}

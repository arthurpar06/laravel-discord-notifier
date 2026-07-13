<?php

namespace Arthurpar06\DiscordNotifier\Messages;

use Arthurpar06\DiscordNotifier\Contracts\Arrayable;
use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbed;
use Arthurpar06\DiscordNotifier\Enums\MessageFlag;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;
use Arthurpar06\DiscordNotifier\Support\Serializer;

/**
 * A Discord Create Message body, assembled fluently.
 *
 * @see https://docs.discord.com/developers/resources/message#create-message
 *
 * @phpstan-consistent-constructor
 */
class DiscordMessage implements Arrayable
{
    public const MAX_CONTENT = 2000;

    public const MAX_EMBEDS = 10;

    protected ?string $content = null;

    /** @var array<int, DiscordEmbed> */
    protected array $embeds = [];

    protected ?bool $tts = null;

    /** @var array<int, MessageFlag> */
    protected array $flags = [];

    protected ?AllowedMentions $allowedMentions = null;

    public static function make(): static
    {
        return new static;
    }

    public function content(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param  array<int, DiscordEmbed>  $embeds
     */
    public function embeds(array $embeds): static
    {
        $this->embeds = array_values($embeds);

        return $this;
    }

    public function embed(DiscordEmbed $embed): static
    {
        $this->embeds[] = $embed;

        return $this;
    }

    public function tts(bool $tts = true): static
    {
        $this->tts = $tts;

        return $this;
    }

    public function flags(MessageFlag ...$flags): static
    {
        $this->flags = array_values($flags);

        return $this;
    }

    public function flag(MessageFlag $flag): static
    {
        $this->flags[] = $flag;

        return $this;
    }

    public function allowedMentions(AllowedMentions $allowedMentions): static
    {
        $this->allowedMentions = $allowedMentions;

        return $this;
    }

    protected function usesComponentsV2(): bool
    {
        return in_array(MessageFlag::IsComponentsV2, $this->flags, true);
    }

    public function validate(): void
    {
        if ($this->content !== null && mb_strlen($this->content) > self::MAX_CONTENT) {
            throw InvalidDiscordMessageException::limitExceeded('content', mb_strlen($this->content), self::MAX_CONTENT, 'characters');
        }

        if (count($this->embeds) > self::MAX_EMBEDS) {
            throw InvalidDiscordMessageException::limitExceeded('embeds', count($this->embeds), self::MAX_EMBEDS);
        }

        if ($this->usesComponentsV2() && ($this->content !== null || $this->embeds !== [])) {
            throw InvalidDiscordMessageException::componentsV2Conflict();
        }
    }

    public function toArray(): array
    {
        $this->validate();

        return Serializer::build([
            'content' => $this->content,
            'embeds' => $this->embeds === [] ? null : $this->embeds,
            'tts' => $this->tts,
            'flags' => MessageFlag::combine($this->flags),
            'allowed_mentions' => $this->allowedMentions,
        ]);
    }
}

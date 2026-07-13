<?php

namespace Arthurpar06\DiscordNotifier\Embeds;

use Arthurpar06\DiscordNotifier\Contracts\Arrayable;
use Arthurpar06\DiscordNotifier\Enums\DiscordColor;
use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;
use Arthurpar06\DiscordNotifier\Support\Serializer;
use DateTimeInterface;

/**
 * @phpstan-consistent-constructor
 */
class DiscordEmbed implements Arrayable
{
    public const MAX_TITLE = 256;

    public const MAX_DESCRIPTION = 4096;

    public const MAX_FIELDS = 25;

    public const MAX_FOOTER_TEXT = 2048;

    public const MAX_AUTHOR_NAME = 256;

    public const MAX_TOTAL = 6000;

    protected ?string $title = null;

    protected ?string $description = null;

    protected ?string $url = null;

    protected ?int $color = null;

    protected ?string $timestamp = null;

    protected ?DiscordEmbedFooter $footer = null;

    protected ?DiscordEmbedAuthor $author = null;

    protected ?DiscordEmbedImage $image = null;

    protected ?DiscordEmbedThumbnail $thumbnail = null;

    /** @var array<int, DiscordEmbedField> */
    protected array $fields = [];

    public static function make(): static
    {
        return new static;
    }

    public function title(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function description(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function url(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function color(int|DiscordColor|null $color): static
    {
        $this->color = $color instanceof DiscordColor ? $color->value : $color;

        return $this;
    }

    public function timestamp(DateTimeInterface|string|null $timestamp): static
    {
        $this->timestamp = $timestamp instanceof DateTimeInterface
            ? $timestamp->format(DateTimeInterface::ATOM)
            : $timestamp;

        return $this;
    }

    public function footer(DiscordEmbedFooter|string|null $footer): static
    {
        $this->footer = is_string($footer) ? DiscordEmbedFooter::make($footer) : $footer;

        return $this;
    }

    public function author(DiscordEmbedAuthor|string|null $author): static
    {
        $this->author = is_string($author) ? DiscordEmbedAuthor::make($author) : $author;

        return $this;
    }

    public function image(DiscordEmbedImage|string|null $image): static
    {
        $this->image = is_string($image) ? DiscordEmbedImage::make($image) : $image;

        return $this;
    }

    public function thumbnail(DiscordEmbedThumbnail|string|null $thumbnail): static
    {
        $this->thumbnail = is_string($thumbnail) ? DiscordEmbedThumbnail::make($thumbnail) : $thumbnail;

        return $this;
    }

    /**
     * @param  array<int, DiscordEmbedField>  $fields
     */
    public function fields(array $fields): static
    {
        $this->fields = array_values($fields);

        return $this;
    }

    public function field(string $name, string $value, bool $inline = false): static
    {
        $this->fields[] = DiscordEmbedField::make($name, $value)->inline($inline);

        return $this;
    }

    public function validate(): void
    {
        if ($this->title !== null && mb_strlen($this->title) > self::MAX_TITLE) {
            throw InvalidDiscordMessageException::limitExceeded('embed.title', mb_strlen($this->title), self::MAX_TITLE, 'characters');
        }

        if ($this->description !== null && mb_strlen($this->description) > self::MAX_DESCRIPTION) {
            throw InvalidDiscordMessageException::limitExceeded('embed.description', mb_strlen($this->description), self::MAX_DESCRIPTION, 'characters');
        }

        if (count($this->fields) > self::MAX_FIELDS) {
            throw InvalidDiscordMessageException::limitExceeded('embed.fields', count($this->fields), self::MAX_FIELDS);
        }

        if ($this->footer !== null && mb_strlen($this->footer->text()) > self::MAX_FOOTER_TEXT) {
            throw InvalidDiscordMessageException::limitExceeded('embed.footer.text', mb_strlen($this->footer->text()), self::MAX_FOOTER_TEXT, 'characters');
        }

        if ($this->author !== null && mb_strlen($this->author->name()) > self::MAX_AUTHOR_NAME) {
            throw InvalidDiscordMessageException::limitExceeded('embed.author.name', mb_strlen($this->author->name()), self::MAX_AUTHOR_NAME, 'characters');
        }

        $total = $this->totalLength();

        if ($total > self::MAX_TOTAL) {
            throw InvalidDiscordMessageException::limitExceeded('embed (combined text)', $total, self::MAX_TOTAL, 'characters');
        }
    }

    /**
     * Combined character count Discord enforces across an embed's text fields.
     */
    public function totalLength(): int
    {
        $total = mb_strlen((string) $this->title)
            + mb_strlen((string) $this->description)
            + ($this->footer !== null ? mb_strlen($this->footer->text()) : 0)
            + ($this->author !== null ? mb_strlen($this->author->name()) : 0);

        foreach ($this->fields as $field) {
            $total += mb_strlen($field->name()) + mb_strlen($field->value());
        }

        return $total;
    }

    public function toArray(): array
    {
        $this->validate();

        return Serializer::build([
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'color' => $this->color,
            'timestamp' => $this->timestamp,
            'footer' => $this->footer,
            'author' => $this->author,
            'image' => $this->image,
            'thumbnail' => $this->thumbnail,
            'fields' => $this->fields === [] ? null : $this->fields,
        ]);
    }
}

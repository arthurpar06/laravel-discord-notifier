<?php

namespace Arthurpar06\DiscordNotifier\Messages;

use Arthurpar06\DiscordNotifier\Exceptions\InvalidDiscordMessageException;
use Illuminate\Http\Client\Response;

/**
 * The identity of a message Discord has accepted: enough to edit it later.
 */
final class SentMessage
{
    public function __construct(
        public readonly string $id,
        public readonly string $channelId,
    ) {}

    /**
     * Build from an HTTP response, treating an empty or non-JSON body as a
     * response without a message.
     */
    public static function fromHttpResponse(Response $response): self
    {
        $body = $response->json();

        return self::fromResponse(is_array($body) ? $body : []);
    }

    /**
     * Build from a Discord message object (the body of a Create/Edit Message response).
     *
     * @param  array<array-key, mixed>  $body
     */
    public static function fromResponse(array $body): self
    {
        $id = $body['id'] ?? null;
        $channelId = $body['channel_id'] ?? null;

        if (! is_string($id)) {
            throw InvalidDiscordMessageException::invalidResponse('id');
        }

        if (! is_string($channelId)) {
            throw InvalidDiscordMessageException::invalidResponse('channel_id');
        }

        return new self($id, $channelId);
    }
}

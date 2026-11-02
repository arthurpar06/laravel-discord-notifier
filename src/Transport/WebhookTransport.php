<?php

namespace Arthurpar06\DiscordNotifier\Transport;

use Arthurpar06\DiscordNotifier\Messages\SentMessage;
use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;
use Illuminate\Support\Facades\Http;

/**
 * Delivers a message by executing a Discord webhook: a plain JSON POST to the
 * webhook URL, with no authentication header. Sends use `wait=true` so Discord
 * returns the created message; edits PATCH {webhook}/messages/{id}. Any query
 * string on the route (e.g. thread_id) is preserved on both.
 */
class WebhookTransport implements Transport
{
    public function send(DiscordRoute $route, array $payload): SentMessage
    {
        return SentMessage::fromHttpResponse(
            Http::asJson()
                ->post($this->url($route->target(), '', ['wait' => 'true']), $payload)
                ->throw()
        );
    }

    public function edit(DiscordRoute $route, string $messageId, array $payload): SentMessage
    {
        return SentMessage::fromHttpResponse(
            Http::asJson()
                ->patch($this->url($route->target(), '/messages/'.rawurlencode($messageId)), $payload)
                ->throw()
        );
    }

    /**
     * Append a path suffix and extra query parameters to the webhook URL,
     * keeping whatever query string it already carries.
     *
     * @param  array<string, string>  $extraQuery
     */
    protected function url(string $webhook, string $pathSuffix, array $extraQuery = []): string
    {
        $parts = explode('?', $webhook, 2);
        $query = $parts[1] ?? '';

        if ($extraQuery !== []) {
            $query = ltrim($query.'&'.http_build_query($extraQuery), '&');
        }

        return $parts[0].$pathSuffix.($query === '' ? '' : '?'.$query);
    }
}

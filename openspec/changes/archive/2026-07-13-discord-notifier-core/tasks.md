## 1. Personalize the package skeleton

- [x] 1.1 Run `php ./configure.php` (or emulate it) to replace all `VendorName\Skeleton` / `:vendor_slug` placeholders with the real vendor + package identity
- [x] 1.2 Rename config to `config/discord-notifier.php`, the service provider, and the facade accordingly; update `composer.json` autoload namespaces
- [x] 1.3 Delete unused skeleton example command/class stubs not needed by the notifier
- [x] 1.4 Verify the package still boots: `composer test` (Testbench discovers the provider) and `composer analyse` pass on the renamed skeleton

## 2. Configuration & service provider

- [x] 2.1 Write `config/discord-notifier.php` exposing `bot.token` (`env('DISCORD_BOT_TOKEN')`) and `bot.api_base` (`https://discord.com/api/v10`), no default route
- [x] 2.2 Register the config file and (in 5.x) the `discord` notification channel in the service provider's `configurePackage()` chain

## 3. Serialization contract & enums

- [x] 3.1 Add a `DiscordPayload`/serializable contract declaring `toArray(): array`
- [x] 3.2 Add a `Support` helper for recursive null-stripping and enum→scalar coercion used by all `toArray()` implementations
- [x] 3.3 Add `MessageFlag` enum (`SUPPRESS_EMBEDS` 1<<2, `SUPPRESS_NOTIFICATIONS` 1<<12, `IS_COMPONENTS_V2` 1<<15, …) with a helper to bitwise-OR selected cases
- [x] 3.4 Add an embed color enum/helper coercing to the integer Discord expects
- [x] 3.5 Add a base validation exception type that reports offending field, value, and limit

## 4. Embed objects

- [x] 4.1 Implement `DiscordEmbed` (`make()` + fluent setters: title, description, url, color, timestamp) with `toArray()`
- [x] 4.2 Implement embed sub-objects: footer (`text`, `icon_url`), author (`name`, `url`, `icon_url`), image (`url`), thumbnail (`url`)
- [x] 4.3 Implement embed fields (`name`, `value`, `inline`) and wire `->fields([...])` / add-field onto `DiscordEmbed`
- [x] 4.4 Add embed-level limit validation (title ≤256, description ≤4096, ≤25 fields, field name ≤256 / value ≤1024, footer ≤2048, author name ≤256, total ≤6000) invoked from `toArray()`

## 5. DiscordMessage payload

- [x] 5.1 Implement `DiscordMessage` (`make()` + fluent: `content`, `embeds`, `tts`, `flags`) with `toArray()` emitting only set fields
- [x] 5.2 Add an `allowed_mentions` typed object (`parse`, `roles`, `users`, `replied_user`) and setters on the message
- [x] 5.3 Implement message-level limit validation (content ≤2000, ≤10 embeds) and the `IS_COMPONENTS_V2` mutual-exclusivity check (flag ⇒ no content/embeds), invoked from `toArray()`

## 6. Routing

- [x] 6.1 Implement `DiscordRoute` value object with `webhook(string $url)` and `channel(string $id)`
- [x] 6.2 Implement route resolution: `DiscordRoute` used as-is; `http…` string → webhook; all-digit snowflake → bot channel; otherwise throw

## 7. Transports

- [x] 7.1 Implement `WebhookTransport`: POST the serialized body as JSON to the webhook URL via the framework HTTP client
- [x] 7.2 Implement `BotTransport`: POST to `{api_base}/channels/{id}/messages` with `Authorization: Bot {token}`; throw a clear exception when the token is unconfigured

## 8. Notification channel

- [x] 8.1 Implement `DiscordChannel::send($notifiable, $notification)`: resolve route (on-demand `Notification::route` and notifiable `routeNotificationForDiscord`), select transport, serialize `toDiscord()`, dispatch
- [x] 8.2 Guard that `toDiscord()` returns a `DiscordMessage` (throw otherwise) and that a missing route surfaces clearly rather than sending nothing silently

## 9. Tests

- [x] 9.1 Serialization tests: only-set-fields emitted, null-stripping, enum coercion, allowed_mentions object, embed sub-objects
- [x] 9.2 Validation tests: >10 embeds, content >2000, embed field caps, V2-flag-with-content exclusivity, and a valid message passing
- [x] 9.3 Route resolution tests: `DiscordRoute`, http URL → webhook, snowflake → bot channel, unrecognisable value throws
- [x] 9.4 Transport tests with `Http::fake()`: webhook POST target/body, bot POST to `/channels/{id}/messages` with Bot auth header, missing-token error
- [x] 9.5 Channel tests: on-demand routing, notifiable `routeNotificationForDiscord` (DM channel id), non-`DiscordMessage` and missing-route errors

## 10. Docs & finalize

- [x] 10.1 Document usage (webhook + bot, `Notification::route`, `routeNotificationForDiscord` with a stored channel id) and the `.env`/config setup in the README
- [x] 10.2 Run `composer format`, `composer analyse`, and `composer test`; ensure the full suite is green

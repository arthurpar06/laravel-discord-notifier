## Why

Sending Discord messages from Laravel today means hand-writing untyped arrays and re-reading the Discord API docs every time to remember field names, limits, and enum values. This package replaces that with a strongly-typed, fluent builder wired into Laravel's notification system, so a message is expressed as `DiscordMessage::make()->embeds([DiscordEmbed::make()->title('…')])` and delivered over either a webhook or a bot — with the IDE and the type system doing the remembering.

## What Changes

- Introduce a fluent, strongly-typed message payload (`DiscordMessage`) that models the Discord Create Message body (`content`, `embeds`, `allowed_mentions`, `flags`, `tts`), with `DiscordEmbed` and its sub-objects (footer, author, image, thumbnail, fields) and enums for every fixed-value field (`MessageFlag`, embed color helpers).
- Introduce a transport layer with two delivery mechanisms behind one message: **webhook** (`POST` to a webhook URL) and **bot** (`POST /channels/{id}/messages` with a bot token). DM-to-user and message-to-guild-channel are the same bot call.
- Introduce `DiscordRoute` (`webhook(url)` | `channel(id)`) plus bare-string sniffing so `Notification::route('discord', $channelId)` and a User model's `routeNotificationForDiscord()` both work ergonomically.
- Add a Laravel notification channel (`via('discord')` + `toDiscord($notifiable)`) that resolves the route, selects the transport, serializes the message, and sends it. No default route — routing is always explicit.
- Bot token and API base live in a published config file, read from `.env` (`DISCORD_BOT_TOKEN`).
- Validate *kinds* through the PHP type system and enums (free, IDE-time); validate *limits/lengths* (≤10 embeds, content ≤2000, embed field caps, mutually-exclusive `IS_COMPONENTS_V2`) at send time via `toArray()`, raising descriptive exceptions.
- Personalize the Spatie skeleton (replace `VendorName\Skeleton` placeholders, config filename, service provider, facade) so real package code can be written.
- Interactive **components** (buttons, select menus, action rows) and **Components V2** container trees are intentionally **out of scope** for this change and deferred to a fast-follow; the `DiscordMessage` shape and `IS_COMPONENTS_V2` flag leave the door open.

## Capabilities

### New Capabilities
- `discord-message`: The typed, fluent message payload — `DiscordMessage`, `DiscordEmbed` and sub-objects, enums, null-stripping serialization to the Create Message array, and send-time limit validation.
- `discord-transport`: Delivery over webhook and bot, `DiscordRoute` and route resolution (DiscordRoute | http URL → webhook | snowflake → bot channel), bot config/token handling.
- `discord-notification-channel`: The Laravel notification channel binding `via('discord')`/`toDiscord()` to the transport, including on-demand (`Notification::route`) and notifiable (`routeNotificationForDiscord`) routing.

### Modified Capabilities
<!-- None — greenfield package, no existing specs. -->

## Impact

- **Package identity**: renames the skeleton namespace/config/provider/facade — every file currently carrying `VendorName\Skeleton`, `skeleton`, `:vendor_slug` placeholders. This is a one-time prerequisite for all other work.
- **New code**: `src/` gains `DiscordMessage`, `Embeds/*`, `Enums/*`, `DiscordRoute`, `Transport/*`, `Notifications/*` (channel), a `Contracts` interface for serializable payloads, and a `Support` helper for validation/null-stripping.
- **Config**: new published `config/discord-notifier.php`.
- **Dependencies**: uses the framework HTTP client (`illuminate/http`); no new third-party runtime dependency expected.
- **Tests**: Pest tests against Orchestra Testbench for serialization, validation, route resolution, and transport dispatch (HTTP faked).
- **Consumers**: `via('discord')` notifications and `Notification::route('discord', …)` become available; no breaking change (nothing ships yet).

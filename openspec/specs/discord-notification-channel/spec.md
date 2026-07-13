# discord-notification-channel Specification

## Purpose

Bind the Discord message and transport layers into Laravel's notification system as a `discord` channel, resolving the destination from either an on-demand route or the notifiable itself.

## Requirements

### Requirement: Discord notification channel

The system SHALL register a Laravel notification channel identified by `discord`, so a `Notification` returning `['discord']` from `via()` and building its payload in `toDiscord($notifiable)` is delivered through the Discord transport.

#### Scenario: Notification delivered

- **WHEN** a notification lists `discord` in `via()` and returns a `DiscordMessage` from `toDiscord()`
- **THEN** the channel serializes that message and sends it through the resolved transport

#### Scenario: toDiscord returns an unsupported value

- **WHEN** `toDiscord()` returns something other than a `DiscordMessage`
- **THEN** an exception is raised indicating a `DiscordMessage` is required

### Requirement: On-demand routing

The system SHALL support on-demand notifications via `Notification::route('discord', $route)`, where `$route` is a `DiscordRoute` or a bare string resolved per the transport rules.

#### Scenario: Route to a channel id on demand

- **WHEN** a caller writes `Notification::route('discord', $channelId)->notify($notification)`
- **THEN** the message is delivered to that channel via the bot transport

### Requirement: Notifiable routing

The system SHALL resolve the route from the notifiable's `routeNotificationForDiscord()` method when a notifiable is notified directly, allowing a model to return its own channel id (e.g. a stored private DM channel id) or a `DiscordRoute`.

#### Scenario: User receives a DM

- **WHEN** a User model returns its stored `discord_private_channel_id` from `routeNotificationForDiscord()` and `$user->notify($notification)` is called
- **THEN** the message is delivered to that channel via the bot transport

#### Scenario: No route available

- **WHEN** a notifiable provides no Discord route and none is supplied on demand
- **THEN** the notification is not sent and the channel surfaces the missing route rather than failing silently

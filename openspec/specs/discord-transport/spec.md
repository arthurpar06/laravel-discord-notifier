# discord-transport Specification

## Purpose

Deliver a serialized Discord message to its destination over either a webhook or a bot, and resolve where a message goes from an explicit `DiscordRoute` or a bare route value, with bot credentials read from configuration.

## Requirements

### Requirement: Explicit routing via DiscordRoute

The system SHALL provide a `DiscordRoute` value object with static constructors `webhook(string $url)` and `channel(string $id)` that explicitly declare how a message is delivered.

#### Scenario: Webhook route

- **WHEN** a caller creates `DiscordRoute::webhook('https://discord.com/api/webhooks/…')`
- **THEN** the route targets the webhook transport with that URL

#### Scenario: Channel route

- **WHEN** a caller creates `DiscordRoute::channel('123456789012345678')`
- **THEN** the route targets the bot transport with that channel id

### Requirement: Bare-string route resolution

The system SHALL resolve a bare string route value into a transport unambiguously: a value starting with `http` resolves to the webhook transport; an all-digits snowflake resolves to the bot transport channel id. A `DiscordRoute` instance is used as declared.

#### Scenario: URL string resolves to webhook

- **WHEN** the route value is the string `https://discord.com/api/webhooks/…`
- **THEN** it resolves to the webhook transport

#### Scenario: Snowflake string resolves to bot channel

- **WHEN** the route value is a numeric snowflake string such as `123456789012345678`
- **THEN** it resolves to the bot transport with that channel id

#### Scenario: Unrecognisable route value

- **WHEN** the route value is neither a `DiscordRoute`, an http URL, nor a snowflake string
- **THEN** an exception is raised explaining the expected route forms

### Requirement: Webhook delivery

The system SHALL deliver a serialized message over a webhook by POSTing the Create Message body to the webhook URL.

#### Scenario: Send via webhook

- **WHEN** a message is delivered to a webhook route
- **THEN** an HTTP POST is issued to the webhook URL with the serialized body as JSON

### Requirement: Bot delivery

The system SHALL deliver a serialized message over a bot by POSTing the Create Message body to `POST {api_base}/channels/{id}/messages` with an `Authorization: Bot {token}` header, where the token and API base come from configuration. A DM channel id and a guild channel id use the same call.

#### Scenario: Send via bot to a channel

- **WHEN** a message is delivered to a channel route (guild channel or a user's DM channel)
- **THEN** an HTTP POST is issued to `/channels/{id}/messages` carrying the bot Authorization header and the serialized JSON body

#### Scenario: Missing bot token

- **WHEN** a bot delivery is attempted but no `DISCORD_BOT_TOKEN` is configured
- **THEN** an exception is raised indicating the bot token is not configured

### Requirement: Configuration from environment

The system SHALL publish a config file exposing the bot token and API base, read from environment variables (`DISCORD_BOT_TOKEN`), with no default route configured.

#### Scenario: Token read from config

- **WHEN** `DISCORD_BOT_TOKEN` is set in the environment
- **THEN** the bot transport uses that token as its Authorization credential

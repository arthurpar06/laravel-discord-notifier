# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A Laravel package (not an application): a **strongly-typed Discord notifier** that plugs into Laravel's notification system. Notifications declare `discord` in their `via()` and return a `DiscordMessage`; the package delivers it either through a **webhook URL** or through the **Discord bot API** (channel / DM ids), chosen per-route.

The package was scaffolded from the Spatie [`package-skeleton-laravel`](https://github.com/spatie/package-skeleton-laravel) template and has since been fully personalized — namespace `Arthurpar06\DiscordNotifier\`, config `config/discord-notifier.php`, provider `DiscordNotifierServiceProvider`. No skeleton placeholders remain and there is no `configure.php` step to run.

## Commands

```bash
composer test              # run the Pest test suite
vendor/bin/pest --filter=SomeTest   # run a single test / group
composer test-coverage     # Pest with code coverage (--min=100)
composer test-type-coverage # Pest type coverage (--min=100)
composer test-mutate       # Pest mutation testing (--min=97)
composer analyse           # PHPStan (larastan) static analysis, level 10
composer format            # Laravel Pint (code style, fixes in place)
composer prepare           # re-run testbench package:discover (auto-runs post-autoload-dump)
```

There is no build step — this is a library consumed via Composer.

## Architecture

Standard `spatie/laravel-package-tools` layout, with the domain split into small typed value objects. Data flows: **notification → `DiscordMessage` → `Serializer` → `Transport` → Discord**.

- **`src/DiscordNotifierServiceProvider.php`** — entry point. `configurePackage()` declares the package name and config file; `packageBooted()` registers the `discord` driver on Laravel's `ChannelManager` so it can be used in `via()` and `Notification::route('discord', ...)`.
- **`src/Notifications/DiscordChannel.php`** — the notification channel. Resolves the route, builds the message, and hands it to a transport.
- **`src/Messages/`** — `DiscordMessage` (the public builder returned from `toDiscord()`) and `AllowedMentions`.
- **`src/Routing/`** — `DiscordRoute` + `RouteType`: where a message goes (webhook URL vs. bot channel/DM id). There is **no default route** — every notification must declare one via `Notification::route('discord', ...)` or a notifiable's `routeNotificationForDiscord()`.
- **`src/Transport/`** — `Transport` interface with `WebhookTransport` and `BotTransport` implementations, selected by `TransportFactory` based on the route type. `BotTransport` uses the credentials in `config/discord-notifier.php` (`DISCORD_BOT_TOKEN`); webhooks do not.
- **`src/Embeds/`** — `DiscordEmbed` and its parts (author, field, footer, image, thumbnail).
- **`src/Components/`** — message components: `ActionRow`, `Button` (see the interactive-button caveat in the README — bot-sent buttons need an application command handler Discord will invoke).
- **`src/Enums/`** — `AllowedMentionType`, `ButtonStyle`, `DiscordColor`, `MessageFlag`.
- **`src/Support/Serializer.php`** — turns the typed objects into Discord's JSON payload shape.
- **`src/Contracts/Arrayable.php`** — implemented by value objects that serialize to a payload array.
- **`src/Exceptions/`** — `DiscordConfigurationException`, `InvalidDiscordMessageException`, `InvalidDiscordRouteException`.

Autoloading (`composer.json`): `src/` → `Arthurpar06\DiscordNotifier\`, tests → `Arthurpar06\DiscordNotifier\Tests\`. Package auto-discovery registers the provider via the `extra.laravel` block.

## Testing

- **Pest 4** on top of **Orchestra Testbench** — tests boot a real Laravel app in-memory with `DiscordNotifierServiceProvider` registered.
- `tests/TestCase.php` is the base case: registers the provider and points the DB at the `testing` connection. Add package providers / env config here.
- `tests/Pest.php` binds `TestCase` to every test in the directory.
- `tests/ArchTest.php` enforces that `dd`, `dump`, and `ray` are never committed — leftover debug calls fail the suite.
- Quality gates are strict: 100% code coverage, 100% type coverage, ≥97% mutation score, PHPStan level 10.
- CI matrix (`.github/workflows/run-tests.yml`) runs PHP 8.3–8.5 × Laravel 12–13 × prefer-lowest/prefer-stable on Linux and Windows; keep code compatible across that range (min PHP is `^8.3` per `composer.json`).

## Spec-driven workflow (OpenSpec)

This repo uses **OpenSpec** (`openspec/`, config `openspec/config.yaml`, schema `spec-driven`) with matching Claude skills/commands (`openspec-propose`, `openspec-explore`, `openspec-apply-change`, `openspec-archive-change`, `openspec-sync-specs`, and the `/opsx:*` commands). For non-trivial features, prefer proposing a change and generating specs/tasks before implementing, rather than editing code directly. Change proposals live in `openspec/changes/`, synced specs in `openspec/specs/`.

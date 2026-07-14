# Laravel Discord Notifier

[![Latest Version on Packagist](https://img.shields.io/packagist/v/arthurpar06/laravel-discord-notifier.svg?style=flat-square)](https://packagist.org/packages/arthurpar06/laravel-discord-notifier)
[![Total Downloads](https://img.shields.io/packagist/dt/arthurpar06/laravel-discord-notifier.svg?style=flat-square)](https://packagist.org/packages/arthurpar06/laravel-discord-notifier)

A strongly-typed way to send Discord messages through Laravel's notification system — over a **webhook** or a **bot** — without ever hand-writing the Discord API payload again.

```php
use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;
use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbed;
use Arthurpar06\DiscordNotifier\Enums\DiscordColor;

DiscordMessage::make()
    ->content('Deployment finished')
    ->embed(
        DiscordEmbed::make()
            ->title('Bonjour')
            ->description('Everything is green.')
            ->color(DiscordColor::Green)
            ->field('Environment', 'production', inline: true)
    );
```

Your IDE and the type system remember the field names, the enums, and the limits — so you don't have to reopen the Discord docs every time.

## Installation

```bash
composer require arthurpar06/laravel-discord-notifier
```

Publish the config file:

```bash
php artisan vendor:publish --tag="discord-notifier-config"
```

To deliver via a bot, set your bot token in `.env`:

```dotenv
DISCORD_BOT_TOKEN=your-bot-token
```

```php
// config/discord-notifier.php
return [
    'bot' => [
        'token' => env('DISCORD_BOT_TOKEN'),
        'api_base' => 'https://discord.com/api/v10',
    ],
];
```

There is **no default route** — every notification declares where it goes.

## Building a message

`DiscordMessage` models the [Create Message](https://docs.discord.com/developers/resources/message#create-message) body. Everything is fluent and typed:

```php
use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;
use Arthurpar06\DiscordNotifier\Messages\AllowedMentions;
use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbed;
use Arthurpar06\DiscordNotifier\Enums\AllowedMentionType;
use Arthurpar06\DiscordNotifier\Enums\MessageFlag;

DiscordMessage::make()
    ->content('Heads up <@123>')
    ->embeds([
        DiscordEmbed::make()
            ->title('Report')
            ->footer('generated automatically')
            ->author('CI bot'),
    ])
    ->allowedMentions(AllowedMentions::make()->parse([AllowedMentionType::Users]))
    ->flags(MessageFlag::SuppressNotifications);
```

Only the fields you set are sent. Discord's limits (≤10 embeds, content ≤2000 chars, embed field caps, the `IS_COMPONENTS_V2` mutual-exclusivity rule, …) are validated when the message is serialized, with an exception that names the offending field.

## Buttons

Attach buttons with `->button()` (a single button in its own row) or `->actionRow()` (up to five buttons per row, up to five rows per message). Buttons are built through a named constructor per style, so you can't assemble an invalid combination:

```php
use Arthurpar06\DiscordNotifier\Components\Button;
use Arthurpar06\DiscordNotifier\Embeds\DiscordEmbed;
use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;

DiscordMessage::make()
    ->embed(DiscordEmbed::make()->title('Deploy finished'))
    ->actionRow(
        Button::link('https://ci.example.com/builds/42', 'View build'),
        Button::primary('redeploy', 'Redeploy'),
    );
```

| Constructor | Style | Needs |
|---|---|---|
| `Button::link($url, $label = null)` | Link | a URL |
| `Button::primary/secondary/success/danger($customId, $label)` | interactive | a `custom_id` |
| `Button::premium($skuId)` | Premium | an SKU id |

All non-premium buttons also support `->disabled()` and `->emoji('🔥')` (or a custom emoji array).

> **Interactive buttons need your own interaction handling.** This package only *sends* messages. `link` and `premium` buttons work with nothing extra, but `primary`/`secondary`/`success`/`danger` buttons raise a Discord Interaction when clicked — if your application does not answer it (via a gateway or an HTTP interactions endpoint) within three seconds, Discord shows "This interaction failed." Reach for link buttons unless you already run an interaction handler.

## Sending notifications

In your notification, list `discord` in `via()` and return a `DiscordMessage` from `toDiscord()`:

```php
use Arthurpar06\DiscordNotifier\Messages\DiscordMessage;
use Illuminate\Notifications\Notification;

class DeploymentFinished extends Notification
{
    public function via($notifiable): array
    {
        return ['discord'];
    }

    public function toDiscord($notifiable): DiscordMessage
    {
        return DiscordMessage::make()->content('Deployment finished ✅');
    }
}
```

### Route on demand

Pass a bot **channel id** (a guild channel or a user's DM channel) or an explicit route:

```php
use Illuminate\Support\Facades\Notification;
use Arthurpar06\DiscordNotifier\Routing\DiscordRoute;

// bare channel id → delivered by the bot
Notification::route('discord', config('services.discord.admin_channel_id'))
    ->notify(new DeploymentFinished);

// explicit webhook
Notification::route('discord', DiscordRoute::webhook(config('services.discord.webhook')))
    ->notify(new DeploymentFinished);
```

### Route from a model

Give the notifiable a `routeNotificationForDiscord()` returning its own channel id — e.g. a stored private DM channel:

```php
class User extends Authenticatable
{
    use Notifiable;

    public function routeNotificationForDiscord(): string
    {
        return $this->discord_private_channel_id;
    }
}

$user->notify(new DeploymentFinished);
```

## How routing is resolved

The route value is resolved to a transport, unambiguously:

| Route value | Transport |
|---|---|
| `DiscordRoute::webhook($url)` / `DiscordRoute::channel($id)` | as declared |
| a string starting with `http` | webhook |
| a numeric snowflake string | bot channel (`POST /channels/{id}/messages`) |

A DM to a user and a message to a guild channel are the same bot call — store the channel id and send.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

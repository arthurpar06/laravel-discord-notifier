<?php

// config for Arthurpar06/DiscordNotifier
return [

    /*
    |--------------------------------------------------------------------------
    | Bot delivery
    |--------------------------------------------------------------------------
    |
    | Credentials used when a message is routed to a channel id (a guild
    | channel or a user's DM channel) via the bot transport. Messages routed
    | to a webhook URL do not use these.
    |
    */

    'bot' => [
        'token' => env('DISCORD_BOT_TOKEN'),
        'api_base' => 'https://discord.com/api/v10',
    ],

    /*
    | There is no default route. Every notification must declare where it goes,
    | either via Notification::route('discord', ...) or a notifiable's
    | routeNotificationForDiscord() method.
    */

];

<?php

namespace Arthurpar06\DiscordNotifier;

use Arthurpar06\DiscordNotifier\Notifications\DiscordChannel;
use Illuminate\Notifications\ChannelManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DiscordNotifierServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('discord-notifier')
            ->hasConfigFile();
    }

    public function packageBooted(): void
    {
        // Register the "discord" notification driver so notifications can list
        // it in via() and be routed with Notification::route('discord', ...).
        $this->app->make(ChannelManager::class)->extend(
            'discord',
            fn ($app): DiscordChannel => $app->make(DiscordChannel::class),
        );
    }
}

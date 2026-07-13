<?php

namespace Arthurpar06\DiscordNotifier\Tests;

use Arthurpar06\DiscordNotifier\DiscordNotifierServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            DiscordNotifierServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}

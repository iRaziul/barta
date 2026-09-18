<?php

declare(strict_types=1);

namespace Larament\Barta;

use Illuminate\Contracts\Container\Container;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Larament\Barta\Commands\InstallBartaCommand;
use Larament\Barta\Notifications\BartaChannel;

final class BartaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/barta.php', 'barta');

        $this->app->singleton(
            BartaManager::class,
            fn (Container $container) => new BartaManager($container)
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/barta.php' => config_path('barta.php'),
            ], 'barta-config');

            $this->commands([
                InstallBartaCommand::class,
            ]);
        }

        Notification::resolved(function (ChannelManager $channel): void {
            $channel->extend('barta', fn ($app) => $app->make(BartaChannel::class));
        });
    }
}

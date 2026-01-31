<?php

declare(strict_types=1);

namespace Larament\Barta;

use Illuminate\Contracts\Container\Container;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Larament\Barta\Commands\InstallBartaCommand;
use Larament\Barta\Notifications\BartaChannel;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BartaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('barta')
            ->hasConfigFile()
            ->hasCommand(InstallBartaCommand::class);

        $this->app->singleton(
            BartaManager::class,
            fn (Container $container) => new BartaManager($container)
        );

        Notification::resolved(function (ChannelManager $channel): void {
            $channel->extend('barta', fn ($app) => $app->make(BartaChannel::class));
        });
    }
}

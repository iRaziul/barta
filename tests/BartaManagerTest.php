<?php

declare(strict_types=1);

use Larament\Barta\BartaManager;
use Larament\Barta\Drivers\EsmsDriver;
use Larament\Barta\Drivers\LogDriver;
use Larament\Barta\Drivers\MimsmsDriver;

it('can resolve the manager from the container', function () {
    $manager = app(BartaManager::class);

    expect($manager)->toBeInstanceOf(BartaManager::class);
});

it('can create a log driver', function () {
    $manager = app(BartaManager::class);

    $driver = $manager->driver('log');

    expect($driver)->toBeInstanceOf(LogDriver::class);
});

it('can create an esms driver', function () {
    config()->set('barta.drivers.esms.api_token', 'test_token');
    config()->set('barta.drivers.esms.sender_id', 'test_sender_id');

    $manager = app(BartaManager::class);

    $driver = $manager->driver('esms');

    expect($driver)->toBeInstanceOf(EsmsDriver::class);
});

it('can create a mimsms driver', function () {
    config()->set('barta.drivers.mimsms.username', 'test_user');
    config()->set('barta.drivers.mimsms.api_key', 'test_key');
    config()->set('barta.drivers.mimsms.sender_id', 'test_sender_id');

    $manager = app(BartaManager::class);

    $driver = $manager->driver('mimsms');

    expect($driver)->toBeInstanceOf(MimsmsDriver::class);
});

it('returns the default driver', function () {
    config()->set('barta.default', 'log');

    $manager = app(BartaManager::class);

    $driver = $manager->driver();

    expect($driver)->toBeInstanceOf(LogDriver::class);
});

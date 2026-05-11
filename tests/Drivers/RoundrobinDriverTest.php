<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Larament\Barta\Drivers\RoundrobinDriver;
use Larament\Barta\Exceptions\BartaException;

beforeEach(function () {
    config()->set('barta.drivers.roundrobin.drivers', ['esms', 'mimsms']);

    // Configure ESMS
    config()->set('barta.drivers.esms.api_token', 'test_token');
    config()->set('barta.drivers.esms.sender_id', 'test_sender');

    // Configure Mimsms
    config()->set('barta.drivers.mimsms.api_key', 'test_key');
    config()->set('barta.drivers.mimsms.username', 'test_user');
    config()->set('barta.drivers.mimsms.sender_id', 'test_sender');

    Cache::clear();
});

it('can instantiate the driver', function () {
    $driver = new RoundrobinDriver(config('barta.drivers.roundrobin'));
    expect($driver)->toBeInstanceOf(RoundrobinDriver::class);
});

it('rotates across different drivers', function () {
    Http::fake([
        'login.esms.com.bd/*' => Http::response(['status' => 'success'], 200),
        'api.mimsms.com/*' => Http::response(['statusCode' => 200], 200),
    ]);

    // First call -> index 1 -> mimsms (index % 2 == 1)
    $driver1 = new RoundrobinDriver(config('barta.drivers.roundrobin'));
    $driver1->to('8801700000000')->message('Test1')->send();

    // Second call -> index 2 -> esms (index % 2 == 0)
    $driver2 = new RoundrobinDriver(config('barta.drivers.roundrobin'));
    $driver2->to('8801700000000')->message('Test2')->send();

    // Third call -> index 3 -> mimsms
    $driver3 = new RoundrobinDriver(config('barta.drivers.roundrobin'));
    $driver3->to('8801700000000')->message('Test3')->send();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'mimsms.com') && isset($request['Message']) && $request['Message'] === 'Test1';
    });

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'esms.com.bd') && isset($request['message']) && $request['message'] === 'Test2';
    });

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'mimsms.com') && isset($request['Message']) && $request['Message'] === 'Test3';
    });
});

it('throws exception if config missing', function () {
    config()->set('barta.drivers.roundrobin.drivers', null);

    $driver = new RoundrobinDriver(config('barta.drivers.roundrobin'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'configure an array of drivers');

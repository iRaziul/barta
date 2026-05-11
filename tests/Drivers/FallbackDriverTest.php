<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Larament\Barta\Drivers\FallbackDriver;
use Larament\Barta\Exceptions\BartaException;

beforeEach(function () {
    config()->set('barta.drivers.fallback.drivers', ['esms', 'mimsms']);

    // Configure ESMS
    config()->set('barta.drivers.esms.api_token', 'test_token');
    config()->set('barta.drivers.esms.sender_id', 'test_sender');

    // Configure Mimsms
    config()->set('barta.drivers.mimsms.api_key', 'test_key');
    config()->set('barta.drivers.mimsms.username', 'test_user');
    config()->set('barta.drivers.mimsms.sender_id', 'test_sender');
});

it('can instantiate the driver', function () {
    $driver = new FallbackDriver(config('barta.drivers.fallback'));
    expect($driver)->toBeInstanceOf(FallbackDriver::class);
});

it('sends via first driver when successful', function () {
    Http::fake([
        'login.esms.com.bd/*' => Http::response(['status' => 'success', 'message' => 'Ok'], 200),
        'api.mimsms.com/*' => Http::response(['statusCode' => 200], 200),
    ]);

    $driver = new FallbackDriver(config('barta.drivers.fallback'));
    $response = $driver->to('8801700000000')->message('Test')->send();

    expect($response->success)->toBeTrue();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'esms.com.bd');
    });

    Http::assertNotSent(function ($request) {
        return str_contains($request->url(), 'mimsms.com');
    });
});

it('falls back to second driver when first fails', function () {
    Http::fake([
        'login.esms.com.bd/*' => Http::response(['status' => 'error', 'message' => 'Failed'], 200),
        'api.mimsms.com/*' => Http::response(['statusCode' => 200], 200),
    ]);

    $driver = new FallbackDriver(config('barta.drivers.fallback'));
    $response = $driver->to('8801700000000')->message('Test')->send();

    expect($response->success)->toBeTrue();

    Http::assertSentCount(2);
});

it('throws exception if all drivers fail', function () {
    Http::fake([
        'login.esms.com.bd/*' => Http::response(['status' => 'error', 'message' => 'Failed ESMS'], 200),
        'api.mimsms.com/*' => Http::response(['status' => 'error', 'message' => 'Failed MIMSMS'], 500),
    ]);

    $driver = new FallbackDriver(config('barta.drivers.fallback'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'All fallback drivers failed');

it('throws exception if config missing', function () {
    config()->set('barta.drivers.fallback.drivers', null);

    $driver = new FallbackDriver(config('barta.drivers.fallback'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'configure an array of drivers');

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\RobiDriver;
use Larament\Barta\Exceptions\BartaException;

beforeEach(function () {
    config()->set('barta.drivers.robi.username', 'test_user');
    config()->set('barta.drivers.robi.password', 'test_pass');
});

it('can instantiate the robi driver', function () {
    $driver = new RobiDriver(config('barta.drivers.robi'));
    expect($driver)->toBeInstanceOf(RobiDriver::class);
});

it('sends sms successfully with robi driver', function () {
    Http::fake([
        'https://bmpws.robi.com.bd/*' => Http::response('Success: Message Sent', 200),
    ]);

    $driver = new RobiDriver(config('barta.drivers.robi'));
    $response = $driver->to('8801700000000')->message('Test message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'robi.com.bd') &&
               $request['username'] === 'test_user';
    });
});

it('throws exception on robi api error', function () {
    Http::fake([
        '*' => Http::response('Error: Authentication failed', 200),
    ]);

    $driver = new RobiDriver(config('barta.drivers.robi'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class);

it('throws exception if username missing', function () {
    config()->set('barta.drivers.robi.username', null);

    $driver = new RobiDriver(config('barta.drivers.robi'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'username');

it('throws exception if password missing', function () {
    config()->set('barta.drivers.robi.password', null);

    $driver = new RobiDriver(config('barta.drivers.robi'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'password');

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\GrameenphoneDriver;
use Larament\Barta\Exceptions\BartaException;

beforeEach(function () {
    config()->set('barta.drivers.grameenphone.username', 'test_user');
    config()->set('barta.drivers.grameenphone.password', 'test_pass');
});

it('can instantiate the grameenphone driver', function () {
    $driver = new GrameenphoneDriver(config('barta.drivers.grameenphone'));
    expect($driver)->toBeInstanceOf(GrameenphoneDriver::class);
});

it('sends sms successfully with grameenphone driver', function () {
    Http::fake([
        'https://gpcmp.grameenphone.com/*' => Http::response(['statusCode' => 200, 'statusDescription' => 'Success'], 200),
    ]);

    $driver = new GrameenphoneDriver(config('barta.drivers.grameenphone'));
    $response = $driver->to('8801700000000')->message('Test message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'grameenphone.com') &&
               $request['username'] === 'test_user' &&
               $request['countrycode'] === '880';
    });
});

it('throws exception on grameenphone api error', function () {
    Http::fake([
        '*' => Http::response(['statusCode' => 401, 'statusDescription' => 'Unauthorized'], 200),
    ]);

    $driver = new GrameenphoneDriver(config('barta.drivers.grameenphone'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'Unauthorized');

it('throws exception if username missing', function () {
    config()->set('barta.drivers.grameenphone.username', null);

    $driver = new GrameenphoneDriver(config('barta.drivers.grameenphone'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'username');

it('throws exception if password missing', function () {
    config()->set('barta.drivers.grameenphone.password', null);

    $driver = new GrameenphoneDriver(config('barta.drivers.grameenphone'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'password');

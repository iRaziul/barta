<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\AdnsmsDriver;
use Larament\Barta\Exceptions\BartaException;

beforeEach(function () {
    config()->set('barta.drivers.adnsms.api_key', 'test_key');
    config()->set('barta.drivers.adnsms.api_secret', 'test_secret');
});

it('can instantiate the adn driver', function () {
    $driver = new AdnsmsDriver(config('barta.drivers.adnsms'));
    expect($driver)->toBeInstanceOf(AdnsmsDriver::class);
});

it('sends sms successfully with adn driver', function () {
    Http::fake([
        'https://portal.adnsms.com/*' => Http::response(['api_response_code' => 200, 'api_response_message' => 'Success'], 200),
    ]);

    $driver = new AdnsmsDriver(config('barta.drivers.adnsms'));
    $response = $driver->to('8801700000000')->message('Test message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();
});

it('throws exception on adn api error', function () {
    Http::fake([
        '*' => Http::response(['api_response_code' => 401, 'api_response_message' => 'Invalid API Key'], 200),
    ]);

    $driver = new AdnsmsDriver(config('barta.drivers.adnsms'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'Invalid API Key');

it('throws exception if api_key missing', function () {
    config()->set('barta.drivers.adnsms.api_key', null);

    $driver = new AdnsmsDriver(config('barta.drivers.adnsms'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'api_key');

it('throws exception if api_secret missing', function () {
    config()->set('barta.drivers.adnsms.api_secret', null);

    $driver = new AdnsmsDriver(config('barta.drivers.adnsms'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'api_secret');

it('checks balance successfully with adn driver', function () {
    Http::fake([
        'https://portal.adnsms.com/api/v1/secure/check-balance' => Http::response([
            'balance' => [
                'sms' => 49,
            ],
            'api_response_code' => 200,
            'api_response_message' => 'SUCCESS',
        ], 200),
    ]);

    $driver = new AdnsmsDriver(config('barta.drivers.adnsms'));
    $balance = $driver->balance();

    expect($balance)->toBe(49.0);
});

it('throws exception on adn balance check error', function () {
    Http::fake([
        'https://portal.adnsms.com/api/v1/secure/check-balance' => Http::response([
            'balance' => [],
            'api_response_code' => 400,
            'api_response_message' => 'INVALID_CREDENTIAL',
        ], 200),
    ]);

    $driver = new AdnsmsDriver(config('barta.drivers.adnsms'));
    $driver->balance();
})->throws(BartaException::class, 'INVALID_CREDENTIAL');

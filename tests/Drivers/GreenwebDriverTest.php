<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\GreenwebDriver;
use Larament\Barta\Exceptions\BartaException;

beforeEach(function () {
    config()->set('barta.drivers.greenweb.token', 'test_token');
});

it('can instantiate the greenweb driver', function () {
    $driver = new GreenwebDriver(config('barta.drivers.greenweb'));
    expect($driver)->toBeInstanceOf(GreenwebDriver::class);
});

it('sends sms successfully with greenweb driver', function () {
    Http::fake([
        'https://api.greenweb.com.bd/api.php*' => Http::response([
            ['status' => 'SENT', 'msgid' => '12345'],
        ], 200),
    ]);

    $driver = new GreenwebDriver(config('barta.drivers.greenweb'));
    $response = $driver->to('8801700000000')->message('Test message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();
});

it('checks balance successfully with greenweb driver', function () {
    Http::fake([
        'https://api.greenweb.com.bd/g_api.php*' => Http::response([
            ['balance' => '175.25'],
        ], 200),
    ]);

    $driver = new GreenwebDriver(config('barta.drivers.greenweb'));
    $balance = $driver->balance();

    expect($balance)->toBe(175.25);
});

it('throws exception on greenweb balance check error', function () {
    Http::fake([
        'https://api.greenweb.com.bd/g_api.php*' => Http::response([
            'error' => 'Invalid Token',
        ], 200),
    ]);

    $driver = new GreenwebDriver(config('barta.drivers.greenweb'));
    $driver->balance();
})->throws(BartaException::class, 'Invalid Token');

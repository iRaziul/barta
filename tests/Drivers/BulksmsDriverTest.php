<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\BulksmsDriver;
use Larament\Barta\Exceptions\BartaException;

beforeEach(function () {
    config()->set('barta.drivers.bulksms.api_key', 'test_key');
    config()->set('barta.drivers.bulksms.sender_id', 'test_sender');
});

it('can instantiate the bulksms driver', function () {
    $driver = new BulksmsDriver(config('barta.drivers.bulksms'));
    expect($driver)->toBeInstanceOf(BulksmsDriver::class);
});

it('sends sms successfully with bulksms driver', function () {
    Http::fake([
        'https://bulksmsbd.net/api/smsapi*' => Http::response([
            'response_code' => 202,
            'success_message' => 'SMS Submitted Successfully',
        ], 200),
    ]);

    $driver = new BulksmsDriver(config('barta.drivers.bulksms'));
    $response = $driver->to('8801700000000')->message('Test message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();
});

it('throws exception on bulksms api error', function () {
    Http::fake([
        'https://bulksmsbd.net/api/smsapi*' => Http::response([
            'response_code' => 1011,
            'error_message' => 'Invalid User ID or API Key',
        ], 200),
    ]);

    $driver = new BulksmsDriver(config('barta.drivers.bulksms'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'Invalid User ID or API Key');

it('checks balance successfully with bulksms driver', function () {
    Http::fake([
        'https://bulksmsbd.net/api/getBalanceApi*' => Http::response([
            'response_code' => 200,
            'balance' => '250.75',
        ], 200),
    ]);

    $driver = new BulksmsDriver(config('barta.drivers.bulksms'));
    $balance = $driver->balance();

    expect($balance)->toBe(250.75);
});

it('throws exception on bulksms balance check error', function () {
    Http::fake([
        'https://bulksmsbd.net/api/getBalanceApi*' => Http::response([
            'response_code' => 1011,
            'error_message' => 'Invalid API Key',
        ], 200),
    ]);

    $driver = new BulksmsDriver(config('barta.drivers.bulksms'));
    $driver->balance();
})->throws(BartaException::class, 'Invalid API Key');

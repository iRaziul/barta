<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\ZendsmsDriver;
use Larament\Barta\Exceptions\BartaException;

beforeEach(function () {
    config()->set('barta.drivers.zendsms.api_key', 'test_key');
    config()->set('barta.drivers.zendsms.sender_id', 'test_sender_id');
});

it('can instantiate the zendsms driver', function () {
    $driver = new ZendsmsDriver(config('barta.drivers.zendsms'));
    expect($driver)->toBeInstanceOf(ZendsmsDriver::class);
});

it('can set recipient and message for zendsms driver', function () {
    $driver = new ZendsmsDriver(config('barta.drivers.zendsms'));

    expect($driver->to('8801700000000'))->toBeInstanceOf(ZendsmsDriver::class);
    expect($driver->message('Test message'))->toBeInstanceOf(ZendsmsDriver::class);
});

it('sends sms successfully with zendsms driver', function () {
    Http::fake([
        'https://api.zendsms.com/*' => Http::response([
            'success' => true,
            'code' => 1000,
            'message' => 'SMS accepted',
            'data' => [
                'message_id' => '6f1a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8',
                'recipient' => '8801700000000',
                'status' => 'QUEUED',
                'sms_count' => 1,
            ],
        ], 202),
    ]);

    $driver = new ZendsmsDriver(config('barta.drivers.zendsms'));
    $response = $driver->to('8801700000000')->message('Test message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();
    expect($response->data['code'])->toBe(1000);
    expect($response->data['data']['message_id'])->toBe('6f1a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.zendsms.com/api/v1/send-sms') &&
               $request->method() === 'POST' &&
               $request->hasHeader('Authorization', 'Bearer test_key') &&
               $request['recipient'] === '8801700000000' &&
               $request['sender_id'] === 'test_sender_id' &&
               $request['message'] === 'Test message';
    });
});

it('sends bulk sms successfully with zendsms driver', function () {
    Http::fake([
        'https://api.zendsms.com/*' => Http::response([
            'success' => true,
            'code' => 1000,
            'message' => 'SMS accepted',
        ], 202),
    ]);

    $driver = new ZendsmsDriver(config('barta.drivers.zendsms'));
    $response = $driver->to(['8801700000000', '8801800000000'])->message('Bulk test')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request['recipient'] === '8801700000000,8801800000000';
    });
});

it('throws BartaException on zendsms api error', function () {
    Http::fake([
        'https://api.zendsms.com/*' => Http::response([
            'success' => false,
            'code' => 2001,
            'message' => 'Invalid API key',
        ], 401),
    ]);

    $driver = new ZendsmsDriver(config('barta.drivers.zendsms'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Invalid API key');

it('throws BartaException on zendsms business error in successful http response', function () {
    Http::fake([
        'https://api.zendsms.com/*' => Http::response([
            'success' => false,
            'code' => 2101,
            'message' => 'Invalid recipient',
        ], 200),
    ]);

    $driver = new ZendsmsDriver(config('barta.drivers.zendsms'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Invalid recipient');

it('throws BartaException if sender_id is missing for zendsms driver', function () {
    config()->set('barta.drivers.zendsms.sender_id', null);

    $driver = new ZendsmsDriver(config('barta.drivers.zendsms'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Please set sender_id for ZendSMS in config/barta.php.');

it('throws BartaException if api_key is missing for zendsms driver', function () {
    config()->set('barta.drivers.zendsms.api_key', null);

    $driver = new ZendsmsDriver(config('barta.drivers.zendsms'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Please set api_key for ZendSMS in config/barta.php.');

it('throws BartaException on http error', function () {
    Http::fake([
        'https://api.zendsms.com/*' => Http::response('Internal Server Error', 500),
    ]);

    $driver = new ZendsmsDriver(config('barta.drivers.zendsms'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class);

it('throws BartaException on non-json response', function () {
    Http::fake([
        'https://api.zendsms.com/*' => Http::response('<html>Error</html>', 200),
    ]);

    $driver = new ZendsmsDriver(config('barta.drivers.zendsms'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Invalid response received');

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\SmsnocDriver;
use Larament\Barta\Exceptions\BartaException;

beforeEach(function () {
    config()->set('barta.drivers.smsnoc.api_token', 'test_token');
    config()->set('barta.drivers.smsnoc.sender_id', 'test_sender_id');
    config()->set('barta.request.retry', 1);
});

it('can instantiate the smsnoc driver', function () {
    $driver = new SmsnocDriver(config('barta.drivers.smsnoc'));
    expect($driver)->toBeInstanceOf(SmsnocDriver::class);
});

it('can set recipient and message for smsnoc driver', function () {
    $driver = new SmsnocDriver(config('barta.drivers.smsnoc'));

    expect($driver->to('8801700000000'))->toBeInstanceOf(SmsnocDriver::class);
    expect($driver->message('Test message'))->toBeInstanceOf(SmsnocDriver::class);
});

it('sends sms successfully with smsnoc driver', function () {
    $body = [
        'status' => 'success',
        'message_count' => 1,
        'sms_type' => 'non_masking',
        'total_cost' => 0.42,
        'messages' => [['id' => 'msg_uuid', 'to' => '8801700000000', 'status' => 'delivered', 'parts' => 1]],
    ];

    Http::fake([
        'https://smsnoc.com/api/v1/send-sms' => Http::response($body, 200),
    ]);

    $driver = new SmsnocDriver(config('barta.drivers.smsnoc'));
    $response = $driver->to('8801700000000')->message('Test message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();
    expect($response->data)->toEqual($body);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://smsnoc.com/api/v1/send-sms' &&
               $request->method() === 'POST' &&
               $request->isJson() &&
               $request->hasHeader('Authorization', 'Bearer test_token') &&
               $request['to'] === ['8801700000000'] &&
               $request['sender_id'] === 'test_sender_id' &&
               $request['message'] === 'Test message';
    });
});

it('sends bulk sms successfully with smsnoc driver', function () {
    Http::fake([
        'https://smsnoc.com/*' => Http::response(['status' => 'success', 'message_count' => 2], 200),
    ]);

    $driver = new SmsnocDriver(config('barta.drivers.smsnoc'));
    $response = $driver->to(['8801700000000', '01800000000'])->message('Bulk test')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();

    Http::assertSentCount(1);
    Http::assertSent(function ($request) {
        return $request['to'] === ['8801700000000', '8801800000000'];
    });
});

it('throws BartaException on smsnoc api error', function () {
    Http::fake([
        'https://smsnoc.com/*' => Http::response(['status' => 'error', 'error_code' => 400, 'message' => 'No valid phone numbers'], 400),
    ]);

    $driver = new SmsnocDriver(config('barta.drivers.smsnoc'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'No valid phone numbers');

it('throws BartaException when the smsnoc api token is rejected', function () {
    Http::fake([
        'https://smsnoc.com/*' => Http::response(['status' => 'error', 'error_code' => 401, 'message' => 'Authentication failed'], 401),
    ]);

    $driver = new SmsnocDriver(config('barta.drivers.smsnoc'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Authentication failed');

it('throws BartaException on a smsnoc response that is not json', function () {
    Http::fake([
        'https://smsnoc.com/*' => Http::response('Bad Gateway', 502),
    ]);

    $driver = new SmsnocDriver(config('barta.drivers.smsnoc'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'smsnoc API error: Bad Gateway');

it('throws BartaException if api_token is missing for smsnoc driver', function () {
    config()->set('barta.drivers.smsnoc.api_token', null);

    $driver = new SmsnocDriver(config('barta.drivers.smsnoc'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Please set api_token for smsnoc in config/barta.php.');

it('throws BartaException if sender_id is missing for smsnoc driver', function () {
    config()->set('barta.drivers.smsnoc.sender_id', null);

    $driver = new SmsnocDriver(config('barta.drivers.smsnoc'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Please set sender_id for smsnoc in config/barta.php.');

it('checks balance successfully with smsnoc driver', function () {
    Http::fake([
        'https://smsnoc.com/api/v1/balance' => Http::response([
            'success' => true,
            'balance' => 97.48,
            'currency' => 'BDT',
            'rates' => ['sms_masking' => 0.62, 'sms_non_masking' => 0.42],
        ], 200),
    ]);

    $driver = new SmsnocDriver(config('barta.drivers.smsnoc'));
    $balance = $driver->balance();

    expect($balance)->toBe(97.48);

    Http::assertSent(fn ($request) => $request->method() === 'GET' && $request->hasHeader('Authorization', 'Bearer test_token'));
});

it('throws exception on smsnoc balance check error', function () {
    Http::fake([
        'https://smsnoc.com/api/v1/balance' => Http::response([
            'success' => false,
            'error' => 'Unauthorized — provide a valid API key or Bearer token',
        ], 401),
    ]);

    $driver = new SmsnocDriver(config('barta.drivers.smsnoc'));
    $driver->balance();
})->throws(BartaException::class, 'Unauthorized');

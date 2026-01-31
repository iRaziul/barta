<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\SmsnocDriver;
use Larament\Barta\Exceptions\BartaException;

beforeEach(function () {
    config()->set('barta.drivers.smsnoc.api_token', 'test_token');
    config()->set('barta.drivers.smsnoc.sender_id', 'test_sender_id');
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
    Http::fake([
        'https://app.smsnoc.com/*' => Http::response(['status' => 'success', 'message' => 'SMS Sent'], 200),
    ]);

    $driver = new SmsnocDriver(config('barta.drivers.smsnoc'));
    $response = $driver->to('8801700000000')->message('Test message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();
    expect($response->data)->toEqual(['status' => 'success', 'message' => 'SMS Sent']);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'app.smsnoc.com/api/v3/sms/send') &&
               $request->method() === 'POST' &&
               $request->hasHeader('Authorization', 'Bearer test_token') &&
               $request['recipient'] === '8801700000000' &&
               $request['sender_id'] === 'test_sender_id' &&
               $request['message'] === 'Test message';
    });
});

it('sends bulk sms successfully with smsnoc driver', function () {
    Http::fake([
        'https://app.smsnoc.com/*' => Http::response(['status' => 'success', 'message' => 'SMS Sent'], 200),
    ]);

    $driver = new SmsnocDriver(config('barta.drivers.smsnoc'));
    $response = $driver->to(['8801700000000', '8801800000000'])->message('Bulk test')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request['recipient'] === '8801700000000,8801800000000';
    });
});

it('throws BartaException on smsnoc api error', function () {
    Http::fake([
        'https://app.smsnoc.com/*' => Http::response(['status' => 'error', 'message' => 'Invalid API Token'], 200),
    ]);

    $driver = new SmsnocDriver(config('barta.drivers.smsnoc'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Invalid API Token');

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

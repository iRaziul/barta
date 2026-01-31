<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\EsmsDriver;
use Larament\Barta\Exceptions\BartaException;

beforeEach(function () {
    config()->set('barta.drivers.esms.api_token', 'test_token');
    config()->set('barta.drivers.esms.sender_id', 'test_sender_id');
});

it('can instantiate the esms driver', function () {
    $driver = new EsmsDriver(config('barta.drivers.esms'));
    expect($driver)->toBeInstanceOf(EsmsDriver::class);
});

it('can set recipient and message for esms driver', function () {
    $driver = new EsmsDriver(config('barta.drivers.esms'));

    expect($driver->to('8801700000000'))->toBeInstanceOf(EsmsDriver::class);
    expect($driver->message('Test message'))->toBeInstanceOf(EsmsDriver::class);
});

it('sends sms successfully with esms driver', function () {
    Http::fake([
        'https://login.esms.com.bd/*' => Http::response(['status' => 'success', 'message' => 'SMS Sent'], 200),
    ]);

    $driver = new EsmsDriver(config('barta.drivers.esms'));
    $response = $driver->to('8801700000000')->message('Test message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();
    expect($response->data)->toEqual(['status' => 'success', 'message' => 'SMS Sent']);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'esms.com.bd/api/v3/sms/send') &&
               $request->method() === 'POST' &&
               $request->hasHeader('Authorization', 'Bearer test_token') &&
               $request['recipient'] === '8801700000000' &&
               $request['sender_id'] === 'test_sender_id' &&
               $request['message'] === 'Test message';
    });
});

it('sends bulk sms successfully with esms driver', function () {
    Http::fake([
        'https://login.esms.com.bd/*' => Http::response(['status' => 'success', 'message' => 'SMS Sent'], 200),
    ]);

    $driver = new EsmsDriver(config('barta.drivers.esms'));
    $response = $driver->to(['8801700000000', '8801800000000'])->message('Bulk test')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request['recipient'] === '8801700000000,8801800000000';
    });
});

it('throws BartaException on esms api error', function () {
    Http::fake([
        'https://login.esms.com.bd/*' => Http::response(['status' => 'error', 'message' => 'Invalid API Token'], 200),
    ]);

    $driver = new EsmsDriver(config('barta.drivers.esms'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Invalid API Token');

it('throws BartaException if sender_id is missing for esms driver', function () {
    config()->set('barta.drivers.esms.sender_id', null);

    $driver = new EsmsDriver(config('barta.drivers.esms'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Please set sender_id for ESMS in config/barta.php.');

it('throws BartaException if api_token is missing for esms driver', function () {
    config()->set('barta.drivers.esms.api_token', null);

    $driver = new EsmsDriver(config('barta.drivers.esms'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Please set api_token for ESMS in config/barta.php.');

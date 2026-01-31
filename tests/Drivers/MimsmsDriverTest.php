<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\MimsmsDriver;
use Larament\Barta\Exceptions\BartaException;

beforeEach(function () {
    config()->set('barta.drivers.mimsms.username', 'test_user');
    config()->set('barta.drivers.mimsms.api_key', 'test_key');
    config()->set('barta.drivers.mimsms.sender_id', 'test_sender_id');
});

it('can instantiate the mimsms driver', function () {
    $driver = new MimsmsDriver(config('barta.drivers.mimsms'));
    expect($driver)->toBeInstanceOf(MimsmsDriver::class);
});

it('can set recipient and message for mimsms driver', function () {
    $driver = new MimsmsDriver(config('barta.drivers.mimsms'));

    expect($driver->to('8801700000000'))->toBeInstanceOf(MimsmsDriver::class);
    expect($driver->message('Test message'))->toBeInstanceOf(MimsmsDriver::class);
});

it('sends sms successfully with mimsms driver', function () {
    Http::fake([
        'https://api.mimsms.com/*' => Http::response(['statusCode' => 200, 'responseResult' => 'Success'], 200),
    ]);

    $driver = new MimsmsDriver(config('barta.drivers.mimsms'));
    $response = $driver->to('8801700000000')->message('Test message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();
    expect($response->data)->toEqual(['statusCode' => 200, 'responseResult' => 'Success']);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.mimsms.com') &&
               $request->method() === 'POST' &&
               $request['UserName'] === 'test_user' &&
               $request['ApiKey'] === 'test_key' &&
               $request['SenderName'] === 'test_sender_id' &&
               $request['MobileNumber'] === '8801700000000' &&
               $request['Message'] === 'Test message';
    });
});

it('sends bulk sms successfully with mimsms driver', function () {
    Http::fake([
        'https://api.mimsms.com/*' => Http::response(['statusCode' => 200, 'responseResult' => 'Success'], 200),
    ]);

    $driver = new MimsmsDriver(config('barta.drivers.mimsms'));
    $response = $driver->to(['8801700000000', '8801800000000'])->message('Bulk test')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request['MobileNumber'] === '8801700000000,8801800000000';
    });
});

it('throws BartaException on mimsms api error', function () {
    Http::fake([
        'https://api.mimsms.com/*' => Http::response(['statusCode' => 401, 'responseResult' => 'Invalid credentials'], 200),
    ]);

    $driver = new MimsmsDriver(config('barta.drivers.mimsms'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Invalid credentials');

it('throws BartaException if username is missing for mimsms driver', function () {
    config()->set('barta.drivers.mimsms.username', null);

    $driver = new MimsmsDriver(config('barta.drivers.mimsms'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Please set username for Mimsms in config/barta.php.');

it('throws BartaException if api_key is missing for mimsms driver', function () {
    config()->set('barta.drivers.mimsms.api_key', null);

    $driver = new MimsmsDriver(config('barta.drivers.mimsms'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Please set api_key for Mimsms in config/barta.php.');

it('throws BartaException if sender_id is missing for mimsms driver', function () {
    config()->set('barta.drivers.mimsms.sender_id', null);

    $driver = new MimsmsDriver(config('barta.drivers.mimsms'));
    $driver->to('8801700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Please set sender_id for Mimsms in config/barta.php.');

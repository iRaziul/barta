<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\AlphasmsDriver;
use Larament\Barta\Exceptions\BartaException;

beforeEach(function () {
    config()->set('barta.drivers.alphasms.api_key', 'test_key');
});

it('can instantiate the alpha driver', function () {
    $driver = new AlphasmsDriver(config('barta.drivers.alphasms'));
    expect($driver)->toBeInstanceOf(AlphasmsDriver::class);
});

it('sends sms successfully with alpha driver', function () {
    Http::fake([
        'https://api.sms.net.bd/*' => Http::response(['error' => 0, 'msg' => 'Success'], 200),
    ]);

    $driver = new AlphasmsDriver(config('barta.drivers.alphasms'));
    $response = $driver->to('8801700000000')->message('Test message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();
});

it('throws exception on alpha api error', function () {
    Http::fake([
        '*' => Http::response(['error' => 1, 'msg' => 'Invalid API Key'], 200),
    ]);

    $driver = new AlphasmsDriver(config('barta.drivers.alphasms'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'Invalid API Key');

it('throws exception if api_key missing', function () {
    config()->set('barta.drivers.alphasms.api_key', null);

    $driver = new AlphasmsDriver(config('barta.drivers.alphasms'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'api_key');

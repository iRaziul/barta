<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\ElitbuzzDriver;
use Larament\Barta\Exceptions\BartaException;

beforeEach(function () {
    config()->set('barta.drivers.elitbuzz.url', 'https://sms.elitbuzz-bd.com');
    config()->set('barta.drivers.elitbuzz.api_key', 'test_key');
    config()->set('barta.drivers.elitbuzz.sender_id', 'test_sender');
});

it('can instantiate the elitbuzz driver', function () {
    $driver = new ElitbuzzDriver(config('barta.drivers.elitbuzz'));
    expect($driver)->toBeInstanceOf(ElitbuzzDriver::class);
});

it('sends sms successfully with elitbuzz driver', function () {
    Http::fake([
        'https://sms.elitbuzz-bd.com/smsapi*' => Http::response('SMS SUBMITTED: 123456', 200),
    ]);

    $driver = new ElitbuzzDriver(config('barta.drivers.elitbuzz'));
    $response = $driver->to('8801700000000')->message('Test message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();
});

it('throws exception on elitbuzz api error', function () {
    Http::fake([
        'https://sms.elitbuzz-bd.com/smsapi*' => Http::response('ERROR: 1002 Invalid User ID or Password', 200),
    ]);

    $driver = new ElitbuzzDriver(config('barta.drivers.elitbuzz'));
    $driver->to('8801700000000')->message('Test')->send();
})->throws(BartaException::class, 'ERROR: 1002');

it('throws exception when checking balance on unsupported elitbuzz driver', function () {
    $driver = new ElitbuzzDriver(config('barta.drivers.elitbuzz'));
    $driver->balance();
})->throws(BartaException::class, 'Balance checking is not supported by [elitbuzz] driver.');

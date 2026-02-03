<?php

declare(strict_types=1);

use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\LogDriver;
use Larament\Barta\Exceptions\BartaException;

it('can instantiate the log driver', function () {
    $driver = new LogDriver;
    expect($driver)->toBeInstanceOf(LogDriver::class);
});

it('can set recipient and message', function () {
    $driver = new LogDriver;

    expect($driver->to('8801700000000'))->toBeInstanceOf(LogDriver::class);
    expect($driver->message('Test message'))->toBeInstanceOf(LogDriver::class);
});

it('throws BartaException if recipient is missing', function () {
    $driver = new LogDriver;
    $driver->message('Test message');

    $driver->send();
})->throws(BartaException::class, 'Recipient number is required. Call ->to() before ->send().');

it('throws BartaException if message is missing', function () {
    $driver = new LogDriver;
    $driver->to('8801700000000');

    $driver->send();
})->throws(BartaException::class, 'Message content is required. Call ->message() before ->send().');

it('returns successful response when sending', function () {
    $driver = new LogDriver;
    $response = $driver->to('8801700000000')->message('Test message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();
    expect($response->data)->toHaveKey('message');
});

<?php

declare(strict_types=1);

use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\AbstractDriver;
use Larament\Barta\Exceptions\BartaException;

class ConcreteDriver extends AbstractDriver
{
    protected function sendSms(): ResponseData
    {
        return new ResponseData(success: true);
    }

    protected function validateConfig(): void
    {
        // Override in child classes for driver-specific validation
    }
}

it('throws exception when recipients are missing', function () {
    $driver = new ConcreteDriver;
    $driver->message('Test message')->send();
})->throws(BartaException::class);

it('throws exception when message is missing', function () {
    $driver = new ConcreteDriver;
    $driver->to('01700000000')->send();
})->throws(BartaException::class);

it('can set recipients and message', function () {
    $driver = new ConcreteDriver;
    $response = $driver->to('01700000000')->message('Test message')->send();

    expect($response->success)->toBeTrue();
});

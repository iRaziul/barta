<?php

declare(strict_types=1);

use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\AbstractDriver;
use Larament\Barta\Exceptions\BartaException;

final class ConcreteDriver extends AbstractDriver
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

final class ThrowingExceptionDriver extends AbstractDriver
{
    protected function sendSms(): ResponseData
    {
        throw new RuntimeException('Connection timed out', 504);
    }

    protected function validateConfig(): void {}
}

final class ThrowingBartaExceptionDriver extends AbstractDriver
{
    protected function sendSms(): ResponseData
    {
        throw new BartaException('Custom gateway error', 422);
    }

    protected function validateConfig(): void {}
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

it('resets recipient and message state after send', function () {
    $driver = new ConcreteDriver;
    $driver->to('01700000000')->message('First message')->send();

    // Second call without setting recipient must fail because state was reset
    $driver->message('Second message')->send();
})->throws(BartaException::class, 'Recipient number is required');

it('resets recipient and message state after queue', function () {
    Illuminate\Support\Facades\Queue::fake();

    $driver = new ConcreteDriver;
    $driver->to('01700000000')->message('Queued message')->queue();

    // Second call without setting recipient must fail because state was reset
    $driver->message('Second message')->send();
})->throws(BartaException::class, 'Recipient number is required');

it('gets driver name from class name', function () {
    $driver = new ConcreteDriver;
    expect($driver->getName())->toBe('concrete');
});

it('handles missing request config with defaults', function () {
    config()->set('barta.request', null);

    $driver = new ConcreteDriver;
    $response = $driver->to('01700000000')->message('Test message')->send();

    expect($response->success)->toBeTrue();
});

it('catches generic throwable and converts to barta exception', function () {
    $driver = new ThrowingExceptionDriver;
    $driver->to('01700000000')->message('Test message')->send();
})->throws(BartaException::class, 'Connection timed out');

it('preserves previous exception and code when wrapping throwable', function () {
    $driver = new ThrowingExceptionDriver;
    try {
        $driver->to('01700000000')->message('Test message')->send();
    } catch (BartaException $e) {
        expect($e->getMessage())->toBe('Connection timed out')
            ->and($e->getCode())->toBe(504)
            ->and($e->getPrevious())->toBeInstanceOf(RuntimeException::class);
    }
});

it('re-throws barta exception directly without wrapping', function () {
    $driver = new ThrowingBartaExceptionDriver;
    try {
        $driver->to('01700000000')->message('Test message')->send();
    } catch (BartaException $e) {
        expect($e->getMessage())->toBe('Custom gateway error')
            ->and($e->getCode())->toBe(422)
            ->and($e->getPrevious())->toBeNull();
    }
});

it('resets state even when sendSms throws exception', function () {
    $driver = new ThrowingExceptionDriver;
    try {
        $driver->to('01700000000')->message('Test message')->send();
    } catch (BartaException) {
        // Suppress exception
    }

    $driver->message('Second message')->send();
})->throws(BartaException::class, 'Recipient number is required');

<?php

declare(strict_types=1);

use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\AbstractDriver;
use Larament\Barta\Exceptions\BartaException;

class ConcreteDriver extends AbstractDriver
{
    public function send(): ResponseData
    {
        // Concrete implementation for testing abstract methods
        $this->validate();

        return new ResponseData(success: true);
    }

    public function callFormatPhoneNumber(string $number): string
    {
        return $this->formatPhoneNumber($number);
    }
}

it('formats valid bangladeshi numbers with 88 prefix', function (string $input, string $expected) {
    $driver = new ConcreteDriver;
    expect($driver->callFormatPhoneNumber($input))->toBe($expected);
})->with([
    '01700000000' => ['01700000000', '8801700000000'],
    '8801700000000' => ['8801700000000', '8801700000000'],
    '+8801700000000' => ['+8801700000000', '8801700000000'],
    '  01700000000  ' => ['  01700000000  ', '8801700000000'], // With spaces
    '017-0000-0000' => ['017-0000-0000', '8801700000000'], // With hyphens
]);

it('throws exception for invalid bangladeshi numbers', function (string $input) {
    $driver = new ConcreteDriver;
    $driver->callFormatPhoneNumber($input);
})->throws(BartaException::class)->with([
    'too short' => ['12345'],
    'invalid prefix' => ['01200000000'],
    'too long' => ['017000000001234'],
]);

it('throws exception for invalid number format', function () {
    $driver = new ConcreteDriver;
    $driver->callFormatPhoneNumber('12345');
})->throws(BartaException::class);

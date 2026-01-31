<?php

declare(strict_types=1);

namespace Larament\Barta\Exceptions;

use Exception;

class BartaException extends Exception
{
    public static function invalidNumber(string $number): self
    {
        return new self("Invalid Bangladeshi mobile number: {$number}");
    }

    public static function missingRecipient(): self
    {
        return new self('Recipient number is required. Call ->to() before ->send().');
    }

    public static function missingMessage(): self
    {
        return new self('Message content is required. Call ->message() before ->send().');
    }
}

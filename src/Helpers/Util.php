<?php

declare(strict_types=1);

namespace Larament\Barta\Helpers;

use Larament\Barta\Exceptions\BartaException;

final class Util
{
    /**
     * Standardizes BD phone numbers to 8801XXXXXXXXX format.
     *
     * @throws BartaException
     */
    public static function formatPhoneNumber(string $number): string
    {
        $digits = (string) preg_replace('/\D/', '', $number);

        if (preg_match('/^(?:00880|880|0)?(1[3-9]\d{8})$/', $digits, $matches)) {
            return '880'.$matches[1];
        }

        throw BartaException::invalidNumber($number);
    }
}

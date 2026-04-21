<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;
use Larament\Barta\Facades\Barta;
use Throwable;

final class FallbackDriver extends AbstractDriver
{
    protected function sendSms(): ResponseData
    {
        $drivers = $this->config['drivers'] ?? [];
        $exceptions = [];

        foreach ($drivers as $driverName) {
            try {
                return Barta::driver($driverName)
                    ->to($this->recipients)
                    ->message($this->message)
                    ->send();
            } catch (Throwable $e) {
                $exceptions[] = $e->getMessage();

                continue;
            }
        }

        throw new BartaException('All fallback drivers failed. Errors: '.implode(', ', $exceptions));
    }

    protected function validateConfig(): void
    {
        if (empty($this->config['drivers']) || ! is_array($this->config['drivers'])) {
            throw new BartaException('Please configure an array of drivers for Fallback');
        }
    }
}

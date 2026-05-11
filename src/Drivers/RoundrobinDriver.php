<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Cache;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;
use Larament\Barta\Facades\Barta;

final class RoundrobinDriver extends AbstractDriver
{
    protected function sendSms(): ResponseData
    {
        $drivers = $this->config['drivers'] ?? [];

        $index = Cache::increment('barta.roundrobin.index');

        $driverName = $drivers[$index % count($drivers)];

        return Barta::driver($driverName)
            ->to($this->recipients)
            ->message($this->message)
            ->send();
    }

    protected function validateConfig(): void
    {
        if (empty($this->config['drivers']) || ! is_array($this->config['drivers'])) {
            throw new BartaException('Please configure an array of drivers for Roundrobin in config/barta.php.');
        }
    }
}

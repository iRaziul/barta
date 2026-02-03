<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class RobiDriver extends AbstractDriver
{
    private string $baseUrl = 'https://bmpws.robi.com.bd/ApacheGearWS';

    protected function sendSms(): ResponseData
    {
        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay)
            ->asForm()
            ->post('/SendTextMessage', [
                'username' => $this->config['username'],
                'password' => $this->config['password'],
                'To' => implode(',', $this->recipients),
                'Message' => $this->message,
            ]);

        $body = $response->body();

        if (str_contains(mb_strtolower($body), 'error') || str_contains(mb_strtolower($body), 'fail')) {
            throw new BartaException($body ?: 'Robi API error');
        }

        return new ResponseData(
            success: true,
            data: ['response' => $body],
        );
    }

    protected function validateConfig(): void
    {
        if (empty($this->config['username'])) {
            throw new BartaException('Please set username for Robi in config/barta.php.');
        }

        if (empty($this->config['password'])) {
            throw new BartaException('Please set password for Robi in config/barta.php.');
        }
    }
}

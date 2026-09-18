<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class InfobipDriver extends AbstractDriver
{
    protected function sendSms(): ResponseData
    {
        $response = Http::baseUrl($this->config['base_url'])
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay, throw: false)
            ->withBasicAuth($this->config['username'], $this->config['password'])
            ->acceptJson()
            ->asJson()
            ->post('/sms/2/text/advanced', [
                'messages' => [
                    [
                        'from' => $this->config['sender_id'],
                        'destinations' => array_map(
                            fn (string $number) => ['to' => $number],
                            $this->recipients
                        ),
                        'text' => $this->message,
                    ],
                ],
            ]);

        if ($response->failed()) {
            $data = $response->json();
            $error = is_array($data) ? ($data['requestError']['serviceException']['text'] ?? null) : null;
            throw new BartaException((string) ($error ?? ($response->body() ?: 'Infobip API error: HTTP '.$response->status())));
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new BartaException('Infobip API error: Invalid response received');
        }

        $status = $data['messages'][0]['status']['groupName'] ?? null;

        if ($status === 'REJECTED' || isset($data['requestError'])) {
            $error = $data['requestError']['serviceException']['text']
                ?? $data['messages'][0]['status']['description']
                ?? 'Infobip API error';
            throw new BartaException((string) $error);
        }

        return new ResponseData(
            success: in_array($status, ['PENDING', 'SENT', 'DELIVERED'], true),
            data: $data,
        );
    }

    protected function fetchBalance(): float
    {
        $response = Http::baseUrl($this->config['base_url'])
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay, throw: false)
            ->withBasicAuth($this->config['username'], $this->config['password'])
            ->acceptJson()
            ->get('/account/1/balance');

        if ($response->failed()) {
            $data = $response->json();
            $error = is_array($data) ? ($data['requestError']['serviceException']['text'] ?? null) : null;
            throw new BartaException((string) ($error ?? ($response->body() ?: 'Infobip API error: HTTP '.$response->status())));
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new BartaException('Infobip API error: Invalid response received');
        }

        if (isset($data['requestError'])) {
            $error = $data['requestError']['serviceException']['text'] ?? 'Infobip API error';
            throw new BartaException((string) $error);
        }

        return (float) ($data['balance'] ?? 0.0);
    }

    protected function validateConfig(): void
    {
        if (empty($this->config['base_url'])) {
            throw new BartaException('Please set base_url for Infobip in config/barta.php.');
        }

        if (empty($this->config['username'])) {
            throw new BartaException('Please set username for Infobip in config/barta.php.');
        }

        if (empty($this->config['password'])) {
            throw new BartaException('Please set password for Infobip in config/barta.php.');
        }

        if (empty($this->config['sender_id'])) {
            throw new BartaException('Please set sender_id for Infobip in config/barta.php.');
        }
    }
}

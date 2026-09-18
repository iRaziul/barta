<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class ZendsmsDriver extends AbstractDriver
{
    private string $baseUrl = 'https://api.zendsms.com';

    protected function sendSms(): ResponseData
    {
        $response = Http::baseUrl($this->baseUrl)
            ->withToken($this->config['api_key'])
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay, throw: false)
            ->acceptJson()
            ->post('/api/v1/send-sms', [
                'recipient' => implode(',', $this->recipients),
                'sender_id' => $this->config['sender_id'],
                'message' => $this->message,
            ]);

        if ($response->failed()) {
            $data = $response->json();
            $errorMessage = is_array($data) ? ($data['message'] ?? null) : null;

            throw new BartaException((string) ($errorMessage ?? ('ZendSMS API error: '.($response->body() ?: 'HTTP request failed with status '.$response->status()))));
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new BartaException('ZendSMS API error: Invalid response received');
        }

        if (! ($data['success'] ?? false) || (isset($data['code']) && (int) $data['code'] !== 1000)) {
            throw new BartaException((string) ($data['message'] ?? 'ZendSMS API error'));
        }

        return new ResponseData(
            success: true,
            data: $data,
        );
    }

    protected function fetchBalance(): float
    {
        $response = Http::baseUrl($this->baseUrl)
            ->withToken($this->config['api_key'])
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay, throw: false)
            ->acceptJson()
            ->get('/api/v1/balance');

        if ($response->failed()) {
            $data = $response->json();
            $errorMessage = is_array($data) ? ($data['message'] ?? null) : null;

            throw new BartaException((string) ($errorMessage ?? ('ZendSMS API error: '.($response->body() ?: 'HTTP request failed with status '.$response->status()))));
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new BartaException('ZendSMS API error: Invalid response received');
        }

        if (! ($data['success'] ?? false) || (isset($data['code']) && (int) $data['code'] !== 1000)) {
            throw new BartaException((string) ($data['message'] ?? 'ZendSMS API error'));
        }

        return (float) ($data['data']['balance'] ?? 0.0);
    }

    protected function validateConfig(): void
    {
        if (empty($this->config['api_key'])) {
            throw new BartaException('Please set api_key for ZendSMS in config/barta.php.');
        }

        if (empty($this->config['sender_id'])) {
            throw new BartaException('Please set sender_id for ZendSMS in config/barta.php.');
        }
    }
}

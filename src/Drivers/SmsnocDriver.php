<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class SmsnocDriver extends AbstractDriver
{
    private string $baseUrl = 'https://app.smsnoc.com/api/v3';

    protected function sendSms(): ResponseData
    {
        $response = Http::baseUrl($this->baseUrl)
            ->withToken($this->config['api_token'])
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay, throw: false)
            ->acceptJson()
            ->post('/sms/send', [
                'recipient' => implode(',', $this->recipients),
                'sender_id' => $this->config['sender_id'],
                'type' => 'plain',
                'message' => $this->message,
            ]);

        if ($response->failed()) {
            throw new BartaException('smsnoc API error: '.($response->body() ?: 'HTTP request failed with status '.$response->status()));
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new BartaException('smsnoc API error: Invalid response received');
        }

        if (($data['status'] ?? '') === 'error') {
            throw new BartaException((string) ($data['message'] ?? 'smsnoc API error'));
        }

        return new ResponseData(
            success: ($data['status'] ?? '') === 'success',
            data: $data,
        );
    }

    protected function fetchBalance(): float
    {
        $response = Http::baseUrl($this->baseUrl)
            ->withToken($this->config['api_token'])
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay, throw: false)
            ->acceptJson()
            ->get('/balance');

        if ($response->failed()) {
            throw new BartaException('smsnoc API error: '.($response->body() ?: 'HTTP request failed with status '.$response->status()));
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new BartaException('smsnoc API error: Invalid response received');
        }

        if (($data['status'] ?? '') === 'error') {
            throw new BartaException((string) ($data['message'] ?? 'smsnoc API error'));
        }

        return (float) ($data['data']['remaining_balance'] ?? $data['data']['balance'] ?? $data['balance'] ?? 0.0);
    }

    protected function validateConfig(): void
    {
        if (empty($this->config['api_token'])) {
            throw new BartaException('Please set api_token for smsnoc in config/barta.php.');
        }

        if (empty($this->config['sender_id'])) {
            throw new BartaException('Please set sender_id for smsnoc in config/barta.php.');
        }
    }
}
